<?php

namespace App\Http\Controllers;

use App\Exports\DevicesExport;
use App\Exports\DevicesExportGenerator;
use App\Models\Device;
use App\Models\DeviceSite;
use App\Services\CustomFieldsService;
use App\Services\SearchDeviceService;
use App\Traits\AccountsTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use App\Traits\DeviceExportTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExportDevicesController extends Controller
{
    use TranslationsTrait, AccountsTrait, SearchFiltersTrait, DeviceExportTrait;

    private SearchDeviceService $searchService;

    public function __construct()
    {
        $this->searchService = new SearchDeviceService();
    }

    public function export(Request $request)
    {
        // Set execution limits for large exports
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '500M');

        // Get parameters from request
        $filtersId    = $request->input('filters_id');
        $exportList   = json_decode($request->input('export_list'), true) ?? [];
        $exportSites  = $request->boolean('export_sites', false);
        $exportFormat = $request->input('format', 'csv');

        // Unique id to allow multiple concurrent exports per user
        $downloadId   = $request->input('download_id');
        if (!$downloadId) {
            $downloadId = (string) Str::uuid();
        }

        $progressFile = storage_path('framework/cache/export_devices_' . auth()->id() . '_' . $downloadId . '.txt');
        // initialise progress file
        @file_put_contents($progressFile, '0');

        // Prepare all the field mappings and translations (same as Livewire component)
        $alertTranslations = $this->getAlertTranslations(session('locale', 'en'));
        $fieldList = $this->getFieldTranslations(session('locale', 'en'));
        unset($fieldList['numbers']);

        $customFieldService = new CustomFieldsService();
        [$customSiteFields, $customDeviceFields] = $this->getCustomFields($customFieldService);

        $siteFields = $this->getSiteFields($customSiteFields);
        $additionalFields = $this->getAdditionalFields($customDeviceFields);
        
        $initialList = array_merge($siteFields, $fieldList, $additionalFields);
        $csvHeaderLabels = array_merge(
            ['site_id' => trans('Installation ID'), 'device_id' => trans('Device ID')],
            $initialList
        );

        // Build header using the same logic
        $header = array_intersect_key($csvHeaderLabels,
            array_flip(array_merge(['site_id', 'device_id'], $exportList))
        );

        if ($exportFormat === 'xlsx') {
            // Asynchronous generation – queue a job and return immediately
            $filters = $this->getDeviceSearchFilter($filtersId);

            \App\Jobs\GenerateDevicesExportJob::dispatchAfterResponse(
                $filters,
                $exportList,
                $exportSites,
                session('locale', 'en'),
                $downloadId,
                auth()->id()
            );

            return response()->json([
                'status'      => 'queued',
                'download_id' => $downloadId
            ]);
        }

        // For CSV, use streaming
        // Calculate total rows for CSV
        $filters = $this->getDeviceSearchFilter($filtersId);
        if ($exportSites) {
            $totalRows = $this->searchService->buildDeviceSitesQuery($filters)
                        ->withCount('devices')
                        ->get()->sum('devices_count');
        } else {
            $base = Device::query()->where('device_enabled', true);
            $totalRows = $this->searchService->buildDevicesQuery($filters, true, $base, true)->count();
        }

        return response()->stream(function() use ($filtersId, $exportSites, $header, $alertTranslations, $siteFields, $additionalFields, $progressFile, $totalRows) {
            $file = fopen('php://output', 'w');
            
            // Write UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write header
            fwrite($file, implode(',', $this->quoteFields(array_values($header))) . "\n");
            
            // Process data in chunks
            $this->streamRows($filtersId, $exportSites, $header, $alertTranslations, $siteFields, $additionalFields, $file, $progressFile, $totalRows);
            
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="devices_' . date('d-m-Y') . '.csv"',
        ]);
    }

    private function streamRows($filtersId, $exportSites, $header, $alertTranslations, $siteFields, $additionalFields, $file, $progressFile, $totalRows)
    {
        $filters = $this->getDeviceSearchFilter($filtersId);
        
        if ($exportSites) {
            $query = $this->searchService->buildDeviceSitesQuery($filters);
            $query->with($this->getOptimizedSiteRelations());
            
            // Process sites in chunks
            $processed = 0;
            $query->chunk(100, function($sites) use ($header, $alertTranslations, $siteFields, $additionalFields, $file, &$processed, $progressFile, $totalRows) {
                foreach ($sites as $site) {
                    foreach ($site->devices as $device) {
                        $row = $this->generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields);
                        fputcsv($file, $row, ',', '"', '\\', "\n");

                        $processed++;
                        $this->updateProgressFile($progressFile, $processed, $totalRows);
                    }
                }
                flush(); // Send to browser immediately
            });
        } else {
            $base = Device::query()->where('device_enabled', true);
            $query = $this->searchService->buildDevicesQuery($filters, true, $base, true);
            $query->with($this->getOptimizedDeviceRelations());
            $processed = 0;
            // Use cursor for memory efficiency
            foreach ($query->cursor() as $device) {
                $row = $this->generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields);
                fputcsv($file, $row, ',', '"', '\\', "\n");
                
                $processed++;
                $this->updateProgressFile($progressFile, $processed, $totalRows);

                // Flush every 100 rows to browser
                if ($processed % 100 === 0) {
                    flush();
                }
            }
        }

        // finish
        $this->updateProgressFile($progressFile, $totalRows, $totalRows);
        register_shutdown_function(function() use ($progressFile) {
            if (file_exists($progressFile)) {
                sleep(2);
                @unlink($progressFile);
            }
        });
    }

    private function collectAllRows($filtersId, $exportSites, $header, $alertTranslations, $siteFields, $additionalFields)
    {
        $rows = [];
        $filters = $this->getDeviceSearchFilter($filtersId);
        
        if ($exportSites) {
            $query = $this->searchService->buildDeviceSitesQuery($filters);
            $query->with($this->getOptimizedSiteRelations());
            
            $query->chunk(500, function($sites) use ($header, $alertTranslations, $siteFields, $additionalFields, &$rows) {
                foreach ($sites as $site) {
                    foreach ($site->devices as $device) {
                        $rows[] = $this->generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields);
                    }
                }
            });
        } else {
            $query = $this->searchService->buildDevicesQuery($filters, true, null, true);
            $query->with($this->getOptimizedDeviceRelations());
            
            $query->chunk(1000, function($devices) use ($header, $alertTranslations, $siteFields, $additionalFields, &$rows) {
                foreach ($devices as $device) {
                    $rows[] = $this->generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields);
                }
            });
        }
        
        return $rows;
    }

    // duplicate helper methods removed; provided via DeviceExportTrait

    // This is the exact same generateCsvRow method from the Livewire component
    private function generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields)
    {
        $row = [];
        foreach ($header as $key => $value) {
            $cellValue = '';

            // Handle custom fields
            if (str_starts_with($key, 'custom_')) {
                $customFieldId = (int) substr($key, 7);
                // For device custom fields
                if ($device->custom_fields) {
                    $fieldValue = $device->custom_fields->where('cfv_cfc_id', $customFieldId)->first();
                    if ($fieldValue) {
                        $cellValue = $fieldValue->cfv_value;
                    }
                }
                // For site custom fields
                if (empty($cellValue) && $device->device_site?->custom_fields) {
                    $fieldValue = $device->device_site->custom_fields->where('cfv_cfc_id', $customFieldId)->first();
                    if ($fieldValue) {
                        $cellValue = $fieldValue->cfv_value;
                    }
                }
            }
            // Site fields
            elseif (isset($siteFields[$key])) {
                if ($key == 'site_name') {
                    $cellValue = $device['device_site']['ds_name'] ?? '';
                } elseif ($key == 'site_module_name') {
                    $cellValue = $device['device_site']['module']['module_desc']
                        ?? $device['device_site']['module']['module_name']
                        ?? '';
                } elseif ($key == 'mac_address') {
                    $cellValue = $device['device_site']['gateway']['dg_mac'] ?? '';
                } elseif ($key == 'imei_number') {
                    $cellValue = $device['device_site']['gateway']['dg_imei'] ?? '';
                }
            }
            // Additional fields (device)
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
                    // Filter warnings from device_alerts based on alert severity MINOR
                    $warnings = collect($device->device_alerts ?? [])->filter(function($alert) {
                        return $alert->alert_type && 
                               $alert->alert_type->alert_severity && 
                               $alert->alert_type->alert_severity->as_type === 'MINOR';
                    })->map(function($warning) use ($alertTranslations) {
                        return $alertTranslations[$warning->alert_type->at_type] ?? '';
                    })->filter()->values();
                    $cellValue = $warnings->implode(' | ');
                } elseif ($key == 'active_errors') {
                    // Filter errors from device_alerts based on alert severity MAJOR
                    $errors = collect($device->device_alerts ?? [])->filter(function($alert) {
                        return $alert->alert_type && 
                               $alert->alert_type->alert_severity && 
                               $alert->alert_type->alert_severity->as_type === 'MAJOR';
                    })->map(function($error) use ($alertTranslations) {
                        return $alertTranslations[$error->alert_type->at_type] ?? '';
                    })->filter()->values();
                    $cellValue = $errors->implode(' | ');
                } elseif ($key == 'overdue') {
                    // Find PERIODICAL alerts (overdue warnings)
                    foreach ($device->device_alerts ?? [] as $alert) {
                        if ($alert->alert_type && $alert->alert_type->at_type === 'PERIODICAL') {
                            $cellValue = toUserDateTime($alert->da_timestamp);
                            break;
                        }
                    }
                } elseif ($key == 'device_module_type') {
                    $cellValue = $device->module?->module_type?->mt_type
                        ?? $device->module?->module_type?->mt_desc
                        ?? '';
                } elseif ($key == 'device_module_name') {
                    $cellValue = $device->module?->module_desc
                        ?? $device->module?->module_name
                        ?? '';
                }
            }
            // Other fields
            else {
                if (in_array($key, ['pbx', 'pstn', 'sim', 'sip'])) {
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

    private function updateProgressFile(string $file, int $processed, int $total): void
    {
        if ($total === 0) return;
        $percent = (int) round(($processed / $total) * 100);
        @file_put_contents($file, $percent);
    }

    /**
     * Download previously generated Excel export by download id.
     */
    public function downloadGenerated(string $id)
    {
        $filePath = storage_path('app/exports/devices_' . $id . '.xlsx');

        if (!file_exists($filePath)) {
            abort(404, 'Export file not found or not ready yet');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}