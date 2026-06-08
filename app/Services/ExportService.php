<?php

namespace App\Services;

use App\Exports\GeneratorExport;
use App\Mail\ExportReady;
use App\Models\Account;
use App\Models\Device;
use App\Models\DeviceGateway;
use App\Models\Session;
use App\Models\SessionType;
use App\Models\User;
use App\Traits\DeviceExportTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    use TranslationsTrait;
    use SearchFiltersTrait;
    use DeviceExportTrait;

    public function requiredParams(string $type): array
    {
        return match ($type) {
            'devices'  => ['filters', 'exportList', 'locale'],
            'comments' => ['filters', 'identifiers'],
            'history'  => ['locale', 'context'],
            'gateways' => [],
            default    => throw new \InvalidArgumentException("Unknown export type: {$type}"),
        };
    }

    /**
     * @return array{0: array, 1: \Generator}
     */
    public function makeExport(string $type, array $params, string $progressFile): array
    {
        return match ($type) {
            'devices'  => $this->makeDevicesExport($params, $progressFile),
            'comments' => $this->makeCommentsExport($params, $progressFile),
            'history'  => $this->makeHistoryExport($params, $progressFile),
            'gateways' => $this->makeGatewaysExport($params, $progressFile),
            default    => throw new \InvalidArgumentException("Unknown export type: {$type}"),
        };
    }

    public function progressFilePath(string $type, int $userId, string $downloadId): string
    {
        return storage_path('framework/cache/export_' . $type . '_' . $userId . '_' . $downloadId . '.txt');
    }

    public function exportFilePath(string $type, string $downloadId, string $format): string
    {
        $dir = storage_path('app/exports');
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        return $dir . '/' . $type . '_' . $downloadId . '.' . $format;
    }

    public function initProgress(string $progressFile): void
    {
        @file_put_contents($progressFile, '0');
        @chmod($progressFile, 0600);
    }

    public function finalizeProgress(string $progressFile): void
    {
        @file_put_contents($progressFile, '100');
        @chmod($progressFile, 0600);
    }

    public function writeFile(\Generator $rows, array $header, string $format, string $filePath): void
    {
        if ($format === 'xlsx') {
            Excel::store(
                new GeneratorExport($rows, array_values($header)),
                'exports/' . basename($filePath),
                'local'
            );
        } else {
            $tempPath = $filePath . '.tmp';
            $file = fopen($tempPath, 'w');
            // UTF-8 BOM so Excel opens CSV without mangling special characters.
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, array_values($header), ',', '"', '\\', "\n");
            foreach ($rows as $row) {
                $line = implode(',', array_map(
                    fn ($val) => '"' . str_replace('"', '""', (string) $val) . '"',
                    $row
                ));
                fwrite($file, $line . "\n");
            }
            fclose($file);
            @rename($tempPath, $filePath);
        }
    }

    public function sendByEmail(string $filePath, string $exportType, string $format, int $userId): void
    {
        $user = User::find($userId);
        if (!$user || !($email = $user->getPrimaryEmail())) {
            return;
        }

        try {
            Mail::to($email)->send(new ExportReady($filePath, $exportType, $format));
        } catch (\Throwable $e) {
            Log::warning('ExportReady email failed', [
                'exportType' => $exportType,
                'userId'     => $userId,
                'error'      => $e->getMessage(),
            ]);
        } finally {
            @unlink($filePath);
        }
    }

    private function makeDevicesExport(array $params, string $progressFile): array
    {
        $context = $this->buildDevicesContext($params);

        return [
            $context['header'],
            $this->devicesRows($params, $progressFile, $context),
        ];
    }

    private function buildDevicesContext(array $params): array
    {
        if (!empty($params['accountId']) && empty($this->profileData)) {
            $account = Account::find($params['accountId']);
            if ($account) {
                $this->profileData = $account->account_translation;
            }
        }

        $locale = $params['locale'];
        $alertTranslations = $this->getAlertTranslations($locale);

        $fieldList = $this->getFieldTranslations($locale);
        unset($fieldList['numbers']);

        $customFieldService = new CustomFieldsService();
        if (!empty($this->profileData)) {
            $customFieldService->initProfileData($this->profileData);
        }

        [$customSiteFields, $customDeviceFields] = $this->getCustomFields($customFieldService, $params['accountId'] ?? null);

        $siteFields = $this->getSiteFields($customSiteFields);
        $additionalFields = $this->getAdditionalFields($customDeviceFields);

        $initialList = array_merge($siteFields, $fieldList, $additionalFields);
        $csvHeaderLabels = array_merge([
            'site_id'   => trans('Installation ID'),
            'device_id' => trans('Device ID'),
        ], $initialList);

        return [
            'header' => array_intersect_key(
                $csvHeaderLabels,
                array_flip(array_merge(['site_id', 'device_id'], $params['exportList']))
            ),
            'alertTranslations' => $alertTranslations,
            'siteFields'        => $siteFields,
            'additionalFields'  => $additionalFields,
        ];
    }

    private function devicesRows(array $params, string $progressFile, array $context): \Generator
    {
        $header = $context['header'];
        $relationFlags = $this->getRelationFlags($header);
        $searchService = new SearchDeviceService();
        $exportSites = !empty($params['exportSites']);

        if ($exportSites) {
            $query = $searchService->buildDeviceSitesQuery($params['filters']);
            $query->with($this->getOptimizedSiteRelations($header, $relationFlags));

            $totalRows = 0;
            (clone $query)->withCount('devices')->chunk(500, function ($sites) use (&$totalRows) {
                $totalRows += $sites->sum('devices_count');
            });
        } else {
            $base = Device::query()->where('device_enabled', true);
            $query = $searchService->buildDevicesQuery($params['filters'], true, $base, true);
            $query->with($this->getOptimizedDeviceRelations($header, $relationFlags));
            if ($relationFlags['needs_last_alarm']) {
                $this->applyLastAlarmAggregate($query);
            }
            $totalRows = (clone $query)->count();
        }

        $this->logMemoryUsage('devices_generator_start');

        $processed = 0;

        if ($exportSites) {
            foreach ($query->lazy(100) as $site) {
                foreach ($site->devices as $device) {
                    yield $this->generateCsvRow(
                        $header,
                        $device,
                        $context['alertTranslations'],
                        $context['siteFields'],
                        $context['additionalFields']
                    );

                    $processed++;
                    if ($processed % 50 === 0 || $processed === $totalRows) {
                        $this->updateProgressFile($progressFile, $processed, $totalRows);
                    }
                }
            }
        } else {
            foreach ($query->lazy(500) as $device) {
                yield $this->generateCsvRow(
                    $header,
                    $device,
                    $context['alertTranslations'],
                    $context['siteFields'],
                    $context['additionalFields']
                );

                $processed++;
                if ($processed % 50 === 0 || $processed === $totalRows) {
                    $this->updateProgressFile($progressFile, $processed, $totalRows);
                }
                if ($processed % 500 === 0) {
                    $this->logMemoryUsage('devices_generator_chunk');
                }
            }
        }

        $this->logMemoryUsage('devices_generator_done');
    }

    private function makeCommentsExport(array $params, string $progressFile): array
    {
        $header = $this->commentsHeader($params);

        return [$header, $this->commentsRows($params, $progressFile, $header)];
    }

    private function commentsHeader(array $params): array
    {
        $headerLabels = ['device_id' => trans('Device ID')];

        foreach ($params['identifiers'] as $identifier => $enabled) {
            if ($enabled) {
                $headerLabels = Arr::add($headerLabels, $identifier, ucfirst($identifier));
            }
        }

        $headerLabels = Arr::add($headerLabels, 'author', 'Author');
        $headerLabels = Arr::add($headerLabels, 'date', 'Date');

        return Arr::add($headerLabels, 'comment', 'Comment');
    }

    private function commentsRows(array $params, string $progressFile, array $header): \Generator
    {
        $searchService = new SearchDeviceService();
        $filters = $params['filters'];

        $devices = !empty($params['exportSites'])
            ? $this->commentDevicesFromSites($filters, $searchService)
            : $this->commentDevices($filters, $searchService);

        $total = $this->countItems($devices);
        $processed = 0;

        foreach ($devices as $device) {
            yield from $this->commentRowsForDevice($header, $device);

            $processed++;
            if ($total > 0) {
                @file_put_contents($progressFile, (string) max(1, min(99, (int) round(($processed / $total) * 99))));
            }
        }
    }

    private function commentDevicesFromSites(array $filters, SearchDeviceService $service): array
    {
        $result = [];
        $sites = $service->searchDeviceSites($filters);

        foreach ($sites as $site) {
            if ($site->devices->isEmpty()) {
                continue;
            }

            foreach ($site->devices as $device) {
                if ($this->shouldIncludeCommentDevice($device, $filters)) {
                    $result[] = $device;
                }
            }
        }

        return $result;
    }

    private function commentDevices(array $filters, SearchDeviceService $service)
    {
        $devices = $service->searchDevices($filters);

        if (empty($filters['search_tabs']) || in_array('all', $filters['search_tabs'])) {
            return $devices;
        }

        return $devices->filter(fn ($device) => $this->shouldIncludeCommentDevice($device, $filters));
    }

    private function shouldIncludeCommentDevice($device, array $filters): bool
    {
        return empty($filters['search_tabs'])
            || in_array('all', $filters['search_tabs'])
            || ($device->device_enabled && in_array('enabled', $filters['search_tabs']))
            || (!$device->device_enabled && in_array('disabled', $filters['search_tabs']));
    }

    private function commentRowsForDevice(array $header, $device): array
    {
        $rows = [];

        foreach ($device['device_comments'] as $comment) {
            $index = 0;
            $row = [];

            foreach ($header as $key => $label) {
                if ($key === 'device_id') {
                    $row[$index] = $device['device_id'];
                } elseif ($key === 'equipment') {
                    $row[$index] = $device['device_equipment'];
                } elseif ($key === 'identity') {
                    $row[$index] = $device['device_identity'];
                } elseif ($key === 'pin') {
                    $row[$index] = $device['device_pin'];
                } elseif ($key === 'module') {
                    $row[$index] = $device['device_module'];
                } elseif ($key === 'numbers') {
                    $row[$index] = implode('|', $device['device_site']['numbers']->pluck('number_value')->toArray());
                } elseif ($key === 'site') {
                    $row[$index] = $device['device_site']['ds_name'];
                } elseif ($key === 'author') {
                    $user = User::where('user_id', $comment['dc_user_id'])->first();
                    $row[$index] = $user ? $user->name : '';
                } elseif ($key === 'date') {
                    $row[$index] = toUserDateTime($comment['dc_created']);
                } elseif ($key === 'comment') {
                    $row[$index] = $comment['dc_text'];
                } else {
                    $row[$index] = '';
                }

                $index++;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function countItems($items): int
    {
        if (is_array($items)) {
            return count($items);
        }

        if (is_object($items) && method_exists($items, 'count')) {
            return (int) $items->count();
        }

        $count = 0;
        foreach ($items as $_) {
            $count++;
        }

        return $count;
    }

    private function makeGatewaysExport(array $params, string $progressFile): array
    {
        return [
            [
                __('ID'),
                __('Gateway type'),
                __('Mac address'),
                __('Password'),
                __('Connected site'),
                __('Pstn'),
                __('Sim'),
                __('Sip'),
                __('Pbx'),
                __('Expires'),
                __('Enabled'),
            ],
            $this->gatewayRows($params, $progressFile),
        ];
    }

    private function gatewayRows(array $params, string $progressFile): \Generator
    {
        $data = $this->gatewayData($params['tab'] ?? 'enabled', $params['search'] ?? '');
        $total = $data->count();
        $processed = 0;

        foreach ($data as $gateway) {
            yield [
                $gateway->dg_id ?? '',
                $gateway->device->module->module_desc ?? $gateway->device->module->module_name ?? '',
                $gateway->dg_mac ?? '',
                $gateway->dg_sippwd ?? '',
                $gateway->device->device_site->ds_name ?? '',
                $gateway->device->device_site->pstn->number_value ?? '',
                $gateway->device->device_site->sim->number_value ?? '',
                $gateway->device->device_site->sip->number_value ?? '',
                $gateway->device->device_site->pbx->number_value ?? '',
                $gateway->dg_expires ?? '',
                $gateway->device->device_enabled ?? '0',
            ];

            $processed++;
            if ($total > 0) {
                @file_put_contents($progressFile, (string) max(1, min(99, (int) round(($processed / $total) * 99))));
            }
        }
    }

    private function gatewayData(string $tab, string $search)
    {
        $query = DeviceGateway::query()
            ->with([
                'device.module',
                'device.device_site.numbers',
            ]);

        if (!empty($search)) {
            $search = strtolower($search);
            $query->where(function ($builder) use ($search) {
                $builder->where('dg_mac', 'like', '%' . $search . '%')
                    ->orWhereHas('type', function ($q) use ($search) {
                        $q->where('dgt_type', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('device_site', function ($q) use ($search) {
                        $q->where('ds_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('numbers', function ($q) use ($search) {
                        $q->where('number_value', 'like', '%' . $search . '%');
                    });
            });
        }

        return match ($tab) {
            'disabled'   => $query->forAccount()->disabled()->orderBy('dg_mac')->get(),
            'assigned'   => $query->forAccount()->assigned()->orderBy('dg_mac')->get(),
            'unassigned' => $query->forAccount()->unassigned()->orderBy('dg_mac')->get(),
            default      => $query->forAccount()->enabled()->orderBy('dg_mac')->get(),
        };
    }

    private function makeHistoryExport(array $params, string $progressFile): array
    {
        return [
            [
                __('SESSION-ID'),
                __('UUID'),
                __('REF-ID'),
                __('SESSION-TYPE'),
                __('EVENT-TYPE'),
                __('EVENT-VALUE'),
                __('EVENT-SEVERITY'),
                __('EVENT_TIMESTAMP'),
            ],
            $this->historyRows($params, $progressFile),
        ];
    }

    private function historyRows(array $params, string $progressFile): \Generator
    {
        $locale = $params['locale'] ?? 'en';
        $deviceTranslations = $this->historyDeviceTranslations($params, $locale);
        $alertTranslations = $params['alert_translations'] ?? $this->getAlertTranslations($locale);

        $total = $this->historyQuery($params)->count();
        $processed = 0;

        foreach ($this->historyQuery($params)->lazy(100) as $session) {
            yield from $this->historyRowsForSession($session, $deviceTranslations, $alertTranslations);

            $processed++;
            if ($total > 0) {
                @file_put_contents($progressFile, (string) max(1, min(99, (int) round(($processed / $total) * 99))));
            }
        }
    }

    private function historyDeviceTranslations(array $params, string $locale): array
    {
        if (!empty($params['translations']) && is_array($params['translations'])) {
            return $params['translations'];
        }

        $translations = session('translations') ?? [];
        if (!empty($translations[$locale]['device']['setting'])) {
            $translations['device'] = $translations[$locale]['device']['setting'];
        }

        return Arr::dot($translations);
    }

    private function historyQuery(array $params)
    {
        $context = $params['context'] ?? null;
        $deviceId = $params['device_id'] ?? null;
        $deviceSiteId = $params['device_site_id'] ?? null;
        $siteDeviceIds = $params['site_device_ids'] ?? [];
        $historyFilter = $params['history_filter'] ?? [];
        $severityFilter = $params['severity_filter'] ?? [];
        $dateFilter = $params['date_filter'] ?? $this->getDateFilter();

        $startDate = $dateFilter['dateFromValue'] ?? null;
        $endDate = $dateFilter['dateToValue'] ?? null;

        $start = $startDate ? $this->getStartDate($startDate) : $this->getStartDate();
        $end = $endDate ? $this->getEndDate($endDate) : $this->getEndDate();

        $alarmSessionTypeId = SessionType::query()
            ->where('st_type', '=', 'ALARM')
            ->value('st_id');

        $query = Session::with([
            'session_type',
            'session_direction',
            'comments',
        ]);

        if ($deviceSiteId && $context === 'all') {
            $query->where(function ($builder) use ($deviceSiteId, $siteDeviceIds) {
                $builder->where('session_ds_id', $deviceSiteId)
                    ->orWhereIn('session_device_id', $siteDeviceIds);
            });
        } elseif ($deviceSiteId && $context === 'only_site') {
            $query->where('session_ds_id', $deviceSiteId)
                ->whereNull('session_device_id');
        } elseif ($deviceId) {
            $query->where('session_device_id', '=', $deviceId);
        }

        return $query
            ->whereBetween('session_start', [$start, $end])
            ->with('events', 'events.event_type', 'events.event_severity')
            ->when(!empty(array_filter($historyFilter)), function ($builder) use ($historyFilter) {
                $filterTypesMap = [
                    'alarms'      => ['ALARM'],
                    'carcalls'    => ['CARCALL'],
                    'periodicals' => ['PERIODICAL', 'MONITOR'],
                    'sets'        => ['SET'],
                    'revivals'    => ['REVIVAL'],
                    'triggers'    => ['TRIGGER'],
                    'calls'       => ['CALL'],
                ];

                $filters = array_filter($historyFilter);
                $types = [];
                foreach (array_intersect_key($filterTypesMap, $filters) as $array) {
                    $types = array_merge($types, $array);
                }

                return $builder->with('alerts', 'sets')->types($types);
            })
            ->when(empty(array_filter($historyFilter)), function ($builder) {
                return $builder->with('alerts', 'sets');
            })
            ->when(($severityFilter['warnings'] ?? false), function ($builder) use ($alarmSessionTypeId) {
                return $builder->where('session_warnings', '>', 0)
                    ->where('session_st_id', '!=', $alarmSessionTypeId);
            })
            ->when(($severityFilter['errors'] ?? false), function ($builder) {
                return $builder->where('session_errors', '>', 0);
            })
            ->orderByDesc('session_id');
    }

    private function historyRowsForSession(Session $history, array $deviceTranslations, array $alertTranslations): array
    {
        $rows = [];
        $session = $history->toArray();
        $common = [
            $session['session_id'],
            $session['session_uuid'],
            $session['session_ref_id'],
            $session['session_type']['st_type'],
        ];

        if (!empty($session['alerts'])) {
            foreach ($session['alerts'] as $alert) {
                $row = $common;
                $row[] = $alertTranslations[$alert['alert_type']['at_type']] ?? $alert['alert_type']['at_type'];
                $row[] = $alert['alert_value'];
                $row[] = $alert['alert_type']['alert_severity']['as_type'];
                $row[] = toUserDateTime($alert['alert_timestamp']);
                $rows[] = $row;
            }
        }

        if (!empty($session['sets'])) {
            foreach ($session['sets'] as $set) {
                $row = $common;
                $row[] = data_get($deviceTranslations, $set['setting']['setting_key'], $set['setting']['setting_key']);
                $row[] = $set['set_value'];
                $row[] = $set['set_success'] ? 'SUCCESS' : 'ERROR';
                $row[] = toUserDateTime($set['set_timestamp']);
                $rows[] = $row;
            }
        }

        if (!empty($session['events'])) {
            foreach ($session['events'] as $event) {
                $row = $common;
                $row[] = data_get($deviceTranslations, $event['event_type']['et_type'], $event['event_type']['et_type']);
                $row[] = $event['event_value'] ?? '';
                $row[] = $event['event_severity']['es_type'] ?? '';
                $row[] = toUserDateTime($event['event_timestamp']);
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
