<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SearchDeviceService;
use App\Services\CustomFieldsService;
use App\Exports\GeneratorExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Device;
use App\Models\Account;
use Illuminate\Support\Facades\Log;
use App\Scopes\DevicesByAccountScope;

class ExportDevicesCommand extends Command
{
    protected $signature = 'devices:export {account_id} {output} {--format=csv}';
    protected $description = 'Export all devices for an account to CSV or Excel (XLSX) file';

    public function handle()
    {
        $accountId = (int) $this->argument('account_id');
        $outputArg = $this->argument('output');
        $outputFilename = basename($outputArg);
        $output = base_path($outputFilename);
        $format = strtolower($this->option('format'));

        // Validate account
        $account = Account::find($accountId);
        if (!$account) {
            $this->error("Account not found: $accountId");
            return 1;
        }

        $searchService = new SearchDeviceService();
        $accountTranslation = $account->account_translation;
        $filters = [
            'alerts' => [],
            'groups' => [],
            'search_selected' => [],
            'search' => '',
            'sortDirection' => 'asc',
            'sortedby' => 'device_equipment',
        ];
        $locale = $account->account_locale_id ? ($account->account_locale->language->language_code ?? 'en') : 'en';
        app()->setLocale($locale);

        $fieldList = $this->getFieldTranslationsFromProfile($accountTranslation, $locale);
        unset($fieldList['numbers']);
        [$customSiteFields, $customDeviceFields] = $this->getCustomFieldsFromProfile($accountTranslation);
        $siteFields = $this->getSiteFields($customSiteFields);
        $additionalFields = $this->getAdditionalFields($customDeviceFields);
        $initialList = array_merge($siteFields, $fieldList, $additionalFields);
        $csvHeaderLabels = array_merge([
            'site_id' => __('Installation ID'),
            'device_id' => __('Device ID'),
        ], $initialList);
        $exportList = array_keys($initialList);

        $alertTranslations = $this->getAlertTranslationsFromProfile($accountTranslation, $locale);

        $devices = Device::withoutGlobalScope(DevicesByAccountScope::class)
            ->where('device_account_id', $accountId)
            ->where('device_enabled', true)
            ->with([
                'module',
                'module.module_type',
                'device_site' => function ($query) {
                    $query->withoutGlobalScope(\App\Scopes\DeviceSitesByAccountScope::class);
                },
                'device_site.address' => function ($query) {
                    $query->withoutGlobalScope(\App\Scopes\DeviceSitesByAccountScope::class);
                },
                'device_site.address.location' => function ($query) {
                    $query->withoutGlobalScope(\App\Scopes\DeviceSitesByAccountScope::class);
                },
                'device_site.numbers.number_type' => function ($query) {
                    $query->withoutGlobalScope(\App\Scopes\DeviceSitesByAccountScope::class);
                },
                'custom_fields',
                'device_site.custom_fields' => function ($query) {
                    $query->withoutGlobalScope(\App\Scopes\DeviceSitesByAccountScope::class);
                },
                'device_site.labels' => function ($query) {
                    $query->withoutGlobalScope(\App\Scopes\DeviceSitesByAccountScope::class);
                },
            ])
            ->get();


        $rows = [];
        foreach ($devices as $device) {
            $rows[] = $this->generateCsvRow($csvHeaderLabels, $device, $siteFields, $additionalFields, $locale, $alertTranslations);
        }

        if ($format === 'xlsx' || Str::endsWith($output, '.xlsx')) {
            $gen     = (static function () use ($rows) { yield from $rows; })();
            $content = \Maatwebsite\Excel\Facades\Excel::raw(new GeneratorExport($gen, array_values($csvHeaderLabels)), \Maatwebsite\Excel\Excel::XLSX);
            file_put_contents($output, $content);
            $this->info("Exported to Excel: $output");
        } else {
            $file = fopen($output, 'w');
            if (!$file) {
                $this->error("Failed to open file for writing: $output");
                return 1;
            }
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, array_values($csvHeaderLabels));
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
            $this->info("Exported to CSV: $output");
        }
        return 0;
    }


    private function getFieldTranslationsFromProfile($accountTranslation, $locale)
    {
        $formTranslations = $accountTranslation['translations'][$locale]['device']['field'] ?? [];
        $outputTranslations = [];
        foreach ($formTranslations as $field => $label) {
            $outputTranslations[$field] = $label;
        }
        return $outputTranslations;
    }

    private function getCustomFieldsFromProfile($accountTranslation)
    {
        $customFields = $accountTranslation['custom_fields'] ?? [];
        $siteFields = [];
        $deviceFields = [];
        foreach ($customFields as $field) {
            if (!is_array($field) || !array_key_exists('cfc_id', $field)) {
                continue; // skip malformed or incomplete custom field entries
            }
            $key = 'custom_' . $field['cfc_id'];
            if (!empty($field['cfc_is_device'])) {
                $deviceFields[$key] = $field['cfc_name'] . ' (' . __('Device Custom Field') . ')';
            } else {
                $siteFields[$key] = $field['cfc_name'] . ' (' . __('Site Custom Field') . ')';
            }
        }
        return [$siteFields, $deviceFields];
    }

    private function getSiteFields($customFields)
    {
        return array_merge([
            'site_name' => __('Installation Name'),
            'site_module_name' => __('Site module type'),
            'mac_address' => __('Mac Address'),
            'imei_number' => __('Imei number'),
        ], $customFields);
    }

    private function getAdditionalFields($customFields)
    {
        return array_merge([
            'device_module_type' => __('device type'),
            'device_module_name' => __('device module type'),
            'device_firmware'     => __('Firmware Version'),
            'device_enabled'      => __('Device Status'),
            'device_created' => __('device_created'),
            'device_deleted' => __('Deleted at'),
            'device_lastset' => __('Last set date'),
            'device_lastrevival' => __('Last revival date'),
            'device_lastreported' => __('Last reported date'),
            'device_lasttech' => __('Last tech date'),
            'device_lastalarm' => __('Last active alarm'),
            'active_warnings' => __('Active Warnings'),
            'active_errors' => __('Active Errors'),
            'overdue' => __('Overdue')
        ], $customFields);
    }

    private function getAlertTranslationsFromProfile($accountTranslation, $locale)
    {
        $alertTranslations = [];
        $alertData = $accountTranslation['translations'][$locale]['alert']['type'] ?? [];
        foreach ($alertData as $field => $label) {
            $alertTranslations[$field] = $label;
        }
        return $alertTranslations;
    }

    private function generateCsvRow($header, $device, $siteFields, $additionalFields, $locale, $alertTranslations)
    {
        $row = [];
        foreach ($header as $key => $value) {
            $cellValue = '';
            if (str_starts_with($key, 'custom_') || $key === 'labels') {
                $row[] = $cellValue;
                continue;
            }
            // Site fields
            if (isset($siteFields[$key])) {
                if ($key == 'site_name') {
                    $cellValue = $device->device_site->ds_name ?? '';
                } elseif ($key == 'site_module_name') {
                    $cellValue = $device->device_site->module->module_desc
                        ?? $device->device_site->module->module_name
                        ?? '';
                } elseif ($key == 'mac_address') {
                    $cellValue = $device->device_site->gateway->dg_mac ?? '';
                } elseif ($key == 'imei_number') {
                    $cellValue = $device->device_site->gateway->dg_imei ?? '';
                }
            } elseif (isset($additionalFields[$key])) {
                if ($key === 'device_lastalarm') {
                    $cellValue = '';
                } elseif ($key === 'device_firmware') {
                    $cellValue = $device->device_firmware ?? '';
                } elseif ($key === 'device_enabled') {
                    $cellValue = $device->device_enabled ? __('Enabled') : __('Disabled');
                } elseif (str_contains($key, 'device_')) {
                    $cellValue = !empty($device->{$key}) ? $this->toUserDateTime($device->{$key}) : '';
                } elseif ($key == 'active_warnings') {
                    $warnings = collect($device->warnings ?? [])->map(function($warning) use ($alertTranslations) {
                        return $alertTranslations[$warning['alert_type']['at_type']] ?? $warning['alert_type']['at_type'] ?? '';
                    })->filter()->values();
                    $cellValue = $warnings->implode(' | ');
                } elseif ($key == 'active_errors') {
                    $errors = collect($device->errors ?? [])->map(function($error) use ($alertTranslations) {
                        return $alertTranslations[$error['alert_type']['at_type']] ?? $error['alert_type']['at_type'] ?? '';
                    })->filter()->values();
                    $cellValue = $errors->implode(' | ');
                } elseif ($key == 'overdue') {
                    foreach ($device->warnings ?? [] as $warning) {
                        if (($warning['alert_type']['at_type'] ?? null) == 'PERIODICAL') {
                            $cellValue = $this->toUserDateTime($warning['da_timestamp'] ?? null);
                            break;
                        }
                    }
                } elseif ($key == 'device_module_type') {
                    $cellValue = $device->module->module_type->mt_type
                        ?? $device->module->module_type->mt_desc
                        ?? '';
                } elseif ($key == 'device_module_name') {
                    $cellValue = $device->module->module_desc
                        ?? $device->module->module_name
                        ?? '';
                }
            } else {
                if (in_array($key, ['pbx', 'pstn', 'sim', 'sip'])) {
                    $cellValue = optional(optional($device->device_site)->{$key})->number_value ?? '';
                } elseif ($key == 'address') {
                    $cellValue = optional(optional($device->device_site)->address)->in_one_line ?? '';
                } elseif ($key == 'site_id') {
                    $cellValue = optional($device->device_site)->ds_id ?? '';
                } elseif ($key == 'device_id') {
                    $cellValue = $device->device_id ?? '';
                } elseif ($key == 'comments') {
                    $cellValue = optional($device->latest_comment)->dc_text ?? '';
                } else {
                    $cellValue = $device->{'device_' . $key} ?? '';
                }
            }
            $row[] = $cellValue;
        }
        return $row;
    }

    private function toUserDateTime($value)
    {
        if (empty($value)) return '';
        // You can adapt this to your preferred date format
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return (string) $value;
        }
    }
} 