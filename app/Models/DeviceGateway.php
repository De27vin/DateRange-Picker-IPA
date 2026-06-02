<?php
namespace App\Models;

use App\Scopes\GatewaysInAccountScope;
use App\Searchable\Search;
use App\Searchable\Searchable;
use App\Searchable\SearchResult;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsAttributesNotSerialize;
use App\Traits\PasswordPolicyTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DeviceGateway
 *
 * @property int $dg_id
 * @property int $dg_account_id
 * @property int $dg_device_id
 * @property string|null $dg_mac
 *
 * @property Account $account
 * @property DeviceGatewayType $type
 * @property Collection|DeviceSite[] $device_sites
 *
 * @package App\Models
 */
class DeviceGateway extends Model implements Searchable
{
    use EncryptsAttributesNotSerialize;
    use PasswordPolicyTrait;
    use SoftDeletes;

    const CREATED_AT = 'dg_created';
    const UPDATED_AT = 'dg_modified';
    const DELETED_AT = 'dg_deleted';

    protected $appends    = ['is_valid', 'valid_in_hours', 'valid_in_minutes'];

    protected $table      = 'device_gateways';
    protected $primaryKey = 'dg_id';
//    protected $with       = ['device_gateway_type'];
    // public $timestamps    = false;
    public $realm         = 'serv24.com';
    public $host          = 'ucp-fs-dev.serv24.com';
    protected $encrypts   = ['dg_sippwd'];
    protected $casts      = [
		'dg_account_id' => 'int',
		'dg_device_id' => 'int',
//		'dg_dgt_id' => 'int',
		'dg_deleted' => 'string',
	];

	protected $fillable = [
		'dg_account_id',
		'dg_device_id',
//		'dg_dgt_id',
		'dg_mac',
		'dg_imei',
		'dg_sippwd',
		'dg_siphash',
		'dg_created',
		'dg_siphost'   
	];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new GatewaysInAccountScope());
    }

	public function account()
	{
		return $this->belongsTo(Account::class, 'dg_account_id');
	}

    public function device()
    {
        return $this->belongsTo(Device::class, 'dg_device_id');
    }

//    public function device_site()
//    {
//		return $this->hasOne(DeviceSite::class, 'ds_dg_id');
//    }

//    public function device_gateway_type()
//    {
//        return $this->belongsTo(DeviceGatewayType::class, 'dg_dgt_id');
//    }

//	public function type()
//	{
//		return $this->belongsTo(DeviceGatewayType::class, 'dg_dgt_id');
//	}

    public function scopeForAccount($query)
    {
        return $query->where('dg_account_id', '=', app(\App\Services\AccountContext::class)->get());
    }

    public function scopeEnabled($query)
    {
        return $query->whereHas('device', function ($subQuery) {
            $subQuery->where('device_enabled', 1);
        });
    }

    public function scopeDisabled($query)
    {
        return $query->whereHas('device', function ($subQuery) {
            $subQuery->where('device_enabled', '!=', 1);
        });
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('dg_device_id');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('dg_device_id');
    }

    public function getIsValidAttribute()
    {
        return (empty($this->dg_expires) || $this->dg_expires > Carbon::now());
    }

    public function getValidInHoursAttribute()
    {
        try {
            $expires = null;
            if (!empty($this->dg_expires) && is_string($this->dg_expires)) {
                $expires = Carbon::createFromFormat('Y-m-d H:i:s', $this->dg_expires, 'UTC');
                if (!$expires instanceof Carbon) {
                    return null;
                }
                return $expires->diffInHours(now(), false);
            }

            if (!empty($this->dg_expires) && $this->dg_expires instanceof Carbon) {
                return $this->dg_expires?->diffInHours(now(), false);
            }

            return null;
        } catch (\Throwable $e) {
            \Log::error('Error on converting gateway expiration date to carbon instance', ['e' => $e]);
            return null;
        }
    }

    public function getValidInMinutesAttribute()
    {
        try {
            $expires = null;
            if (!empty($this->dg_expires) && is_string($this->dg_expires)) {
                $expires = Carbon::createFromFormat('Y-m-d H:i:s', $this->dg_expires, 'UTC');
                if (!$expires instanceof Carbon) {
                    return null;
                }
                return $expires->diffInMinutes(now(), false);
            }

            if (!empty($this->dg_expires) && $this->dg_expires instanceof Carbon) {
                return $this->dg_expires?->diffInMinutes(now(), false);
            }

            return null;
        } catch (\Throwable $e) {
            \Log::error('Error on converting gateway expiration date to carbon instance', ['e' => $e]);
            return null;
        }
    }

    public function scopeValid($query)
    {
        return $query->where('dg_expires','>',Carbon::now());
    }

    public function setDgSiphashAttribute($realm)
    {
        $hashPart = $this->dg_mac ?? $this->dg_imei ?? '';

        if(!is_null($this->dg_sippwd)){
            $this->attributes['dg_siphash'] = md5($hashPart . ":" . $realm . ":" . $this->dg_sippwd);
        }
    }

    // todo: this function together with validations for gateways should be moved to GatewayService
	public function addData($data)
	{
        $accountId = array_key_exists('account_id', $data) ? $data['account_id'] : session('account.id');

        if (!empty($data['mac']) && !empty($data['imei'])) {
            $deviceGateway = DeviceGateway::withTrashed()->where(['dg_mac' => $data['mac'], 'dg_imei' => $data['imei']])->first();
        } elseif (!empty($data['mac'])) {
            $deviceGateway = DeviceGateway::withTrashed()->where('dg_mac', $data['mac'])->first();
        } elseif (!empty($data['imei'])) {
            $deviceGateway = DeviceGateway::withTrashed()->where('dg_imei', $data['imei'])->first();
        }

        if (empty($deviceGateway)) {
            $this->dg_mac = $data['mac'];
            $this->dg_imei = $data['imei'];
            $this->dg_sippwd = $this->generatePassword($accountId);
            $this->dg_siphash = $this->realm;
            $this->dg_created = Carbon::now();
            $this->dg_account_id = $accountId;
            $this->save();
            return $this;
        }

        // there potentially could be another logic for scenario where only some data matches
        if ($deviceGateway->trashed()) {
            $deviceGateway->restore();
            $deviceGateway->dg_mac = $data['mac'] ?? null;
            $deviceGateway->dg_imei = $data['imei'] ?? null;
            $deviceGateway->dg_modified = Carbon::now();
            $deviceGateway->save();
        }

        return $deviceGateway;
	}

    public function handleEncryptionError($attributeKey, $exception)
    {
        if ($attributeKey === 'dg_sippwd') {
            // regenerate password
            $accountId = $this->dg_account_id ?? session('account.id');
            $newPassword = $this->generatePassword($accountId);

            // directly set encrypted value to avoid infinite loop
            $this->attributes['dg_sippwd'] = encrypt($newPassword, false);

            // update hash if needed
            if (isset($this->realm)) {
                $hashPart = $this->dg_mac ?? $this->dg_imei ?? '';
                $this->attributes['dg_siphash'] = md5($hashPart . ":" . $this->realm . ":" . $newPassword);
            }

            // save changes to database
            $this->save();

            \Log::error("Regenerated password after encryption error", [
                'model' => get_class($this),
                'id' => $this->{$this->getKeyName()}
            ]);
        }
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->dg_mac ?? $this->dg_imei ?? ''
        );
    }
}
