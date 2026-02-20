<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Scopes\DeviceSitesByAccountScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Searchable\Searchable;
use App\Searchable\SearchResult;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DeviceSite
 * 
 * @property int $ds_id
 * @property int $ds_account_id
 * @property int $ds_protocol_id
 * @property int|null $ds_address_id
 * @property int|null $ds_dg_id
 * @property string|null $ds_name
 * @property string|null $ds_link
 * 
 * @property Account $account
 * @property Address|null $address
 * @property Module $module
 * @property Collection|Device[] $devices
 * @property Collection|Number[] $numbers
 *
 * @package App\Models
 */
class DeviceSite extends Model implements Searchable
{
    use SoftDeletes;

	protected $table      = 'device_sites';
	protected $primaryKey = 'ds_id';
	// public $timestamps    = false;
	protected $appends    = ['latest_comment', 'gateway', 'gateway_device', 'gateway_type_device', 'sip', 'sim', 'pbx', 'pstn', 'single_number'];
	protected $casts      = [
		'ds_account_id' => 'int',
		'ds_address_id' => 'int',
		'ds_protocol_id' => 'int',
		'ds_dg_id' => 'int'
	];

//    protected $with = ['comments', 'numbers', 'pstn', 'sim', 'sip', 'pbx', 'module'];

    const CREATED_AT = 'ds_created';
    const UPDATED_AT = 'ds_modified';
    const DELETED_AT = 'ds_deleted';

    protected $fillable = [
        'ds_name',
        'ds_account_id',
        'ds_address_id',
        'ds_protocol_id',
        'ds_link',
    ];

    const DEVICE_TYPE_ORDER = ['GATEWAY', 'TELEALARM', 'INTERCOM'];

    private array $cachedAttributes = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DeviceSitesByAccountScope());

        static::deleting(function (DeviceSite $deviceSite) {
            $deviceSite->safeguardForDeleteSystemDeviceSite();
        });

        static::updated(function (DeviceSite $deviceSite) {
            $deviceSite->devicesAccountIntegrityGuard();
        });
    }

   public function scopeForAccount($query)
   {
       return $query->where('ds_account_id', '=', session('account.id'));
   }

    public function scopeWithAnyLabels($query, array $labelsId)
    {
        return $query->whereHas('devices.device_labels', function ($query) use ($labelsId) {
            $query->whereIn('device_labels.dl_id', $labelsId);
        });
    }

    public function scopeWithAllLabels($query, array $labelsId)
    {
        foreach ($labelsId as $labelId) {
            $query->whereHas('devices.device_labels', function ($query) use ($labelId) {
                $query->where('device_labels.dl_id', $labelId);
            });
        }
    }

    public function scopeWithAlerts($query, $alerts = [])
    {
        if(count($alerts) > 0){
            $query->whereHas('devices', function ($q1) use ($alerts) {
                $q1->whereHas('device_alerts', function($q2) use ($alerts) {
                    $q2->whereHas('alert_type', function ($q3) use ($alerts) {
                        $q3->whereIn('alert_types.at_type', $alerts);
                    });
                });
            });
        } else {
            return $query;
        }
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'ds_account_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'ds_address_id')->with('location', 'location.country');
    }

    public function labels()
	{
        return $this->belongsToMany(DeviceLabel::class, 'device_labels_sites', 'dld_ds_id', 'dld_dl_id');
	}

    public function getLatestCommentAttribute()
    {
        return $this->comments->first();
    }

    public function comments()
    {
        return $this->hasManyThrough(
            DeviceComment::class,
            Device::class,
            'device_ds_id',
            'dc_device_id'
        )->withTrashedParents()->where('device_deleted', '0000-00-00 00:00:00')
        ->orderBy('dc_created', 'desc');
    }

    // gateway device through type gateway
    public function getGatewayTypeDeviceAttribute()
    {
        if (array_key_exists('gateway_type_device', $this->cachedAttributes)) {
            return $this->cachedAttributes['gateway_type_device'];
        }
        $gateway = $this->devices()
                    ->whereHas('module', function($query) {
                        $query->whereHas('module_type', function($query) {
                            $query->where('mt_type', 'GATEWAY');
                        });
                    })
                    ->first();
        $this->cachedAttributes['gateway_type_device'] = $gateway;
        return $this->cachedAttributes['gateway_type_device'];
    }

    // gateway device through actual gateway @todo: rename to getConnectedGatewayDevice
    public function getGatewayDeviceAttribute()
    {
        if (array_key_exists('gateway_device', $this->cachedAttributes)) {
            return $this->cachedAttributes['gateway_device'];
        }
        $gateway = $this->devices()->whereHas('gateway')?->first();
        $this->cachedAttributes['gateway_device'] = $gateway;
        return $this->cachedAttributes['gateway_device'];
    }

    // actual gateway
    public function getGatewayAttribute()
    {
        if (array_key_exists('gateway', $this->cachedAttributes)) {
            return $this->cachedAttributes['gateway'];
        }
        $gateway = $this->devices()->whereHas('gateway')?->first()?->gateway;
        $this->cachedAttributes['gateway'] = $gateway;
        return $this->cachedAttributes['gateway'];
    }

    // actual gateway
    public function getDeviceGatewayAttribute()
    {
        if (array_key_exists('gateway', $this->cachedAttributes)) {
            return $this->cachedAttributes['gateway'];
        }
        $gateway = $this->devices()->whereHas('gateway')?->first()?->gateway;
        $this->cachedAttributes['gateway'] = $gateway;
        return $this->cachedAttributes['gateway'];
    }


	public function module()
	{
		return $this->belongsTo(Module::class, 'ds_protocol_id');
	}

    public function device_site_settings()
    {
        return $this->hasMany(DeviceSiteSetting::class, 'dss_ds_id');
    }

//	public function getModuleAttribute()
//	{
//        if (array_key_exists('module', $this->cachedAttributes)) {
//            return $this->cachedAttributes['module'];
//        }
//        $module = $this->module()->first();
//        $this->cachedAttributes['module'] = $module;
//        return $this->cachedAttributes['module'];
//	}


	public function devices()
	{
		return $this->hasMany(Device::class, 'device_ds_id');
	}

	public function all_devices()
	{
		return $this->hasMany(Device::class, 'device_ds_id')->withTrashed();
	}

    public function custom_fields()
    {
        return $this->hasMany(CustomFieldValue::class, 'cfv_ds_id');
    }

	public function numbers()
	{
		return $this->hasMany(Number::class, 'number_ds_id');
	}

    public function getSipAttribute()
    {
        return $this->numbers->first(fn($number) => $number->number_type->nt_type === 'SIP');
    }

    public function getSimAttribute()
    {
        return $this->numbers->first(fn($number) => $number->number_type->nt_type === 'SIM');
    }

    public function getPbxAttribute()
    {
        return $this->numbers->first(fn($number) => $number->number_type->nt_type === 'PBX');
    }

    public function getPstnAttribute()
    {
        return $this->numbers->first(fn($number) => $number->number_type->nt_type === 'PSTN');
    }

    // todo: check if numbers loading can be generally optimized
    public function getSingleNumberAttribute()
    {
        return $this->sip ? ['type' => 'sip', 'value' => $this->sip->number_value] :
               ($this->sim ? ['type' => 'sim', 'value' => $this->sim->number_value] :
               ($this->pbx ? ['type' => 'pbx', 'value' => $this->pbx->number_value] :
               ($this->pstn ? ['type' => 'pstn', 'value' => $this->pstn->number_value] : null)));
    }

//    public function getIsSystemAttribute(): bool
//    {
//        return $this->devices->contains(fn(Device $device) => $device->device_type->dt_type === 'SYSTEM');
//    }

    public function sortDevicesByType(): self
    {
        $this->devices = $this->devices->sortBy(function ($device) {
            $index = array_search($device->module?->module_type?->mt_type, self::DEVICE_TYPE_ORDER);
            return $index !== false ? $index : count(self::DEVICE_TYPE_ORDER);
        });

        return $this;
    }


    public function getSearchResult(): SearchResult
    {
        return new \App\Searchable\SearchResult(
            $this,
            $this->ds_id,
        );
    }

    private function devicesAccountIntegrityGuard()
    {
        $devices = $this->devices()->withoutGlobalScopes()->get();
        foreach($devices as $device) {
            if ($device->device_account_id !== $this->ds_account_id) {
                $device->device_account_id = $this->ds_account_id;
                $device->save();
            }
        }
    }

    private function safeguardForDeleteSystemDeviceSite()
    {
        $siteName = strtolower($this->ds_name);
        $moduleName = strtolower($this->module->module_name);
        $moduleType = strtolower($this->module->module_type->mt_type);

        if (in_array('system', [$siteName, $moduleName, $moduleType])) {
            throw new \Exception('Attempt to delete SYSTEM device site');
        }
    }

    public function scopeSystem($query)
    {
        return $query->where(function ($query) {
            $query->where(function ($query) {
                $query->whereHas('module', function ($query) {
                    $query->whereHas('module_type', function ($query) {
                        $query->whereRaw('LOWER(mt_type) = ?', ['system'])
                              ->orWhereRaw('LOWER(mt_desc) LIKE ?', ['%system%']);
                    })->orWhereRaw('LOWER(module_name) = ?', ['system'])
                      ->orWhereRaw('LOWER(module_name) = ?', ['watchdog'])
                      ->orWhereRaw('LOWER(module_desc) LIKE ?', ['%system%'])
                      ->orWhereRaw('LOWER(module_desc) LIKE ?', ['%watchdog%']);
                });
            })->orWhereHas('devices.module', function ($query) {
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
