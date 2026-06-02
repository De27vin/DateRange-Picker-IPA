<?php
namespace App\Services;

use App\Models\Device;
use App\Searchable\Search;
use App\Searchable\SearchResultCollection;
use App\Traits\DeviceFormTrait;
use App\Traits\DeviceFilterTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** @deprecated  */
class DeviceQueryService
{
    use DeviceFormTrait;
    use DeviceFilterTrait;

    public $sortedby;
    public $groups;
    public $sortDirection;
    public $filters;
    public $sortOptions = [
        'device_id' => 'ID',
        'device_identity' => 'Identity',
        'device_pin' => 'Pin',
        'device_module' => 'Module'
    ];

    public function __construct($searchSelected)
    {
        $this->searchSelected = $searchSelected;
        $this->filters        = $this->getDeviceSearchFilter();
        $this->sortOptions    = json_encode($this->sortOptions);
        $this->sortedby       = 'device_id';
        $this->sortDirection  = 'asc';
        $this->perPage        = 20;
    }

    public function getDeviceSites($rawQuery): Collection
    {
        $searchSelected = $this->searchSelected;
        $searchPhrase = $this->getDeviceSearchFilter()['search'];

        if (!$searchPhrase && !in_array($searchPhrase, ['0', 0])) {
            return $rawQuery
//                ->orderBy($this->sortedby, $this->sortDirection)
                ->orderBy('ds_id', 'ASC')
                ->get()
                ->groupBy('ds_id');
        }

        $searchResults = $this->performSearch($searchPhrase, $searchSelected);
        $resultArray = $this->transformSearchResultsForSites($searchResults, $searchSelected);
        $rawResultIds = $rawQuery->get()->pluck('ds_id')->toArray();

        $searchResult = collect($resultArray)->whereNotNull()
            ->unique()
            ->filter(fn($site) => in_array($site->ds_id, $rawResultIds))
            ->groupBy('ds_id');

        return $searchResult;
    }

    public function getDevicesQueryNew($rawQuery, $state)
    {
        $searchSelected = $this->searchSelected;

        $searchPhrase = $this->getDeviceSearchFilter()['search'];

        if (empty($this->filters['alerts']) && !$searchPhrase && !in_array($searchPhrase, ['0', 0])) {
            return $rawQuery
                ->orderBy($this->sortedby, $this->sortDirection)
                ->orderBy('device_id', 'ASC')
                ->get()
                ->whereNotNull()
                ->groupBy('device_site.ds_id');
        }

        if (!empty($this->filters['alerts'])) {
            $alertDevices = Device::query()->withAlerts($this->filters['alerts'])->get()->keyBy('device_id');
        }

        if (!empty($alertDevices) && !$searchPhrase && !in_array($searchPhrase, ['0', 0])) {
            return $alertDevices->groupBy('device_site.ds_id');
        }

        $searchResults = $this->performSearch($searchPhrase, $searchSelected);
        $resultArray = $this->transformSearchResultsForDevices($searchResults, $state, $searchSelected);
        $resultCollection = collect($resultArray)->whereNotNull()->unique()->keyBy('device_id');

        if (!empty($alertDevices)) {
            $resultCollection = $resultCollection->intersectByKeys($alertDevices);
        }

        return $resultCollection->groupBy('device_site.ds_id');
    }

    private function performSearch($searchPhrase, $searchSelected): SearchResultCollection
    {
        $deviceSearch = [
            'identity' => 'device_identity',
            'equipment' => 'device_equipment',
            'module' => 'device_module',
            'custom1' => 'device_custom1',
            'custom2' => 'device_custom2',
            'custom3' => 'device_custom3',
            'custom4' => 'device_custom4',
            'pin' => 'device_pin',
            'link' => 'device_link',
        ];
        $deviceSiteSearch = [
            'site' => 'ds_name',
        ];
        $moduleSearch = [
            'module' => 'module_name',
        ];
        $numberSearch = [
            'numbers' => 'number_value',
            'pstn' => 'number_value',
            'sim' => 'number_value',
            'sip' => 'number_value',
            'pbx' => 'number_value',
        ];
        $commentSearch = [
            'comments' => 'dc_text',
        ];
        $addressSearch = [
            'address' => 'address_value',
        ];
        $locationSearch = [
            'address' => ['location_value', 'location_postcode'],
        ];

        $allSelected = in_array('all', $searchSelected) || empty($searchSelected);
        $deviceSearch = $allSelected ? $deviceSearch : array_intersect_key($deviceSearch, array_flip($searchSelected));
        $deviceSiteSearch = $allSelected ? $deviceSiteSearch : array_intersect_key($deviceSiteSearch, array_flip($searchSelected));
        $moduleSearch = $allSelected ? $moduleSearch : array_intersect_key($moduleSearch, array_flip($searchSelected));
        $numberSearch = $allSelected ? $numberSearch : array_intersect_key($numberSearch, array_flip($searchSelected));
        $commentSearch = $allSelected ? $commentSearch : array_intersect_key($commentSearch, array_flip($searchSelected));
        $addressSearch = $allSelected ? $addressSearch : array_intersect_key($addressSearch, array_flip($searchSelected));
        $locationSearch = $allSelected ? $locationSearch : array_intersect_key($locationSearch, array_flip($searchSelected));

        $query = (new Search());
        $query = $deviceSearch ? $query->registerModel(\App\Models\Device::class, false, array_values($deviceSearch)) : $query;
        $query = $deviceSiteSearch ? $query->registerModel(\App\Models\DeviceSite::class, false, array_values($deviceSiteSearch)) : $query;
        $query = $moduleSearch ? $query->registerModel(\App\Models\Module::class, false, array_values($moduleSearch)) : $query;
        $query = $numberSearch ? $query->registerModel(\App\Models\Number::class, false, array_unique(array_values($numberSearch))) : $query;
        $query = $commentSearch ? $query->registerModel(\App\Models\DeviceComment::class, false, array_values($commentSearch)) : $query;
        $query = $addressSearch ? $query->registerModel(\App\Models\Address::class, true, array_values($addressSearch)) : $query;
        $query = $locationSearch ? $query->registerModel(\App\Models\Location::class, true, ...array_values($locationSearch)) : $query;

        return $query->search($searchPhrase);
    }

    private function transformSearchResultsForSites($searchResults, $searchSelected): array
    {
        $resultArray = [];

        foreach ($searchResults as $searchResult) {
            $modelName = class_basename($searchResult->searchable);
            switch ($modelName) {
                case 'Device':
                    $resultArray[] = $searchResult->searchable->device_site ?? null;
                    break;
                case 'Address':
                    $resultArray[] = $searchResult->searchable->devices?->first()->device_site ?? null;
                    break;
                case 'DeviceSite':
                    $resultArray[] = $searchResult->searchable;
                    break;
                case 'Module':
                    $deviceSites = $searchResult->searchable->device_sites ?? [];
                    foreach ($deviceSites as $deviceSite) {
                        $resultArray[] = $deviceSite;
                    }
                    break;
                case 'Number':
                    if (!in_array('all', $searchSelected)
                        && !in_array('numbers', $searchSelected)
                        && !in_array(strtolower($searchResult->searchable->number_type->nt_type), $searchSelected)) {
                        continue 2;
                    }
                    $resultArray[] = $searchResult->searchable->device_site ?? null;
                    break;
                case 'DeviceComment':
                    $resultArray[] = $searchResult->searchable->device->device_site ?? null;
                    break;
                case 'Location':
                    $addresses = $searchResult->searchable->addresses;
                    foreach ($addresses as $address) {
                        $resultArray[] = $address->devices?->first()->device_site ?? null;
                    }
                    break;
                default:
                    // code...
                    break;
            }
        }

        return $resultArray;
    }

    private function transformSearchResultsForDevices($searchResults, $state, $searchSelected): array
    {
        $resultArray = [];

        foreach ($searchResults as $searchResult) {
            $modelName = class_basename($searchResult->searchable);
            switch ($modelName) {
                case 'Device':
                    $device = $searchResult->searchable;
                    $resultArray[] = $this->getDeviceByState($device, $state);
                    break;
                case 'Address':
                case 'DeviceSite':
                    $devices = $searchResult->searchable->devices;
                    foreach ($devices as $device) {
                        $resultArray[] = $this->getDeviceByState($device, $state);
                    }
                    break;
                case 'Module':
                    $deviceSites = $searchResult->searchable->device_sites;
                    foreach ($deviceSites as $deviceSite) {
                        $devices = $deviceSite->devices;
                        foreach ($devices as $device) {
                            $resultArray[] = $this->getDeviceByState($device, $state);
                        }
                    }
                    break;
                case 'Number':
                    if (!in_array('all', $searchSelected)
                        && !in_array('numbers', $searchSelected)
                        && !in_array(strtolower($searchResult->searchable->number_type->nt_type), $searchSelected)) {
                        continue 2;
                    }
                    $devices = $searchResult->searchable->device_site->devices ?? [];
                    foreach ($devices as $device) {
                        $resultArray[] = $this->getDeviceByState($device, $state);
                    }
                    break;
                case 'DeviceComment':
                    $device = $searchResult->searchable->device;
                    if ($device) $resultArray[] = $this->getDeviceByState($device, $state);
                    break;
                case 'Location':
                    $addresses = $searchResult->searchable->addresses;
                    foreach ($addresses as $address) {
                        $devices = $address->devices;
                        foreach ($devices as $device) {
                            $resultArray[] = $this->getDeviceByState($device, $state);
                        }
                    }
                    break;
                default:
                    // code...
                    break;
            }
        }

        return $resultArray;
    }

    private function getDeviceByState($device, $state)
    {
        if ($state == 'enabled') {
            if ($device->device_enabled == 1) {
                return $device;
            }
        } elseif ($state == 'disabled') {
            if ($device->device_enabled == 0 && $device->device_deleted == '0000-00-00 00:00:00') {
                return $device;
            }
        } else {
            if ($device->device_deleted != '0000-00-00 00:00:00') {
                return $device;
            }
        }
    }

//    public function getDeviceStates()
//    {
//        // (2024-02-01) Habinho: added account scope in query
//        $filterString = '';
//        $voiceAlertTypeId = \App\Models\AlertType::query()->where('at_type', '=', 'VOICE')->first()->at_id;
//        $result = DB::select('
//            select device_id,
//                   (select count(da_id) from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 1 and da_at_id != ' . $voiceAlertTypeId . ' ) as device_active_warnings,
//                   (select da_timestamp from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 1 and da_at_id != ' . $voiceAlertTypeId . '  order by da_timestamp limit 1) as device_lastactive_warning,
//                   (select count(da_id) from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 2) as device_active_errors,
//                   (select da_timestamp from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 2 order by da_timestamp limit 1) as device_lastactive_error,
//                   device_lastreported,
//                   (select count(da_id) from device_alerts join alert_types on da_at_id = at_id where da_device_id = device_id and at_type = "OVERDUE") as device_overdue,
//                   device_lastset,
//                   IF (lastset.session_warnings = 0 AND lastset.session_errors = 0 AND lastset.session_critical = 0, 1, 0) as device_setok,
//                   device_lastrevival,
//                   IF (lastrevival.session_warnings = 0 AND lastrevival.session_errors = 0 AND lastrevival.session_critical = 0, 1, 0) as device_revivalok
//                   from devices
//                   left join sessions lastreported on device_id = lastreported.session_device_id and device_lastreported = lastreported.session_start
//                   left join sessions lastset on device_id = lastset.session_device_id and device_lastset = lastset.session_start
//                   left join sessions lastrevival on device_id = lastrevival.session_device_id and device_lastrevival = lastrevival.session_start
//                   where device_enabled = 1 and device_account_id = '.session('account.id').'
//        ');
//
//        return collect($result)->mapToGroups(function ($item, $key) {
//            return [collect($item)['device_id'] => $item];
//        });
//    }


}