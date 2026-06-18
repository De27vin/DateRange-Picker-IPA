<?php

namespace App\Services\Export;

use App\Models\Device;
use App\Services\CustomFieldsService;
use App\Services\SearchDeviceService;
use App\Traits\AccountsTrait;
use App\Traits\DeviceExportTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;

class DevicesRowGenerator implements RowGeneratorInterface
{
    use TranslationsTrait, AccountsTrait, SearchFiltersTrait, DeviceExportTrait;

    /** Cached between getHeader() and generate() within the same job invocation. */
    private array $cachedHeader       = [];
    private array $cachedSiteFields   = [];
    private array $cachedAdditional   = [];
    private array $cachedAlertTrans   = [];

    public function requiredParams(): array
    {
        return ['filters', 'exportList', 'locale'];
    }

    public function getHeader(array $params): array
    {
        $this->buildContext($params);
        return $this->cachedHeader;
    }

    public function generate(array $params, string $progressFile): \Generator
    {
        if (empty($this->cachedHeader)) {
            $this->buildContext($params);
        }

        $header           = $this->cachedHeader;
        $alertTranslations = $this->cachedAlertTrans;
        $siteFields        = $this->cachedSiteFields;
        $additionalFields  = $this->cachedAdditional;

        $relationFlags = $this->getRelationFlags($header);
        $searchService = new SearchDeviceService();

        if ($params['exportSites']) {
            $query = $searchService->buildDeviceSitesQuery($params['filters']);
            $query->with($this->getOptimizedSiteRelations($header, $relationFlags));

            $totalRows = 0;
            (clone $query)->withCount('devices')->chunk(500, function ($sites) use (&$totalRows) {
                $totalRows += $sites->sum('devices_count');
            });
        } else {
            $base  = Device::query()->where('device_enabled', true);
            $query = $searchService->buildDevicesQuery($params['filters'], true, $base, true);
            $query->with($this->getOptimizedDeviceRelations($header, $relationFlags));
            if ($relationFlags['needs_last_alarm']) {
                $this->applyLastAlarmAggregate($query);
            }
            $totalRows = (clone $query)->count();
        }

        $this->logMemoryUsage('devices_generator_start');

        $processed = 0;

        if ($params['exportSites']) {
            foreach ($query->lazy(100) as $site) {
                foreach ($site->devices as $device) {
                    yield $this->generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields);
                    $processed++;
                    if ($processed % 50 === 0 || $processed === $totalRows) {
                        $this->updateProgressFile($progressFile, $processed, $totalRows);
                    }
                }
            }
        } else {
            foreach ($query->lazy(500) as $device) {
                yield $this->generateCsvRow($header, $device, $alertTranslations, $siteFields, $additionalFields);
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

    private function buildContext(array $params): void
    {
        // In a queue worker there is no HTTP session, so profile data must be loaded
        // directly from the DB using the accountId passed by the Livewire component.
        if (!empty($params['accountId']) && empty($this->profileData)) {
            $account = \App\Models\Account::find($params['accountId']);
            if ($account) {
                $this->profileData = $account->account_translation;
            }
        }

        $locale = $params['locale'];

        $this->cachedAlertTrans = $this->getAlertTranslations($locale);

        $fieldList = $this->getFieldTranslations($locale);
        unset($fieldList['numbers']);

        $customFieldService = new CustomFieldsService();
        // Seed the service's profile data directly so it never falls back to the
        // session-dependent ProfileAccessService path (which would return null in a queue worker).
        if (!empty($this->profileData)) {
            $customFieldService->initProfileData($this->profileData);
        }
        [$customSiteFields, $customDeviceFields] = $this->getCustomFields($customFieldService, $params['accountId'] ?? null);

        $this->cachedSiteFields  = $this->getSiteFields($customSiteFields);
        $this->cachedAdditional  = $this->getAdditionalFields($customDeviceFields);

        $initialList     = array_merge($this->cachedSiteFields, $fieldList, $this->cachedAdditional);
        $csvHeaderLabels = array_merge([
            'site_id'   => trans('Installation ID'),
            'device_id' => trans('Device ID'),
        ], $initialList);

        $this->cachedHeader = array_intersect_key(
            $csvHeaderLabels,
            array_flip(array_merge(['site_id', 'device_id'], $params['exportList']))
        );
    }
}
