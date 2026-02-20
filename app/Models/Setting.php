<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 * 
 * @property int $setting_id
 * @property int|null $setting_read_role_id
 * @property int|null $setting_write_role_id
 * @property int $setting_st_id
 * @property string $setting_key
 * @property string $setting_value
 * 
 * @property Role|null $role
 * @property SettingType $setting_type
 * @property Collection|Account[] $accounts
 * @property Collection|Module[] $modules
 * @property Collection|DeviceLabelOld[] $device_labels
 * @property Collection|Device[] $devices
 * @property Collection|Host[] $hosts
 * @property Collection|ModulesSettable[] $modules_settables
 * @property Collection|Set[] $sets
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Setting extends Model
{
	protected $table = 'settings';
	protected $primaryKey = 'setting_id';
	public $timestamps = false;

	protected $casts = [
		'setting_read_role_id' => 'int',
		'setting_write_role_id' => 'int',
		'setting_st_id' => 'int'
	];

	protected $fillable = [
		'setting_read_role_id',
		'setting_write_role_id',
		'setting_st_id',
		'setting_key',
		'setting_value'
	];

	public function role()
	{
		return $this->belongsTo(Role::class, 'setting_write_role_id');
	}

	public function setting_type()
	{
		return $this->belongsTo(SettingType::class, 'setting_st_id');
	}

	public function accounts()
	{
		return $this->belongsToMany(Account::class, 'account_settings', 'as_setting_id', 'as_account_id')
					->withPivot('as_id', 'as_value');
	}

	public function modules()
	{
		return $this->belongsToMany(Module::class, 'module_settings', 'ms_setting_id', 'ms_module_id')
					->withPivot('ms_id', 'ms_value');
	}

	public function device_labels()
	{
		return $this->belongsToMany(DeviceLabelOld::class, 'device_label_settings', 'dls_setting_id', 'dls_dl_id')
					->withPivot('dls_id', 'dls_value');
	}

	public function devices()
	{
		return $this->belongsToMany(Device::class, 'device_settings', 'ds_setting_id', 'ds_device_id')
					->withPivot('ds_id', 'ds_value');
	}

	public function hosts()
	{
		return $this->belongsToMany(Host::class, 'host_settings', 'hs_setting_id', 'hs_host_id')
					->withPivot('hs_id', 'hs_value');
	}

	public function modules_settables()
	{
		return $this->hasMany(ModulesSettable::class, 'ms_setting_id');
	}

	public function sets()
	{
		return $this->hasMany(Set::class, 'set_setting_id');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'user_settings', 'us_setting_id', 'us_user_id')
					->withPivot('us_id', 'us_value');
	}

    // PORTING TO TEST BELOW

    public function scopeOnlyCustomSettings($query)
    {
        return $query->whereRaw("
            setting_key = 'call.dtmf.tx.gain'
            or setting_key = 'call.dtmf.tx.mark'
            or setting_key = 'call.dtmf.tx.space'
            or setting_key = 'call.recording'
            or setting_key = 'device.initial.rx.ignore'
        ");
    }

    public function scopeSettingsOnly($query)
    {
        return $query->whereRaw("
            setting_key like 'device.%'
            or setting_key = 'call.dtmf.tx.gain'
            or setting_key = 'call.dtmf.tx.mark'
            or setting_key = 'call.dtmf.tx.space'
            or setting_key = 'call.recording'
        ");
    }
}
