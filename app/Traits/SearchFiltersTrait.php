<?php
namespace App\Traits;

use Carbon\Carbon;
use App\Helpers\Ucp;
use App\Models\DeviceAlert;
use Illuminate\Support\Facades\Auth;

trait SearchFiltersTrait {

    public function getDateFilter()
    {
        return [
                'dateFromValue'  => Carbon::now()->subDays(30)->startOfDay()->format('d.m.Y'),
                'dateToValue' => Carbon::now()->endOfDay()->format('d.m.Y')
            ];
    }

    public function initDateFilter(\DateTime|string|null $startDate = null, \DateTime|string|null $endDate = null)
    {
        $dateFromValue = null;
        if (!empty($startDate)) {
            if ($startDate instanceof \DateTime) {
                $dateFromValue = Carbon::instance($startDate)->startOfDay()->format('d.m.Y');
            } else {
                $dateFromValue = Carbon::parse($startDate)->startOfDay()->format('d.m.Y');
            }
        }

        $dateToValue = null;
        if (!empty($endDate)) {
            if ($endDate instanceof \DateTime) {
                $dateToValue = Carbon::instance($endDate)->endOfDay()->format('d.m.Y');
            } else {
                $dateToValue = Carbon::parse($endDate)->endOfDay()->format('d.m.Y');
            }
        }

        $dateFilter = [
            'dateFromValue'  => $dateFromValue ?? Carbon::now()->subDays(30)->startOfDay()->format('d.m.Y'),
            'dateToValue' => $dateToValue ?? Carbon::now()->endOfDay()->format('d.m.Y')
        ];

        return $dateFilter;
    }

    public function resetDateFilter()
    {
        $dateFilter = [
            'dateFromValue'  => Carbon::now()->subDays(30)->startOfDay()->format('d.m.Y'),
            'dateToValue' => Carbon::now()->endOfDay()->format('d.m.Y')
        ];
//        session(['dateFilter' => $dateFilter]);
        return $dateFilter;
    }

    public function storeFilter($name, $data)
    {
        session([$name => $data]);
        return $data;
    }

    public function updateDeviceAlerts()
    {
        $result = DeviceAlert::with('alert_type')
            ->get()
            ->mapToGroups(function($item, $key){
                return [$item->da_device_id => $item];
            });
            return $result;
    }

    public function updateDeviceAlertsByDeviceId($device_id)
    {
        if(is_null($device_id)){
            return null;
        }
        $result = DeviceAlert::with('alert_type')
            ->where('da_device_id','=',$device_id)
            ->get()
            ->mapToGroups(function($item, $key){
                return [$item->da_device_id => $item];
            });
            return $result;
    }

    public function getLocale()
    {

        $locale = session('locale', null);
        if($locale == null){
            return Auth::user()->user_timezone;
        } else {
            return $locale;
        }
    }

    public function getSeverityFilter()
    {
        $severityFilterFromSession = session('severityFilter', null);
        if($severityFilterFromSession == null || !is_array($severityFilterFromSession)){
            $severityFilter = [
                'warnings' => false,
                'errors' => false
            ];
            session(['severityFilter' => $severityFilter]);
            return $severityFilter;
        } else {
            return $severityFilterFromSession;
        }
    }

    public function getHistoryFilter()
    {
        $historyFilterFromSession = session('historyFilter', null);
        if($historyFilterFromSession == null){
            $historyFilter = [
                'alarms'  => false,
                'carcalls'  => false,
                'periodicals' => false,
                'sets'   => false,
                'revivals' => false,
                'triggers' => false,
                'calls' => false,
            ];
            session(['historyFilter' => $historyFilter]);
            return $historyFilter;
        } else {
            return $historyFilterFromSession;
        }
    }

    public function resetSeverityFilter()
    {
        $severityFilter = [
            'warnings' => false,
            'errors' => false
        ];
        session(['severityFilter' => $severityFilter]);
        return $severityFilter;
    }
    
    public function resetHistoryFilter()
    {
        $historyFilter = [
            'alarms'  => false,
            'carcalls'  => false,
            'periodicals' => false,
            'sets'   => false,
            'revivals' => false,
            'triggers' => false,
            'calls' => false,
        ];
        session(['historyFilter' => $historyFilter]);
        return $historyFilter;
    }

    public function updateSeverityFilter($severity)
    {
        $severityFilter = $this->getSeverityFilter();
        $severityFilter[$severity] = (!$severityFilter[$severity]);
        session(['severityFilter' => $severityFilter]);
        return $severityFilter;
    }

    public function updateHistoryFilter($activeFilter, bool $state = true)
    {
        try {
            $historyFilterFromSession = $this->getHistoryFilter();
            if (isset($historyFilterFromSession[$activeFilter])) {
                $historyFilterFromSession[$activeFilter] = $state;
            }
            return $this->storeFilter('historyFilter', $historyFilterFromSession);
        } catch(\Throwable $e){
            \Log::warning($e, ['Caught']);
            return $this->resetHistoryFilter();
        }
    }

    public function getStartDate(\DateTime|string|null $startDate = null)
    {
        $dateFilter = $this->initDateFilter($startDate)['dateFromValue'];
        return Ucp::stringDateToUTC($dateFilter, 'start');
    }

    public function getEndDate(\DateTime|string|null $endDate = null)
    {
        $dateFilter = $this->initDateFilter(null, $endDate)['dateToValue'];
        return Ucp::stringDateToUTC($dateFilter)->endOfDay();
    }

    public function initDeviceSearchFilter($device_enabled = true, string $id = '')
    {
        // Get default sorting options based on context
        $sortDefaults = $this->getDefaultSortOptions($id);
        
        $deviceSearchFilter = [
            'tab'                 => 'enabled',
            'search'              => '',
            'identity'            => null,
            'device_pin'          => null,
            'module_id'           => 0,
            'device_number'       => null,
            'device_module'       => null,
            'device_lastreported' => null,
            'overdue'             => false,
            'device_has_warning'  => false,
            'device_has_error'    => false,
            'device_enabled'      => $device_enabled,
            'device_deleted'      => false,
            'device_disabled'     => false,
            // some keys above are rather deprecated - to test and remove
            'sortedby'            => $sortDefaults['default_sort'],
            'sortDirection'       => $sortDefaults['default_direction'],
            'groups'              => [],
            'alerts'              => [],
            'search_selected'     => [],
            'search_tabs'         => [],
            'sort_options'        => $sortDefaults['options'],
        ];

        $base = 'deviceSearchFilter';
        $full = $id ? $base.'.'.$id : $base;

        return $this->storeFilter($full, $deviceSearchFilter);
    }

    public function getDeviceSearchFilter(string $id = '')
    {
        $base = 'deviceSearchFilter';
        $full = $id ? ($base.'.'.$id) : $base;

        $deviceSearchFilter = session($full, null);
        if($deviceSearchFilter == null){
            $deviceSearchFilter = $this->initDeviceSearchFilter(true, $id);
        } else {
            // Ensure sort options are always present
            $sortDefaults = $this->getDefaultSortOptions($id);
            $deviceSearchFilter['sort_options'] = $sortDefaults['options'];
            
            // Set default sort if not present or invalid
            if (empty($deviceSearchFilter['sortedby']) || !isset($sortDefaults['options'][$deviceSearchFilter['sortedby']])) {
                $deviceSearchFilter['sortedby'] = $sortDefaults['default_sort'];
            }
            
            // Set default direction if not present
            if (empty($deviceSearchFilter['sortDirection'])) {
                $deviceSearchFilter['sortDirection'] = $sortDefaults['default_direction'];
            }
        }

        return $this->storeFilter($full, $deviceSearchFilter);
    }

    public function updateDeviceSearchFilter($data = [], string $id = '')
    {
        $deviceSearchFilter = $this->getDeviceSearchFilter($id);

        foreach ($data as $key => $value) {
            if(array_key_exists($key, $deviceSearchFilter)){
                $deviceSearchFilter[$key] = $value;
            }
        }

        $base = 'deviceSearchFilter';
        $full = $id ? ($base.'.'.$id) : $base;
        session([$full => $deviceSearchFilter]);
    }

    public function resetDeviceSearchFilter($device_enabled = true, string $id = '')
    {
        return $this->initDeviceSearchFilter($device_enabled, $id);
    }

    private function getSearchOptions(): array
    {
        $fieldTranslations = $this->getFieldTranslations(session('locale', 'en'));
        $options = [
//            ['value' => 'all', 'label' => __('All')],
            ['value' => 'site', 'label' => __('Installation Name')],
            ['value' => 'module_type', 'label' => __('Module Type')],
            ['value' => 'device_type', 'label' => __('Device Type')],
            ['value' => 'equipment', 'label' => $fieldTranslations['equipment'] ?? __('Equipment')],
            ['value' => 'identity', 'label' => $fieldTranslations['identity']],
            ['value' => 'module_number', 'label' => $fieldTranslations['module']],
            ['value' => 'address', 'label' => $fieldTranslations['address']],
            ['value' => 'link', 'label' => $fieldTranslations['link']],
            ['value' => 'pin', 'label' => $fieldTranslations['pin']],
            ['value' => 'numbers', 'label' => $fieldTranslations['numbers']],
            ['value' => 'pstn', 'label' => $fieldTranslations['pstn']],
            ['value' => 'sip', 'label' => $fieldTranslations['sip']],
            ['value' => 'sim', 'label' => $fieldTranslations['sim']],
            ['value' => 'pbx', 'label' => $fieldTranslations['pbx']],
            ['value' => 'comments', 'label' => $fieldTranslations['comments']],

            // Update - 10.11.24
            ['value' => 'mac', 'label' => $fieldTranslations['mac'] ?? __('MAC')],
            ['value' => 'imei', 'label' => $fieldTranslations['imei'] ?? __('IMEI')],
            ['value' => 'field_value', 'label' => $fieldTranslations['field_value'] ?? __('Custom Fields')],
            ['value' => 'label', 'label' => $fieldTranslations['label'] ?? __('Labels')],
            ['value' => 'group', 'label' => $fieldTranslations['group'] ?? __('Label Groups')],
            // Update - 10.11.24
        ];

        usort($options, function($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        return array_merge(
            [['value' => 'all', 'label' => __('All')]],
            $options
        );
    }

    private function getAlertsForFilters(string $id = '')
    {
        $filters = [];
        $filters['alerts'] = $this->getDeviceSearchFilter($id)['alerts'];

        $alerts = array_filter($this->getAlertTypeDisplayStates());
        foreach ($alerts as $key => $value) {
            $alerts[$key] = !empty($filters['alerts'][$key]);
        }

        return $alerts;
    }

    private function getDefaultSortOptions(string $id = '')
    {
        $fieldTranslations = $this->getFieldTranslations(session('locale', 'en'));
        
        if ($id === 'Equipment') {
            return [
                'default_sort' => 'ds_name',
                'default_direction' => 'asc',
                'options' => [
                    'ds_name' => $fieldTranslations['site_name'] ?? __('Site name'),
                    'ds_modified' => $fieldTranslations['modified'] ?? __('Modified'),
                    'ds_created' => $fieldTranslations['created'] ?? __('Created'),
                ]
            ];
        }
        
        // Default (Dashboard)
        return [
            'default_sort' => 'device_equipment',
            'default_direction' => 'asc',
            'options' => [
                'device_equipment' => $fieldTranslations['equipment'] ?? __('Equipment'),
                'device_identity' => $fieldTranslations['identity'] ?? __('Identity'),
                'device_modified' => $fieldTranslations['modified'] ?? __('Modified'),
                'device_created' => $fieldTranslations['created'] ?? __('Created'),
            ]
        ];
    }
}
