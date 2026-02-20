<?php
namespace App\Traits;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;

trait DeviceQueryTrait
{
    use SearchFiltersTrait;
    public $filters;
    public $hasFilters        = false;
    public $sortedby;
    public $sortDirection;

    public function getDevicesByTabState($tabState)
    {
        switch($tabState){
            case 'enabled':
                return $this->getDevicesQuery( Device::with('childs', 'device_alerts', 'device_labels', 'device_type', 'latest_comment', 'primary_number', 'sim_number', 'sip_number')->up()->withCount([
                    'childs_enabled', 'childs_disabled', 'childs_deleted'])
                )->get();
                break;
            case 'disabled':
                return $this->getDevicesQuery( Device::with('childs','device_alerts', 'device_labels', 'device_type', 'primary_number', 'sim_number', 'sip_number')->down()->withCount([
                    'childs_enabled', 'childs_disabled', 'childs_deleted'])
                )->get();
                break;
            case 'deleted':
                return $this->getDevicesQuery( Device::with('childs','device_alerts', 'device_labels', 'device_type', 'primary_number', 'sim_number', 'sip_number')->onlyTrashed()->withCount([
                    'childs_enabled', 'childs_disabled', 'childs_deleted'])
                )->get();
                break;
        }
        return (new Collection());
    }

    public function getDevicesQuery($rawQuery)
    {

        $this->hasFilters = false;
        $this->filters = $this->getDeviceSearchFilter();
        $query = $rawQuery
            ->when($this->filters['identity'], function($query, $device_identity){
                $this->hasFilters = true;
                return $query->where('device_identity', 'like', '%'.$device_identity.'%');
            })
            ->when($this->filters['device_pin'], function($query, $device_pin){
                $this->hasFilters = true;
                return $query->where('device_pin', '=', $device_pin);
            })
            ->when($this->filters['device_module'], function($query, $device_module){
                $this->hasFilters = true;
                return $query->where('device_module', '=', $device_module);
            })
            ->when($this->filters['dt_name'], function($query, $dt_name){
                $this->hasFilters = true;
                return $query->where('device_dt_id', '=', $dt_name);
            })
            ->when($this->filters['device_number'], function($query, $device_number){
                $this->hasFilters = true;
                return $query->whereHas('device_numbers', function($q) use ($device_number) {
                    $q->where('dn_value', 'like', '%'.$device_number.'%' );
                });
            })
            ->when($this->filters['groups'], function($query, $device_label){
                $this->hasFilters = true;
                return $query->whereHas('device_labels', function($q) use ($device_label) {
                    if(is_array($device_label)){
                        $q->whereIn('device_labels.dl_id', array_keys($device_label));
                    } else {
                        $q->where('device_labels.dl_id', '=', array_keys($device_label));
                    }
                });
            })
            ->when($this->filters['device_disabled'], function($query, $device_disabled){
                return $query->where('device_enabled', '=', false);
            })
            ->when($this->filters['search'], function($query, $search){
                $this->hasFilters = true;
                return $query->whereNested(function($query) use ($search) {
                    return $query->where('device_identity', 'like', '%'.$search.'%');
//                        ->orWhere('device_tech', 'like', '%'.$search.'%')
//                        ->orWhere('device_custom', 'like', '%'.$search.'%')
//                        ->orWhere('device_custom3', 'like', '%'.$search.'%')
//                        ->orWhere('device_custom4', 'like', '%'.$search.'%');
                });
            });

        return $query->where('device_parent_id','=',null)->orderBy($this->sortedby, $this->sortDirection)->orderBy('device_parent_id','ASC');
    }

    // public function getSelectRaw()
    // {
    //     return '
    //         select device_id,
    //            (select count(da_id) from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 1) as device_active_warnings,
    //            (select da_timestamp from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 1 order by da_timestamp limit 1) as device_lastactive_warning,
    //            (select count(da_id) from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 2) as device_active_errors,
    //            (select da_timestamp from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 2 order by da_timestamp limit 1) as device_lastactive_error,
    //            device_lastreported,
    //            (select count(da_id) from device_alerts join alert_types on da_at_id = at_id where da_device_id = device_id and at_type = "PERIODICAL") as device_overdue,
    //            device_lastset,
    //            IF (lastset.session_warnings = 0 AND lastset.session_errors = 0 AND lastset.session_critical = 0, 1, 0) as device_setok,
    //            device_lastrevival,
    //            IF (lastrevival.session_warnings = 0 AND lastrevival.session_errors = 0 AND lastrevival.session_critical = 0, 1, 0) as device_revivalok
    //            from devices
    //            left join sessions lastreported on device_id = lastreported.session_device_id and device_lastreported = lastreported.session_start
    //            left join sessions lastset on device_id = lastset.session_device_id and device_lastset = lastset.session_start
    //            left join sessions lastrevival on device_id = lastrevival.session_device_id and device_lastrevival = lastrevival.session_start
    //            where device_enabled = 1;
    //     ';
    // }
}