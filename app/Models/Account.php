<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Account
 * 
 * @property int $account_id
 * @property int|null $account_address_id
 * @property string $account_name
 * @property string $account_slug
 * @property string|null $account_translation
 * @property string|null $account_apikey
 * @property Carbon $account_created
 * @property Carbon|null $account_modified
 * @property int $account_enabled
 * 
 * @property Address|null $address
 * @property Collection|Module[] $modules
 * @property Collection|Setting[] $settings
 * @property Collection|Command[] $commands
 * @property Collection|AccountsCountry[] $accounts_countries
 * @property Collection|DeviceGateway[] $device_gateways
 * @property Collection|DeviceLabelOld[] $device_labels
 * @property Collection|DeviceSite[] $device_sites
 * @property Collection|Route[] $routes
 * @property Collection|Session[] $sessions
 * @property Collection|SipUser[] $sip_users
 * @property Collection|UsersRole[] $users_roles
 *
 * @package App\Models
 */
class Account extends Model
{
	protected $table = 'accounts';
	protected $primaryKey = 'account_id';
	public $timestamps = false;

	protected $casts = [
		'account_id' => 'int',
		'account_address_id' => 'int',
		'account_created' => 'datetime',
		'account_modified' => 'datetime',
		'account_enabled' => 'int',
		'account_translation' => 'json'
	];

	protected $fillable = [
		'account_address_id',
		'account_name',
		'account_slug',
		'account_translation',
		'account_apikey',
		'account_created',
		'account_modified',
		'account_enabled'
	];

    protected static function boot()
    {
        parent::boot();

        // SYSTEM entity delete safeguard
        static::deleting(function (Account $account) {
            if (strtolower($account->account_name) === 'system') {
                throw new \Exception('Attempt to delete SYSTEM account');
            }
        });
    }

	public function address()
	{
		return $this->belongsTo(Address::class, 'account_address_id');
	}

	public function modules()
	{
		// exclude modules where module_flags = 0
		return $this->belongsToMany(Module::class, 'accounts_modules', 'am_account_id', 'am_module_id');
		// return $this->belongsToMany(Module::class, 'accounts_modules', 'am_account_id', 'am_module_id')->where('modules.module_flags','>',0);
	}

	public function settings()
	{
		return $this->belongsToMany(Setting::class, 'account_settings', 'as_account_id', 'as_setting_id')
					->withPivot('as_id', 'as_value');
	}

	public function commands()
	{
		return $this->belongsToMany(Command::class, 'accounts_commands', 'ac_account_id', 'ac_command_id')
					->withPivot('ac_dtmf_id');
	}

	public function accounts_countries()
	{
		return $this->hasMany(AccountsCountry::class, 'ac_account_id');
	}

	public function device_gateways()
	{
		return $this->hasMany(DeviceGateway::class, 'dg_account_id');
	}

	public function device_labels()
	{
		return $this->hasMany(DeviceLabelOld::class, 'dl_account_id');
	}

	public function device_sites()
	{
		return $this->hasMany(DeviceSite::class, 'ds_account_id');
	}

	public function routes()
	{
		return $this->hasMany(Route::class, 'route_account_id');
	}

	public function sessions()
	{
		return $this->hasMany(Session::class, 'session_account_id');
	}

	public function users_roles()
	{
		return $this->hasMany(UsersRole::class, 'ur_account_id');
	}

	public function getAccountTranslationAttribute($value)
	{
		return json_decode($value, true);
	}

	public function scopeEnabled($query)
	{
		return $query->where('account_enabled','=',1);
	}

}
