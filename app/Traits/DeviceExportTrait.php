<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait DeviceExportTrait
{
    private ?array $alarmalityTypesCache = null;

    private function getCustomFields($service, ?int $accountId = null)
    {
        $customFields = $service->getAccountCustomFieldsConfig($accountId ?? session('account.id'));
        $siteFields = [];
        $deviceFields = [];

        foreach ($customFields as $field) {
            $key = 'custom_' . $field['cfc_id'];
            if ($field['cfc_is_device']) {
                $deviceFields[$key] = $field['cfc_name'] . ' ('.trans('Device Custom Field').')';
            } else {
                $siteFields[$key] = $field['cfc_name'] . ' ('.trans('Site Custom Field').')';
            }
        }

        return [$siteFields, $deviceFields];
    }

    private function getSiteFields($customFields)
    {
        return array_merge([
            'site_name' => trans('Installation Name'),
            'site_module_name' => trans('Site module type'),
            'mac_address' => trans('Mac Address'),
            'imei_number' => trans('Imei number'),
        ], $customFields);
    }

    private function getAdditionalFields($customFields)
    {
        return array_merge([
            'device_module_type' => trans('device type'),
            'device_module_name' => trans('device module type'),
            'device_firmware'     => trans('Firmware Version'),
            'device_enabled'      => trans('Device Status'),
            'device_created' => trans('device_created'),
            'device_deleted' => trans('Deleted at'),
            'device_lastset' => trans('Last set date'),
            'device_lastrevival' => trans('Last revival date'),
            'device_lastreported' => trans('Last reported date'),
            'device_lasttech' => trans('Last tech date'),
            'device_lastalarm' => trans('Last active alarm'),
            'active_warnings' => trans('Active Warnings'),
            'active_errors' => trans('Active Errors'),
            'overdue' => trans('Overdue')
        ], $customFields);
    }

    private function getRelationFlags(array $header): array
    {
        $fields = array_keys($header);
        $flags = [
            'device_module'        => false,
            'device_custom_fields' => false,
            'device_alerts'        => false,
            'device_latest_comment'=> false,
            'site_custom_fields'   => false,
            'site_numbers'         => false,
            'site_labels'          => false,
            'site_address'         => false,
            'site_module'          => false,
            'needs_device_site'    => false,
            'needs_last_alarm'     => false,
        ];

        foreach ($fields as $field) {
            if (str_starts_with($field, 'custom_')) {
                $flags['device_custom_fields'] = true;
                $flags['site_custom_fields']   = true;
                $flags['needs_device_site']    = true;
                continue;
            }

            switch ($field) {
                case 'active_warnings':
                case 'active_errors':
                case 'overdue':
                    $flags['device_alerts'] = true;
                    break;
                case 'device_lastalarm':
                    $flags['device_alerts'] = true;
                    $flags['needs_last_alarm'] = true;
                    break;
                case 'comments':
                    $flags['device_latest_comment'] = true;
                    break;
                case 'device_module_type':
                case 'device_module_name':
                    $flags['device_module'] = true;
                    break;
                case 'site_module_name':
                    $flags['site_module'] = true;
                    $flags['needs_device_site'] = true;
                    break;
                case 'mac_address':
                case 'imei_number':
                    $flags['needs_device_site'] = true;
                    break;
                case 'address':
                    $flags['site_address'] = true;
                    $flags['needs_device_site'] = true;
                    break;
                case 'labels':
                    $flags['site_labels'] = true;
                    $flags['needs_device_site'] = true;
                    break;
                case 'pbx':
                case 'pstn':
                case 'sim':
                case 'sip':
                    $flags['site_numbers'] = true;
                    $flags['needs_device_site'] = true;
                    break;
                case 'site_name':
                case 'site_id':
                    $flags['needs_device_site'] = true;
                    break;
            }
        }

        return $flags;
    }

    protected function logMemoryUsage(string $context): void
    {
        Log::debug('ExportDevices memory', [
            'context'   => $context,
            'usage_mb'  => round(memory_get_usage(true) / 1048576, 2),
            'peak_mb'   => round(memory_get_peak_usage(true) / 1048576, 2),
        ]);
    }

    private function getOptimizedSiteRelations(array $header, ?array $flags = null): array
    {
        $flags ??= $this->getRelationFlags($header);

        $deviceWith = [];
        if ($flags['device_module']) {
            $deviceWith[] = 'module.module_type';
        }
        if ($flags['device_custom_fields']) {
            $deviceWith[] = 'custom_fields';
        }
        if ($flags['device_latest_comment']) {
            $deviceWith[] = 'latest_comment';
        }
        if ($flags['device_alerts']) {
            $deviceWith[] = 'device_alerts.alert_type.alert_severity';
        }
        if ($flags['needs_device_site']) {
            $deviceWith['device_site'] = function($q) use ($flags) {
                $siteWith = [];
                if ($flags['site_address']) {
                    $siteWith[] = 'address.location';
                }
                if ($flags['site_numbers']) {
                    $siteWith[] = 'numbers';
                }
                if ($flags['site_labels']) {
                    $siteWith[] = 'labels';
                }
                if ($flags['site_module']) {
                    $siteWith[] = 'module.module_type';
                }
                if ($flags['site_custom_fields']) {
                    $siteWith[] = 'custom_fields';
                }
                if (!empty($siteWith)) {
                    $q->with($siteWith);
                }
            };
        }

        $relations = [
            'devices' => function($q) use ($deviceWith, $flags) {
                if (!empty($deviceWith)) {
                    $q->with($deviceWith);
                }
                if ($flags['needs_last_alarm']) {
                    $this->applyLastAlarmAggregate($q);
                }
            },
        ];

        if ($flags['site_module']) {
            $relations[] = 'module.module_type';
        }
        if ($flags['site_numbers']) {
            $relations[] = 'numbers';
        }
        if ($flags['site_address']) {
            $relations[] = 'address.location';
        }
        if ($flags['site_custom_fields']) {
            $relations[] = 'custom_fields';
        }
        if ($flags['site_labels']) {
            $relations[] = 'labels';
        }
        // no direct gateway relation on DeviceSite

        return $relations;
    }


    private function getOptimizedDeviceRelations(array $header, ?array $flags = null): array
    {
        $flags ??= $this->getRelationFlags($header);

        $relations = [];
        if ($flags['device_module']) {
            $relations[] = 'module.module_type';
        }
        if ($flags['device_custom_fields']) {
            $relations[] = 'custom_fields';
        }
        if ($flags['device_latest_comment']) {
            $relations[] = 'latest_comment';
        }
        if ($flags['device_alerts']) {
            $relations[] = 'device_alerts.alert_type.alert_severity';
        }
        if ($flags['needs_device_site']) {
            $relations['device_site'] = function($q) use ($flags) {
                $siteWith = [];
                if ($flags['site_address']) {
                    $siteWith[] = 'address.location';
                }
                if ($flags['site_numbers']) {
                    $siteWith[] = 'numbers';
                }
                if ($flags['site_labels']) {
                    $siteWith[] = 'labels';
                }
                if ($flags['site_module']) {
                    $siteWith[] = 'module.module_type';
                }
                if ($flags['site_custom_fields']) {
                    $siteWith[] = 'custom_fields';
                }
                if (!empty($siteWith)) {
                    $q->with($siteWith);
                }
            };
        }

        return $relations;
    }

    private function generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields)
    {
        $row = [];
        foreach ($header as $key => $value) {
            $cellValue = '';

            if (str_starts_with($key, 'custom_')) {
                $customFieldId = (int) substr($key, 7);
                if ($device->custom_fields) {
                    $fieldValue = $device->custom_fields->where('cfv_cfc_id', $customFieldId)->first();
                    if ($fieldValue) {
                        $cellValue = $fieldValue->cfv_value;
                    }
                }
                if ($cellValue === '' && $device->device_site?->custom_fields) {
                    $fieldValue = $device->device_site->custom_fields->where('cfv_cfc_id', $customFieldId)->first();
                    if ($fieldValue) {
                        $cellValue = $fieldValue->cfv_value;
                    }
                }
            } elseif (isset($siteFields[$key])) {
                if ($key === 'site_name') {
                    $cellValue = $device['device_site']['ds_name'] ?? '';
                } elseif ($key === 'site_module_name') {
                    $cellValue = $device['device_site']['module']['module_desc']
                        ?? $device['device_site']['module']['module_name']
                        ?? '';
                } elseif ($key === 'mac_address') {
                    $cellValue = $device['device_site']['gateway']['dg_mac'] ?? '';
                } elseif ($key === 'imei_number') {
                    $cellValue = $device['device_site']['gateway']['dg_imei'] ?? '';
                }
            } elseif (isset($additionalFields[$key])) {
                if ($key === 'device_lastalarm') {
                    if (array_key_exists('last_alarm_at', $device->getAttributes())) {
                        $lastAlarm = $device->last_alarm_at;
                    } else {
                        $lastAlarm = $device->getLastActiveAlarm();
                    }
                    $cellValue = $lastAlarm ? toUserDateTime($lastAlarm) : '';
                } elseif ($key === 'device_firmware') {
                    $cellValue = $device[$key] ?? '';
                } elseif ($key === 'device_enabled') {
                    $cellValue = $device[$key] ? trans('Enabled') : trans('Disabled');
                } elseif (Str::contains($key, 'device_')) {
                    $cellValue = !empty($device[$key]) ? toUserDateTime($device[$key]) : '';
                } elseif ($key === 'active_warnings') {
                    $warnings = collect($device->device_alerts ?? [])->filter(function($alert) {
                        return $alert->alert_type &&
                            $alert->alert_type->alert_severity &&
                            $alert->alert_type->alert_severity->as_type === 'MINOR';
                    })->map(function($warning) use ($alertTranslations) {
                        return $alertTranslations[$warning->alert_type->at_type] ?? '';
                    })->filter()->values();
                    $cellValue = $warnings->implode(' | ');
                } elseif ($key === 'active_errors') {
                    $errors = collect($device->device_alerts ?? [])->filter(function($alert) {
                        return $alert->alert_type &&
                            $alert->alert_type->alert_severity &&
                            $alert->alert_type->alert_severity->as_type === 'MAJOR';
                    })->map(function($error) use ($alertTranslations) {
                        return $alertTranslations[$error->alert_type->at_type] ?? '';
                    })->filter()->values();
                    $cellValue = $errors->implode(' | ');
                } elseif ($key === 'overdue') {
                    foreach ($device->device_alerts ?? [] as $alert) {
                        if ($alert->alert_type && $alert->alert_type->at_type === 'PERIODICAL') {
                            $cellValue = toUserDateTime($alert->da_timestamp);
                            break;
                        }
                    }
                } elseif ($key === 'device_module_type') {
                    $cellValue = $device->module?->module_type?->mt_type
                        ?? $device->module?->module_type?->mt_desc
                        ?? '';
                } elseif ($key === 'device_module_name') {
                    $cellValue = $device->module?->module_desc
                        ?? $device->module?->module_name
                        ?? '';
                }
            } else {
                if (in_array($key, ['pbx', 'pstn', 'sim', 'sip'])) {
                    $cellValue = $device['device_site'][$key]['number_value'] ?? '';
                } elseif ($key === 'address') {
                    $cellValue = $device['device_site']['address']['in_one_line'] ?? '';
                } elseif ($key === 'site_id') {
                    $cellValue = $device['device_site']['ds_id'] ?? '';
                } elseif ($key === 'device_id') {
                    $cellValue = $device['device_id'] ?? '';
                } elseif ($key === 'comments') {
                    $cellValue = $device['latest_comment']['dc_text'] ?? '';
                } elseif ($key === 'labels') {
                    $labels = collect($device['device_site']['labels'] ?? [])->pluck('dl_name')->filter()->values();
                    $cellValue = $labels->implode(' | ');
                } else {
                    $cellValue = $device['device_' . $key] ?? '';
                }
            }

            $row[] = $cellValue;
        }

        return $row;
    }

    private function applyLastAlarmAggregate(Builder|Relation $builder): void
    {
        $alarmality = $this->alarmalityTypesCache ??= array_keys(array_filter($this->getAlertAlarmalityStates()));
        if (empty($alarmality)) {
            return;
        }

        $query = $builder instanceof Relation ? $builder->getQuery() : $builder;
        $qualifiedDeviceId = $query->qualifyColumn('device_id');

        $query->addSelect([
            'last_alarm_at' => function($sub) use ($alarmality, $qualifiedDeviceId) {
                $sub->selectRaw('MAX(alerts.alert_timestamp)')
                    ->from('sessions')
                    ->join('alerts', 'alerts.alert_session_id', '=', 'sessions.session_id')
                    ->join('alert_types', 'alerts.alert_at_id', '=', 'alert_types.at_id')
                    ->whereColumn('sessions.session_device_id', $qualifiedDeviceId)
                    ->whereIn('alert_types.at_type', $alarmality);
            }
        ]);
    }

    protected function updateProgressFile(string $file, int $processed, int $total): void
    {
        if ($total === 0) {
            return;
        }

        $percent = (int) round(($processed / $total) * 100);
        @file_put_contents($file, $percent);
    }
}
