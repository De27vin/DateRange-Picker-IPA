<?php

namespace App\Services\Export;

use App\Models\Session;
use App\Models\SessionType;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Arr;

class HistoryRowGenerator implements RowGeneratorInterface
{
    use TranslationsTrait;
    use SearchFiltersTrait;

    private array $deviceTranslations = [];
    private array $alertTranslations = [];

    public function requiredParams(): array
    {
        return ['locale', 'context'];
    }

    public function getHeader(array $params): array
    {
        return [
            __('SESSION-ID'),
            __('UUID'),
            __('REF-ID'),
            __('SESSION-TYPE'),
            __('EVENT-TYPE'),
            __('EVENT-VALUE'),
            __('EVENT-SEVERITY'),
            __('EVENT_TIMESTAMP'),
        ];
    }

    public function generate(array $params, string $progressFile): \Generator
    {
        $locale = $params['locale'] ?? 'en';

        if (!empty($params['translations']) && is_array($params['translations'])) {
            $this->deviceTranslations = $params['translations'];
        } else {
            $translations = session('translations') ?? [];
            if (!empty($translations[$locale]['device']['setting'])) {
                $translations['device'] = $translations[$locale]['device']['setting'];
            }
            $this->deviceTranslations = Arr::dot($translations);
        }

        $this->alertTranslations = $params['alert_translations'] ?? $this->getAlertTranslations($locale);

        // COUNT first (one cheap query), then stream — avoids loading the full collection into RAM.
        $total     = $this->buildQuery($params)->count();
        $processed = 0;

        foreach ($this->buildQuery($params)->lazy(100) as $session) {
            yield from $this->generateRowsForSession($session);

            $processed++;
            if ($total > 0) {
                @file_put_contents($progressFile, (string) max(1, min(99, (int) round(($processed / $total) * 99))));
            }
        }
    }

    // todo: this is common with DeviceHistoryNew ques - good candidate to move to separate service
    private function buildQuery(array $params)
    {
        $context        = $params['context'] ?? null;
        $deviceId       = $params['device_id'] ?? null;
        $deviceSiteId   = $params['device_site_id'] ?? null;
        $siteDeviceIds  = $params['site_device_ids'] ?? [];
        $historyFilter  = $params['history_filter'] ?? [];
        $severityFilter = $params['severity_filter'] ?? [];
        $dateFilter     = $params['date_filter'] ?? $this->getDateFilter();

        $startDate = $dateFilter['dateFromValue'] ?? null;
        $endDate   = $dateFilter['dateToValue'] ?? null;

        $start = $startDate ? $this->getStartDate($startDate) : $this->getStartDate();
        $end   = $endDate ? $this->getEndDate($endDate) : $this->getEndDate();

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

        $query
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

        return $query;
    }

    private function generateRowsForSession(Session $history): array
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
                $row[] = $this->alertTranslations[$alert['alert_type']['at_type']] ?? $alert['alert_type']['at_type'];
                $row[] = $alert['alert_value'];
                $row[] = $alert['alert_type']['alert_severity']['as_type'];
                $row[] = toUserDateTime($alert['alert_timestamp']);
                $rows[] = $row;
            }
        }

        if (!empty($session['sets'])) {
            foreach ($session['sets'] as $set) {
                $row = $common;
                $row[] = data_get($this->deviceTranslations, $set['setting']['setting_key'], $set['setting']['setting_key']);
                $row[] = $set['set_value'];
                $row[] = $set['set_success'] ? 'SUCCESS' : 'ERROR';
                $row[] = toUserDateTime($set['set_timestamp']);
                $rows[] = $row;
            }
        }

        if (!empty($session['events'])) {
            foreach ($session['events'] as $event) {
                $row = $common;
                $row[] = data_get($this->deviceTranslations, $event['event_type']['et_type'], $event['event_type']['et_type']);
                $row[] = $event['event_value'] ?? '';
                $row[] = $event['event_severity']['es_type'] ?? '';
                $row[] = toUserDateTime($event['event_timestamp']);
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
