<?php

namespace App\Jobs;

use App\Exports\DevicesExportGenerator;
use App\Services\CustomFieldsService;
use App\Services\SearchDeviceService;
use App\Traits\AccountsTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use App\Traits\DeviceExportTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class GenerateDevicesExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use TranslationsTrait, AccountsTrait, SearchFiltersTrait, DeviceExportTrait;

    private array $filters;
    private array $exportList;
    private bool $exportSites;
    private string $locale;
    private string $downloadId;
    private int $userId;

    public function __construct(array $filters, array $exportList, bool $exportSites, string $locale, string $downloadId, int $userId)
    {
        $this->filters     = $filters;
        $this->exportList  = $exportList;
        $this->exportSites = $exportSites;
        $this->locale      = $locale;
        $this->downloadId  = $downloadId;
        $this->userId      = $userId;
    }

    public function handle()
    {
        // Build field mappings (duplicated from controller for now)
        $alertTranslations = $this->getAlertTranslations($this->locale);
        $fieldList         = $this->getFieldTranslations($this->locale);
        unset($fieldList['numbers']);

        $customFieldService = new CustomFieldsService();
        [$customSiteFields, $customDeviceFields] = $this->getCustomFields($customFieldService);

        $siteFields       = $this->getSiteFields($customSiteFields);
        $additionalFields = $this->getAdditionalFields($customDeviceFields);

        $initialList = array_merge($siteFields, $fieldList, $additionalFields);
        $csvHeaderLabels = array_merge([
            'site_id'   => trans('Installation ID'),
            'device_id' => trans('Device ID'),
        ], $initialList);

        $header = array_intersect_key($csvHeaderLabels, array_flip(array_merge(['site_id', 'device_id'], $this->exportList)));

        $searchService = new SearchDeviceService();
        // Build query depending on exportSites
        if ($this->exportSites) {
            $query = $searchService->buildDeviceSitesQuery($this->filters);
            $query->with($this->getOptimizedSiteRelations());
            $totalRows = (clone $query)->withCount('devices')->get()->sum('devices_count');
        } else {
            $base = \App\Models\Device::query()->where('device_enabled', true);
            $query = $searchService->buildDevicesQuery($this->filters, true, $base, true);
            $query->with($this->getOptimizedDeviceRelations());
            $totalRows = (clone $query)->count();
        }

        // Determine progress file path
        $progressFile = storage_path('framework/cache/export_devices_' . $this->userId . '_' . $this->downloadId . '.txt');
        @file_put_contents($progressFile, '0');

        // Row generator now calls trait method directly
        $rowGenerator = function ($item, $headers) use ($alertTranslations, $siteFields, $additionalFields) {
            if ($this->exportSites) {
                $rows = [];
                foreach ($item->devices as $device) {
                    $rows[] = $this->generateCsvRow($headers, $device, $alertTranslations, $siteFields, $additionalFields);
                }
                return $rows;
            }
            return $this->generateCsvRow($headers, $item, $alertTranslations, $siteFields, $additionalFields);
        };

        // Store file to storage/app/exports
        $filePath = 'exports/devices_' . $this->downloadId . '.xlsx';

        Excel::store(
            new DevicesExportGenerator($query, $header, $rowGenerator, $progressFile, $totalRows),
            $filePath,
            'local'
        );

        // Mark completion and cleanup file after short delay
        @file_put_contents($progressFile, '100');
    }
} 