<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enum\ModuleFlags;
use App\Overrides\SoftDelete\UniqueConstraintSoftDeletes;
use App\Traits\TranslationsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Scopes\DevicesByAccountScope;
use App\Searchable\Searchable;
use App\Searchable\SearchResult;

/**
 * Class Device
 * 
 * @property int $device_id
 * @property int $device_ds_id
 * @property int $device_module_id
// * @property int|null $device_address_id
 * @property string|null $device_slug
 * @property string|null $device_identity
 * @property string|null $device_equipment
 * @property string|null $device_setidentity
 * @property int|null $device_module
 * @property string|null $device_pin
 * @property string|null $device_setpin
 * @property Carbon $device_created
 * @property Carbon|null $device_modified
 * @property Carbon|null $device_deleted
 * @property Carbon|null $device_lastset
 * @property Carbon|null $device_lastrevival
 * @property Carbon|null $device_lastreported
 * @property Carbon|null $device_expectedreport
 * @property Carbon|null $device_reservation
 * @property int $device_enabled
 *
 * // computed attributes
 * @property bool $can_assign_gateway
 *
// * @property Address|null $address
 * @property DeviceSite $device_site
 * @property Collection|DeviceAlert[] $device_alerts
 * @property Collection|DeviceComment[] $device_comments
 * @property Collection|DeviceLabelsDevice[] $device_labels_devices
 * @property Collection|Setting[] $settings
 * @property Collection|Session[] $sessions
 *
 * @package App\Models
 */
class Device extends Model implements Searchable
{
    use UniqueConstraintSoftDeletes;
    use TranslationsTrait;

    protected $table      = 'devices';
    protected $primaryKey = 'device_id';
    protected $appends    = ['latest_comment', 'device_alerts_unique', 'expected_periodical_in_hours', 'expected_periodical_in_minutes', 'can_assign_gateway'];
    public $timestamps    = false;

    const CREATED_AT = 'device_created';
    const UPDATED_AT = 'device_modified';
    const DELETED_AT = 'device_deleted';

	protected $casts = [
		'device_ds_id'          => 'int',
//		'device_address_id'     => 'int',
		'device_module'         => 'int',
		'device_created'        => 'datetime',
		'device_modified'       => 'datetime',
		'device_deleted'        => 'string',
		'device_lastset'        => 'datetime',
		'device_lastrevival'    => 'datetime',
		'device_lastreported'   => 'datetime',
		'device_expectedreport' => 'datetime',
		'device_reservation'    => 'datetime',
		'device_enabled'        => 'int'
	];

	protected $fillable = [
		'device_ds_id',
//		'device_address_id',
		'device_account_id',
        'device_module_id',
		'device_equipment',
//		'device_link',
		'device_slug',
		'device_identity',
		'device_setidentity',
		'device_module',
		'device_setmodule',
		'device_pin',
		'device_setpin',
		'device_created',
		'device_modified',
		'device_deleted',
		'device_lastset',
		'device_lastrevival',
		'device_lastreported',
		'device_expectedreport',
		'device_reservation',
		'device_enabled'
	];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DevicesByAccountScope);

        static::deleting(function (Device $device) {
            $device->safeguardForDeleteSystemDevice();
        });

        // device account_id integrity guard
        // todo: consider testing wrong account id and changing method to 'saving'

        static::updating(function (Device $device) {
            if ($device->device_account_id !== $device->device_site->ds_account_id) {
                throw new Exception('Attempt to change account id on device');
            }
        });
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'device_module_id');
    }

    public function custom_fields()
    {
        return $this->hasMany(CustomFieldValue::class, 'cfv_device_id');
    }

//    public function address()
//    {
//        return $this->belongsTo('App\Models\Address', 'device_address_id')->with('location', 'location.country');
//    }
//
//    public function device_address()
//    {
//        return $this->belongsTo('App\Models\Address', 'device_address_id')->with('location', 'location.country');
//    }

	public function device_site()
	{
		return $this->belongsTo(DeviceSite::class, 'device_ds_id');
	}

    public function gateway()
    {
        return $this->hasOne(DeviceGateway::class, 'dg_device_id');
    }

	public function site_account()
	{
		return $this->belongsTo(DeviceSite::class, 'device_ds_id')->forAccount();
	}

	public function device_alerts()
	{
		return $this->hasMany(DeviceAlert::class, 'da_device_id')->with('warnings','errors');
	}

	public function warnings()
	{
		return $this->hasMany(DeviceAlert::class, 'da_device_id')->with('warnings');
	}

	public function errors()
	{
		return $this->hasMany(DeviceAlert::class, 'da_device_id')->with('errors');
	}

    public function getLatestCommentAttribute()
    {
        return $this->device_comments()->first();
    }

    public function latest_comment()
    {
        return $this->hasOne('App\Models\DeviceComment', 'dc_device_id', 'device_id')->orderBy('dc_created','desc')->take(1);
    }

	public function device_comments()
	{
		return $this->hasMany(DeviceComment::class, 'dc_device_id')->orderBy('dc_created','desc');
	}

	// public function device_labels_devices()
	// {
	// 	return $this->hasMany(DeviceLabelsDevice::class, 'dld_device_id');
	// }

//	public function device_labels()
//	{
////        return $this->belongsToMany(DeviceLabelOld::class, 'device_labels_devices', 'dld_device_id', 'dld_dl_id');
//        return $this->belongsToMany(DeviceLabel::class, 'device_labels_devices', 'dld_device_id', 'dld_dl_id');
//	}

	public function settings()
	{
		return $this->belongsToMany(Setting::class, 'device_settings', 'ds_device_id', 'ds_setting_id')
					->withPivot('ds_id', 'ds_value');
	}

    public function device_settings()
    {
        return $this->hasMany(DeviceSetting::class, 'ds_device_id');
    }

	public function sessions()
	{
		return $this->hasMany(Session::class, 'session_device_id');
	}

//    public function getAddressAttribute()
//    {
//        return $this->address()->first();
//    }

    public function getDeviceAlertsUniqueAttribute()
    {
        return $this->device_alerts->unique('da_at_id');
    }

    public function getExpectedPeriodicalInHoursAttribute()
    {
        return $this->device_expectedreport?->diffInHours(now(), false);
    }

    public function getExpectedPeriodicalInMinutesAttribute()
    {
        return $this->device_expectedreport?->diffInMinutes(now(), false);
    }

    public function getCanAssignGatewayAttribute(): bool
    {
        $isModuleTypeGateway = str_contains(strtolower($this->module->module_type->mt_type ?? ''), 'gateway');
        $doesModuleSupportSip = boolval(($this->module->module_flags ?? 0) & ModuleFlags::MODULE_FLAG_SIP_SUPPORT->value);

        return $isModuleTypeGateway && $doesModuleSupportSip;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeAll($query)
    {
        return $query;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeEnabled($query)
    {
        return $query->where('device_enabled', '=', 1);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeDeleted($query)
    {
        return $query->where('device_deleted', '!=', '0000-00-00 00:00:00');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeDisabled($query)
    {
        return $query->where('device_enabled', '=', 0);
    }

    public function scopeMissing($query)
    {
        return $query->whereHas('device_alerts', function($q){
            $q->whereHas('alert_type', function($qu){
                $qu->where('alert_types.at_type','=','PERIODICAL');
            });
        });
    }

    public function scopeWithAlerts($query, $alerts = [])
    {
    	if(count($alerts) > 0){
	        return $query->whereHas('device_alerts', function($q) use($alerts){
	            $q->whereHas('alert_type', function($qu) use($alerts){
	                $qu->whereIn('alert_types.at_type',$alerts);
	            });
	        });
	    } else {
	        return $query;
	    }
    }


    public function scopeAlarm($query)
    {
        return $query->whereHas('device_alerts', function($q){
            $q->whereHas('alert_type', function($qu){
                $qu->where('alert_types.at_type','=','VOICE');
            });
        });
    }

    public function scopeWithAnyLabels($query, array $labelsId)
    {
        return $query->whereHas('device_labels', function ($query) use ($labelsId) {
            $query->whereIn('device_labels.dl_id', $labelsId);
        });
    }

    public function scopeWithAllLabels($query, array $labelsId)
    {
        foreach ($labelsId as $labelId) {
            $query->whereHas('device_labels', function ($query) use ($labelId) {
                $query->where('device_labels.dl_id', $labelId);
            });
        }
    }

    public function addData(Array $data, Array $deviceFieldsRequired, bool $save = true)
    {
        try{
            $queryRequiredFields = '';

            foreach ($deviceFieldsRequired as $key => $field) {
                $fieldValue = (is_array($data[$field]) ? $data[$field]['default'] : $data[$field]);
                if($key == 0){
                    $queryRequiredFields .= " where 'device_".$field."' = '".$fieldValue."' ";
                } else {
                    $queryRequiredFields .= " and 'device_".$field."' = '".$fieldValue."' ";
                }
            }
            if(is_null($data['id'])){
                $result = DB::select('select * from devices ' . $queryRequiredFields);
            } else {
                $result = Device::find($data['id']);
            }
            if($result == null || empty($result)){
                // insert new device
                $this->device_ds_id = $data['site_id'];
                $this->device_account_id = $data['account_id'];
                $this->device_equipment = ($data['equipment'] ?? null);
//                $this->device_address_id = ($data['address_id'] ?? null);
                $this->device_identity = ($data['identity']['default'] ?? null);
                $this->device_module = ($data['module']['default'] ?? null);
                $this->device_pin = ($data['pin']['default'] ?? null);
//                $this->device_link = ($data['link'] ?? null);
                !$save || $this->save();
                return $this;
            } else {
                // update existing device
                $result->device_equipment = ($data['equipment'] ?? null);
//                $result->device_address_id = ($data['address_id'] ?? null);
                $result->device_setidentity = ($result->device_identity != $data['identity']['default'] ? $data['identity']['default'] : $result->device_setidentity);
                $result->device_setpin = ($result->device_pin != $data['pin']['default'] ? $data['pin']['default'] : $result->device_setpin);
                $result->device_setmodule = ($result->device_module != $data['module']['default'] ? $data['module']['default'] : $result->device_setmodule);
//                $result->device_link = ($data['link'] ?? null);
                !$save || $this->save();
                return $result;
            }
        } catch(\Throwable $e){
            session()->flash('error','storing data for device failed');
        }
    }


    // ------------------------------------
    // PORTING TO TEST BELOW:
    // ------------------------------------

    public function scopeUp($query)
    {
        return $query->where('device_enabled', '=', 1);
    }

    public function scopeDown($query)
    {
        return $query->where('device_enabled', '=', 0);
    }

    public function getStatesAttribute()
    {
        if($this->device_id == null){
            return [];
        }
        try {
            $voiceAlertTypeId = AlertType::query()->where('at_type','=','VOICE')->first()->at_id;
            return Arr::first( DB::select('
                select device_id,
                -- this below  is not used after all (it used in element that is not used)
                (select count(da_id) from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 1 and da_at_id != ' . $voiceAlertTypeId . ' ) as device_active_warnings,
                -- this below  is not used after all
                (select da_timestamp from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 1 and da_at_id != ' . $voiceAlertTypeId . ' order by da_timestamp limit 1) as device_lastactive_warning,
                -- this below  is not used after all (it used in element that is not used)
                (select count(da_id) from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 2) as device_active_errors,
                -- this below  is not used after all
                (select da_timestamp from device_alerts join alert_types on da_at_id = at_id join alert_severities on at_as_id = as_id where da_device_id = device_id and as_severity = 2 order by da_timestamp limit 1) as device_lastactive_error,
                -- below are USED ONES
                device_lastreported,
                device_expectedreport,
                (select count(da_id) from device_alerts join alert_types on da_at_id = at_id where da_device_id = device_id and at_type = "PERIODICAL") as device_overdue,
                device_lastset,
                IF (lastset.session_warnings = 0 AND lastset.session_errors = 0 AND lastset.session_critical = 0, 1, 0) as device_setok,
                device_lastrevival,
                IF (lastrevival.session_warnings = 0 AND lastrevival.session_errors = 0 AND lastrevival.session_critical = 0, 1, 0) as device_revivalok
                from devices
                left join sessions lastreported on device_id = lastreported.session_device_id and device_lastreported = lastreported.session_start
                left join sessions lastset on device_id = lastset.session_device_id and device_lastset = lastset.session_start
                left join sessions lastrevival on device_id = lastrevival.session_device_id and device_lastrevival = lastrevival.session_start
                WHERE  `device_id` = ' . $this->device_id .'
            '));
        } catch(\Throwable $e){
            return [];
        }
    }

    public function getLastActiveAlarm()
    {
        $alarmality = $this->getAlertAlarmalityStates();
        $alarmality = array_keys(array_filter($alarmality));

        $alerts = $this->sessions->load(['alerts', 'alerts.alert_type'])->pluck('alerts')->flatten()->sortByDesc('alert_timestamp');

        foreach ($alerts as $alert) {
            if (in_array($alert['alert_type']['at_type'], $alarmality)) {
                return $alert['alert_timestamp'];
            }
        }

        return null;
    }

    public function getSearchResult(): SearchResult
    {

        $equipment = ($this->device_equipment == null ? '' : $this->device_equipment);

        return new \App\Searchable\SearchResult(
            $this,
            $equipment,
        );
    }

    private function safeguardForDeleteSystemDevice()
    {
        $siteName = strtolower($this->device_site->ds_name);
        $moduleName = strtolower($this->device_site->module->module_name);
        $moduleType = strtolower($this->device_site->module->module_type->mt_type);

        if (in_array('system', [$siteName, $moduleName, $moduleType])) {
            throw new \Exception('Attempt to delete SYSTEM device');
        }
    }

    public function scopeSystem($query)
    {
        return $query->where(function ($query) {
            $query->whereHas('module', function ($query) {
                $query->whereHas('module_type', function ($query) {
                    $query->whereRaw('LOWER(mt_type) = ?', ['system'])
                          ->orWhereRaw('LOWER(mt_desc) LIKE ?', ['%system%']);
                })->orWhereRaw('LOWER(module_name) = ?', ['system'])
                  ->orWhereRaw('LOWER(module_name) = ?', ['watchdog'])
                  ->orWhereRaw('LOWER(module_desc) LIKE ?', ['%system%'])
                  ->orWhereRaw('LOWER(module_desc) LIKE ?', ['%watchdog%']);
            })->orWhereHas('device_site.module', function ($query) {
                $query->whereHas('module_type', function ($query) {
                    $query->whereRaw('LOWER(mt_type) = ?', ['system'])
                          ->orWhereRaw('LOWER(mt_desc) LIKE ?', ['%system%']);
                })->orWhereRaw('LOWER(module_name) = ?', ['system'])
                  ->orWhereRaw('LOWER(module_name) = ?', ['watchdog'])
                  ->orWhereRaw('LOWER(module_desc) LIKE ?', ['%system%'])
                  ->orWhereRaw('LOWER(module_desc) LIKE ?', ['%watchdog%']);
            });
        });
    }
}
