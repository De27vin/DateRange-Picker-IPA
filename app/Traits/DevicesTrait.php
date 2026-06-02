<?php

namespace App\Traits;

use App\Models\AlertSeverity;
use App\Models\Device;
use App\Models\DeviceSite;
use App\Models\DeviceAlert;
use App\Models\AlertType;
use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/** @deprecated  */
trait DevicesTrait
{

    /**
     * @param $state String ( enabled | disabled | deleted )
     * @param $alerts Array ( array of alert_types.at_type )
     * @return collection
     */
    public function searchDevices()
    {
        $this->filters = session('deviceSearchFilter', null);

        return DeviceSite::query()
        ->with('devices', 'address', 'numbers', 'devices.device_alerts', 'devices.device_labels', 'devices.latest_comment', 'module')
        ->where('device_sites.ds_account_id','=',$this->account->account_id)
        ->when($this->filters['search'] != '', function($query){
            $search = $this->filters['search'];
            $this->hasFilters = true;
            $query->where('device_sites.ds_name', 'like', '%'.$search.'%');
        })
        ->when($this->filters['device_number'], function($query, $device_number){
            $query->whereHas('numbers', function($q) use($device_number){
                // $q->where('numbers.number_value','=', $device_number);
                $q->where('numbers.number_value','like','%'.$device_number.'%');
            });
        })

        ->when(intval($this->filters['module_id']) > 0, function($query){
            $query->where('device_sites.ds_protocol_id','=',$this->filters['module_id']);
        })
        ->whereHas('devices', function($q){
            $q
            ->when($this->filters['device_pin'], function($query, $device_pin){
                $this->hasFilters = true;
                return $query->where('device_pin', '=', $device_pin);
            })
            ->when($this->filters['device_module'], function($query, $device_module){
                $this->hasFilters = true;
                return $query->where('device_module', '=', $device_module);
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
            ->when($this->filters['periodical'], function($query){
                $this->hasFilters = true;
                return $query->whereHas('device_alerts', function($q) {
                    $q->whereHas('alert_type', function($qq){
                        $qq->where('alert_types.at_type','=','PERIODICAL');
                    });
                });
            })
            ->when($this->filters['device_has_warning'], function($query){
                $this->hasFilters = true;
                return $query->whereHas('device_alerts', function($q) {
                    $warnings = implode(',',  $this->getActiveAlertIds('warning'));
                    $q->whereIntegerInRaw('device_alerts.da_at_id', [$warnings]);
                });
            })
            ->when($this->filters['device_has_error'], function($query){
                $this->hasFilters = true;
                return $query->whereHas('device_alerts', function($q) {
                    $errors = implode(',',  $this->getActiveAlertIds('error'));
                    $q->whereIntegerInRaw('device_alerts.da_at_id', [$errors]);
                });
            })
            ->when($this->filters['device_enabled'], function($query){
                return $query->where('device_enabled', '=', 1);
            })
            ->when($this->filters['device_disabled'], function($query){
                return $query->where('device_enabled', '=', 0)->where('device_deleted','=',null);
            })
            ->when($this->filters['device_deleted'], function($query){
                return $query->where('device_deleted','!=',null);
            })
            ->when($this->filters['search'] != '', function($query){
                $search = $this->filters['search'];
                $this->hasFilters = true;
                return $query->whereNested(function($query) use ($search) {
                    return $query->where('device_identity', 'like', '%'.$search.'%');
                     });
            });
        })->get();
    }


    /**
     * @param $state String ( enabled | disabled | deleted )
     * @param $alerts Array ( array of alert_types.at_type )
     * @return collection
     */
    public function getAlertDevices($state = 'all', $alerts = [])
    {
        if(count($alerts) == 0){
            return (new Collection);
        }

        $this->account = app(\App\Services\UserContextService::class)->getCurrentAccount();
        if($this->account == null){
            abort(500, 'Unauthenticated.');
        }

        $return = Device::query()
//            ->with('device_labels', 'device_alerts', 'device_site.module', 'device_site.address')
            ->with('device_alerts', 'device_site.module', 'device_site.address')
            ->where('device_account_id','=',$this->account->account_id)
            ->withAlerts($alerts)
            ->get();

        return $return;
    }

    /**
     * @param $state String ( enabled | disabled | deleted )
     * @param $alerts Array ( array of alert_types.at_type )
     * @return collection
     */
    public function getDevicesByPhone($phone = null)
    {
        return DeviceSite::query()
        ->with('devices', 'address', 'devices.device_alerts', 'devices.device_labels', 'devices.latest_comment', 'module', 'numbers')
            ->where('device_sites.ds_account_id','=',$this->account->account_id)
            ->whereHas('numbers', function($q) use($phone){
                $q->where('numbers.number_value','like','%'.$search.'%');
            })
            ->get();
    }

    /**
     * @param $state String ( enabled | disabled | deleted )
     * @param $alerts Array ( array of alert_types.at_type )
     * @return collection
     */
    public function getDevicesById($deviceIds = [])
    {
        $deviceIds = (is_array($deviceIds) ? $deviceIds : [$deviceIds]);

        return DeviceSite::query()
        ->with('devices', 'module')
            ->where('device_sites.ds_account_id','=',$this->account->account_id)
            ->whereHas('devices', function($q) use($deviceIds){
                $q->whereIn('devices.device_id',$deviceIds)->withAlerts();
            })
            ->get();
    }

    /**
     * @param $state String ( enabled | disabled | deleted )
     * @param $alerts Array ( array of alert_types.at_type )
     * @return collection
     */
    public function getDeviceDetails($deviceId = null)
    {
        if($deviceId == null){
            return null;
        }

        return DeviceSite::query()
        ->with('devices', 'address', 'devices.device_alerts', 'devices.latest_comment', 'module')
            ->where('device_sites.ds_account_id','=',$this->account->account_id)
            ->whereHas('devices', function($q) use($deviceId){
                $q->where('devices.device_id','=',$deviceId)->withAlerts();
            })
            ->get();
    }

    public function getDevicesBySeverity($severity = 'warning')
    {
        $severity = strtoupper($severity);
        return AlertSeverity::query()
            ->with('device_alerts','device_alerts.alert_type','device_alerts.device.site_account')
            ->where('as_type','=','WARNING')
            ->first()
            ->device_alerts->filter(function ($warning, $key) {
                return $warning->device != null;
            })->mapToGroups(function($item, $key){
                return [$item->da_device_id => $item->alert_type->at_type];
            })->toArray();
    }

    /**
     * @deprecated
     * use AlertsService
     */
    public function getAlertCounts($typeList = [])
    {
        return DeviceAlert::query()
            ->select('da_id', 'da_device_id', 'da_at_id')
            ->withCount('device')
            ->whereHas('device', function($q){
                $q->enabled()->whereHas('device_site', function($qq){
                    $qq->where('device_sites.ds_account_id','=',session('account.id'));
                });
            })
            ->whereHas('alert_type', function($q) use($typeList){
                $q->whereIn('alert_types.at_type', $typeList);
            })
            ->get()
            ->mapToGroups(function($item, $key){
                return [$item->alert_type->at_type => $item->device->device_id];
            })
            ->map(function($item,$key){
                return $item->count();
            })
            ->toArray();
    }

    public function getAlertCountsBySeverity($severity = 'error')
    {
        return DeviceAlert::query()
            ->select('da_id', 'da_device_id', 'da_at_id')
            ->withCount('device')
            ->whereHas('device', function($q){
                $q->enabled()->whereHas('device_site', function($qq){
                    $qq->where('device_sites.ds_account_id','=',session('account.id'));
                });
            })
            ->whereHas('alert_type', function($q) use($severity){
                $q->{strtolower($severity)}();
            })
            ->get()
            ->mapToGroups(function($item, $key){
                return [$item->alert_type->at_type => $item->device->device_id];
            })
            ->map(function($item,$key){
                return $item->count();
            })
            ->toArray();
    }

    public function getActiveAlertTypes($severity = 'warning')
    {
        return DeviceAlert::query()
            ->whereHas('alert_type', function($q) use($severity){
                $q->whereHas('alert_severity', function($qq) use($severity){
                    $qq->where('alert_severities.as_type','=',$severity);
                });
            })
            ->get()
            ->map(function($item, $key){
                return $item->alert_type->at_type;
            })
            ->unique()
            ->all();
    }


    public function getTest($severity = 'warning')
    {
        $test = DeviceAlert::query()
            ->with('alert_type','alert_type.alert_severity')
            ->whereHas('alert_type', function($q) use($severity){
                $q->whereHas('alert_severity', function($qq) use($severity){
                    $qq->where('alert_severities.as_type','=',strtoupper($severity));
                });
            })
            ->distinct()
            ->get(['da_at_id'])
            ->pluck('da_at_id')
            ->all();
            dd($test);
    }



    public function getActiveAlertIds($severity = 'warning')
    {
        return DeviceAlert::query()
            ->with('alert_type','alert_type.alert_severity')
            ->whereHas('alert_type', function($q) use($severity){
                $q->whereHas('alert_severity', function($qq) use($severity){
                    $qq->where('alert_severities.as_type','=',strtoupper($severity));
                });
            })
            ->distinct()
            ->get(['da_at_id'])
            ->pluck('da_at_id')
            ->all();
    }

    /**
     * @param $severity ( all | warning | error )
     * @return collection
     */
    public function getActiveAlerts($severity = 'all')
    {
        if(session('account.id') == null){
            abort(500, 'Unauthenticated.');
        }


        if($severity == 'all'){
            $deviceAlerts = DeviceAlert::query()
                ->select('da_id', 'da_device_id', 'da_at_id')
                ->withCount('device')
                ->with('device:device_id,device_ds_id','alert_type:at_id,at_type,at_as_id','alert_type.alert_severity:as_id,as_type')
                ->whereHas('device', function($q){
                    $q->whereHas('device_site', function($qq){
                        $qq->where('device_sites.ds_account_id','=',session('account.id'));
                    });
                })
                ->whereHas('alert_type', function($q) use($severity){
                    $q->{strtolower($severity)}();
                })
                ->get()
                ->mapToGroups(function($item, $key){
                    return [
                        $item->alert_type->alert_severity->as_type => $item];
                });
                foreach ($deviceAlerts as $key => $item) {
                    $deviceAlerts[$key] = $item->mapToGroups(function($innerItem,$innerKey){
                        return [$innerItem->alert_type->at_type => $innerItem->device->device_id];
                    });
                }
            return $deviceAlerts;
        } else {
            $deviceAlerts = DeviceAlert::query()
                ->select('da_id', 'da_device_id', 'da_at_id')
                ->withCount('device')
                ->with('device:device_id,device_ds_id','alert_type:at_id,at_type')
                ->whereHas('device', function($q){
                    $q->whereHas('device_site', function($qq){
                        $qq->where('device_sites.ds_account_id','=',session('account.id'));
                    });
                })
                ->whereHas('alert_type', function($q) use($severity){
                    $q->{strtolower($severity)}();
                })
                ->get()
                ->mapToGroups(function($item, $key){
                    return [$item->alert_type->at_type => $item];
                });
            return collect([$severity => $deviceAlerts]);
        }
    }

    public function getDeviceStates()
    {
        $voiceAlertTypeId = AlertType::query()->where('at_type', '=', 'VOICE')->first()->at_id;
        $result = DB::select('
            select device_id,
                   (select count(da_id) from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 1 and da_at_id != ' . $voiceAlertTypeId . ' ) as device_active_warnings,       
                   (select da_timestamp from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 1 and da_at_id != ' . $voiceAlertTypeId . '  order by da_timestamp limit 1) as device_lastactive_warning,
                   (select count(da_id) from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 2) as device_active_errors,
                   (select da_timestamp from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 2 order by da_timestamp limit 1) as device_lastactive_error,
                   device_lastreported,
                   (select count(da_id) from device_alerts join alert_types on da_at_id = at_id where da_device_id = device_id and at_type = "PERIODICAL") as device_overdue,
                   device_lastset,
                   IF (lastset.session_warnings = 0 AND lastset.session_errors = 0 AND lastset.session_critical = 0, 1, 0) as device_setok,
                   device_lastrevival,
                   IF (lastrevival.session_warnings = 0 AND lastrevival.session_errors = 0 AND lastrevival.session_critical = 0, 1, 0) as device_revivalok
                   from devices
                   left join sessions lastreported on device_id = lastreported.session_device_id and device_lastreported = lastreported.session_start
                   left join sessions lastset on device_id = lastset.session_device_id and device_lastset = lastset.session_start
                   left join sessions lastrevival on device_id = lastrevival.session_device_id and device_lastrevival = lastrevival.session_start
                   where device_enabled = 1 and device_account_id = '.session('account.id').'
        ');

        return collect($result)->mapToGroups(function ($item, $key) {
            return [collect($item)['device_id'] => $item];
        });

    }
    

    public function loadDeviceStates($deviceId)
    {
        return json_decode(json_encode($this->getDeviceStates($deviceId)),true);
    }

    public function loadAlertDevices($requestedPage, $activeAlerts)
    {
        $alertDevices = $this->getAlertDevices('enabled',array_keys($activeAlerts));
        // notify()->warning($alertDevices->count() . ' devices with alerts with alerts «' . implode(',',$this->activeFilters) . '» found');
        if($alertDevices == null){
            $this->alertDevices = null;
        } else {
            $this->alertDevices = $alertDevices;
        }
    }

    private function buildNestedArray($structure, $data) {
        $result = [];

        foreach ($data as $row) {
            $temp = &$result;

            foreach ($structure as $key => $value) {
                if (!isset($row[$key])) {
                    continue;
                }

                if (is_array($value)) {
                    $temp[$key] = $this->buildNestedArray($value, [$row]);
                } else {
                    $temp[$key] = $row[$key];
                }
            }
        }

        return $result;
    }


}