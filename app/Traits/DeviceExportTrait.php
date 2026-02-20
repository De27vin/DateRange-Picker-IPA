<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait DeviceExportTrait
{
    private function getCustomFields($service)
    {
        $customFields = $service->getAccountCustomFieldsConfig(session('account.id'));
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

    private function getOptimizedSiteRelations(): array
    {
        return [
            'devices' => function($q) {
                $q->with([
                    'module.module_type',
                    'custom_fields',
                    'latest_comment',
                    'device_alerts.alert_type.alert_severity'
                ]);
            },
            'devices.gateway',
            'module.module_type',
            'numbers',
            'address.location',
            'custom_fields',
            'labels'
        ];
    }

    private function getOptimizedDeviceRelations(): array
    {
        return [
            'module.module_type',
            'custom_fields',
            'latest_comment',
            'device_alerts.alert_type.alert_severity',
            'device_site' => function($q) {
                $q->with([
                    'address.location',
                    'numbers',
                    'labels',
                    'gateway',
                    'module.module_type',
                    'custom_fields'
                ]);
            }
        ];
    }

    private function quoteFields($fields): array
    {
        return array_map(fn($field) => '"' . str_replace('"', '""', $field) . '"', $fields);
    }

    private function generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields)
    {
        $row = [];
        foreach ($header as $key => $value) {
            $cellValue = '';

            // Custom fields
            if (str_starts_with($key, 'custom_')) {
                $customFieldId = (int) substr($key, 7);
                if ($device->custom_fields) {
                    $field = $device->custom_fields->where('cfv_cfc_id', $customFieldId)->first();
                    if ($field) $cellValue = $field->cfv_value;
                }
                if (empty($cellValue) && $device->device_site?->custom_fields) {
                    $field = $device->device_site->custom_fields->where('cfv_cfc_id', $customFieldId)->first();
                    if ($field) $cellValue = $field->cfv_value;
                }
            }
            // Site fields
            elseif (isset($siteFields[$key])) {
                if ($key == 'site_name') {
                    $cellValue = $device['device_site']['ds_name'] ?? '';
                } elseif ($key == 'site_module_name') {
                    $cellValue = $device['device_site']['module']['module_desc']
                        ?? $device['device_site']['module']['module_name'] ?? '';
                } elseif ($key == 'mac_address') {
                    $cellValue = $device['device_site']['gateway']['dg_mac'] ?? '';
                } elseif ($key == 'imei_number') {
                    $cellValue = $device['device_site']['gateway']['dg_imei'] ?? '';
                }
            }
            // Additional device fields
            elseif (isset($additionalFields[$key])) {
                if ($key === 'device_lastalarm') {
                    $lastAlarm = $device->getLastActiveAlarm();
                    $cellValue = $lastAlarm ? toUserDateTime($lastAlarm) : '';
                } elseif ($key === 'device_firmware') {
                    $cellValue = $device[$key] ?? '';
                } elseif ($key === 'device_enabled') {
                    $cellValue = $device[$key] ? trans('Enabled') : trans('Disabled');
                } elseif (Str::contains($key, 'device_')) {
                    $cellValue = !empty($device[$key]) ? toUserDateTime($device[$key]) : '';
                } elseif ($key == 'active_warnings') {
                    $warnings = collect($device->device_alerts ?? [])->filter(fn($a) => $a->alert_type && $a->alert_type->alert_severity && $a->alert_type->alert_severity->as_type === 'MINOR')
                        ->map(fn($w) => $alertTranslations[$w->alert_type->at_type] ?? '')->filter()->values();
                    $cellValue = $warnings->implode(' | ');
                } elseif ($key == 'active_errors') {
                    $errors = collect($device->device_alerts ?? [])->filter(fn($a) => $a->alert_type && $a->alert_type->alert_severity && $a->alert_type->alert_severity->as_type === 'MAJOR')
                        ->map(fn($e) => $alertTranslations[$e->alert_type->at_type] ?? '')->filter()->values();
                    $cellValue = $errors->implode(' | ');
                } elseif ($key == 'overdue') {
                    foreach ($device->device_alerts ?? [] as $alert) {
                        if ($alert->alert_type && $alert->alert_type->at_type === 'PERIODICAL') {
                            $cellValue = toUserDateTime($alert->da_timestamp);
                            break;
                        }
                    }
                } elseif ($key == 'device_module_type') {
                    $cellValue = $device->module?->module_type?->mt_type ?? $device->module?->module_type?->mt_desc ?? '';
                } elseif ($key == 'device_module_name') {
                    $cellValue = $device->module?->module_desc ?? $device->module?->module_name ?? '';
                }
            }
            // Generic fallbacks
            else {
                if (in_array($key, ['pbx','pstn','sim','sip'])) {
                    $cellValue = $device['device_site'][$key]['number_value'] ?? '';
                } elseif ($key == 'address') {
                    $cellValue = $device['device_site']['address']['in_one_line'] ?? '';
                } elseif ($key == 'site_id') {
                    $cellValue = $device['device_site']['ds_id'] ?? '';
                } elseif ($key == 'device_id') {
                    $cellValue = $device['device_id'] ?? '';
                } elseif ($key == 'comments') {
                    $cellValue = $device['latest_comment']['dc_text'] ?? '';
                } elseif ($key == 'labels') {
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
} 