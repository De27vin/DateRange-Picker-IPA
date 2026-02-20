<?php

namespace App\Traits;

use App\Models\AlertSeverity;
use App\Models\AlertType;
use App\Models\Session;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

trait AlertsTrait
{
    public $severities;
    public $alertTypes;
    public $alerts;
    public $alertSeverityTotals = [];
    public $accountDeviceTypeIds;

    public function getDeviceIdFromUrl()
    {
        $device_id = last(explode('/',url()->current()));
        if($device = Device::where('device_id','=',$device_id)->first()){
            return $device->device_id;
        } else {
            return null;
        }
    }

    public function getAlertData($deviceId = null)
    {
        $this->severities = AlertSeverity::all();
        $this->alertTypes = [
            'warnings' => AlertType::warnings()->get(),
            'errors' => AlertType::errors()->get()
        ];

        $alerts = $this->getAlertsByType($deviceId);
// dd($this->alertTypes);

        foreach ($alerts as $severity => $alertTypes) {
            foreach ($alertTypes as $alertType => $data) {
                $this->alerts[$severity][$alertType] = $alerts[$severity][$alertType]
                    ->unique('session_device_id')
                    ->mapToGroups(function ($item, $key) {
                    return [$item->session_device_id => $item];
                });
            }
        }

    }

    public function getAlertsByType($deviceId)
    {
        foreach($this->alertTypes['warnings'] as $typeItem) {
            $alerts['warnings'][$typeItem->at_type] = (new Session)->getAlertWarningsByType($typeItem->at_id, $deviceId);
        }
        foreach($this->alertTypes['errors'] as $typeItem) {
            $alerts['errors'][$typeItem->at_type] = (new Session)->getAlertErrorsByType($typeItem->at_id, $deviceId);
        }
        return $alerts;
    }

    public function getActiveErrors()
    {
        return AlertType::with(['account' => function($q){
            $q->wherePivot('is_visible','=',true);
        }])->errors()->get()->filter(function($item, $key){
            if($item->account->count()>0){
                return $item;
            }
        });
        // return ['warnings' => $warnings, 'errors' => $errors];
    }

    public function getActiveWarnings()
    {
        return AlertType::with(['account' => function($q){
            $q->wherePivot('is_visible','=',true);
        }])->warnings()->get()->filter(function($item, $key){
            if($item->account->count()>0){
                return $item;
            }
        });
    }

    public function getRawDeviceAlertsByAlertTypes($filter, $sortedBy='da_timestamp', $sortDirection='DESC')
    {
        $filterAsString = implode("','",$filter);
        return collect(DB::select("
            SELECT 
                device_id, 
                device_parent_id, 
                device_identity,
                device_lastreported, 
                da_timestamp, 
                device_pin, 
                device_setpin,
                device_dt_id, 
                as_type, 
                dt_name, 
                at_type, 
                device_numbers.dn_value,
                device_parent_numbers.dn_value as dn_parent_value
            FROM device_alerts
            JOIN alert_types on device_alerts.da_at_id = alert_types.at_id
            JOIN devices on device_alerts.da_device_id = devices.device_id
            inner join device_types on devices.device_dt_id = device_types.dt_id AND device_types.dt_account_id = " . session('account.id') . "
            JOIN alert_severities ON alert_types.at_as_id = alert_severities.as_id
            LEFT OUTER JOIN device_numbers ON device_numbers.dn_device_id = devices.device_id 
            LEFT OUTER JOIN device_numbers as device_parent_numbers ON device_parent_numbers.dn_device_id = devices.device_parent_id 
            JOIN accounts ON device_types.dt_account_id = accounts.account_id
            WHERE account_id = " . session('account.id') . "
            AND devices.device_enabled = true
            AND devices.device_deleted IS NULL
            AND alert_types.at_type IN ('" . $filterAsString . "')
            ORDER BY " . $sortedBy . " " . strtoupper($sortDirection) . "
        ") )->mapToGroups(function($item,$key){
            $test = [$item->at_type => $item];
            return [$item->device_id =>  $test] ;
        })->map(function($item, $key){
            return $item->collapse();
        });
    }

    public function getRawAlertCountsByAlertTypes()
    {
        $activeAlertTypes = implode(',',AlertType::get()->pluck('at_id')->all());
        return collect(DB::select("
            select 
                COUNT(device_id) as total,
                `as_type`,
                `at_type`    
            from alert_types
                right join `alert_severities` on `at_as_id` = `as_id`
                right join `device_alerts` on `da_at_id` = `at_id`
                right join `devices` on `da_device_id` = `device_id` AND `device_enabled` = '1' and `device_deleted` IS NULL
                inner join `device_types` on `device_dt_id` = `dt_id` AND `dt_account_id` = " . session('account.id') . "
               where at_id in (" . $activeAlertTypes . ")
            group by at_type
            order by as_type, at_type
        "))->mapWithKeys(function($item, $key){
            return [$item->at_type => $item->total];
        })->toArray();
    }


}
