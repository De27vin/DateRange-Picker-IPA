<?php
namespace App\Traits;

use Carbon\Carbon;
use App\Helpers\Ucp;
use App\Models\DeviceAlert;
use Illuminate\Support\Facades\Auth;

/** @deprecated  */
trait DeviceFilterTrait {

    public function getDateFilter()
    {
        $userTimezone = (Auth::user()->timezone != null ? Auth::user()->timezone : 'Europe/Zurich');
        $dateFilterFromSession = session('dateFilter', null);
        if($dateFilterFromSession == null){
            $dateFilter = [
                'dateFromValue'  => Carbon::now()->subYear()->startOfDay()->format('d.m.Y'),
                'dateToValue' => Carbon::now()->endOfDay()->format('d.m.Y')
            ];
            session(['dateFilter' => $dateFilter]);
            return $dateFilter;
        } else {
            return $dateFilterFromSession;
        }
    }

    public function initDateFilter()
    {
        $dateFilterFromSession = session('dateFilter', null);
        if($dateFilterFromSession == null){
            $dateFilter = [
                'dateFromValue'  => Carbon::now()->subYear()->startOfDay()->format('d.m.Y'),
                'dateToValue' => Carbon::now()->endOfDay()->format('d.m.Y')
            ];
            session(['dateFilter' => $dateFilter]);
            return $dateFilter;
        } else {
            $dateFilterFromSession['dateToValue'] = Carbon::now()->endOfDay()->format('d.m.Y');
            session(['dateFilter' => $dateFilterFromSession]);
            return $dateFilterFromSession;
        }
    }

    public function resetDateFilter()
    {
        $dateFilter = [
            'dateFromValue'  => Carbon::now()->subYear()->startOfDay()->format('d.m.Y'),
            'dateToValue' => Carbon::now()->endOfDay()->format('d.m.Y')
        ];
        session(['dateFilter' => $dateFilter]);
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
                'all'    => true,
                'calls'  => false,
                'alerts' => false,
                'sets'   => false,
                'parrots' => false
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
            'all'    => true,
            'calls'  => false,
            'alerts' => false,
            'sets'   => false,
            'parrots' => false
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

    public function updateHistoryFilter($activeFilter)
    {
        try {
            $historyFilterFromSession = $this->getHistoryFilter();
            $historyFilterFromSession = [
                'all' => ($activeFilter === 'all' ? true : false),
                'calls' => ($activeFilter === 'calls' ? true : false),
                'alerts' => ($activeFilter === 'alerts' ? true : false),
                'sets' => ($activeFilter === 'sets' ? true : false),
                'parrots' => ($activeFilter === 'parrots' ? true : false)
            ];
            return $this->storeFilter('historyFilter', $historyFilterFromSession);
        } catch(\Exception $e){
            return $this->resetHistoryFilter();
        }
    }

    public function getStartDate()
    {
        $dateFilter = $this->getDateFilter();
        return Ucp::stringDateToUTC($dateFilter['dateFromValue'], 'start');
    }

    public function getEndDate()
    {
        $dateFilter = $this->getDateFilter();
        return Ucp::stringDateToUTC($dateFilter['dateToValue'])->endOfDay();
    }

    public function initDeviceSearchFilter($device_enabled = true)
    {
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
            'sortedby'            => 'device_identity',
            'sortDirection'       => 'desc',
            'groups'              => [],
            'alerts'              => [],
        ];

        return $this->storeFilter('deviceSearchFilter', $deviceSearchFilter);
    }

    public function getDeviceSearchFilter()
    {
        $deviceSearchFilter = session('deviceSearchFilter', null);
        if($deviceSearchFilter == null){
            $deviceSearchFilter = $this->initDeviceSearchFilter();
        }
        return $this->storeFilter('deviceSearchFilter', $deviceSearchFilter);
    }

    public function updateDeviceSearchFilter($data = [])
    {
        $deviceSearchFilter = $this->getDeviceSearchFilter();
        foreach ($data as $key => $value) {
            if(array_key_exists($key, $deviceSearchFilter)){
                $deviceSearchFilter[$key] = $value;
            }
        }
        session(['deviceSearchFilter' => $deviceSearchFilter]);
    }

    public function resetDeviceSearchFilter($device_enabled = true)
    {
        return $this->initDeviceSearchFilter($device_enabled);
    }

}
