<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Searchable\Searchable;
use App\Searchable\SearchResult;

/**
 * Class Module
 * 
 * @property int $module_id
 * @property int $module_mt_id
 * @property string $module_name
 * @property string $module_desc
 * @property string $module_version
 * @property int $module_flags
 * 
 * @property ModuleType $module_type
 * @property Collection|Account[] $accounts
 * @property Collection|Setting[] $settings
 * @property Collection|DeviceSite[] $device_sites
 * @property Collection|Function[] $functions
 * @property Collection|ModulesSettable[] $modules_settables
 * @property Collection|Route[] $routes
 *
 * @package App\Models
 */
class Module extends Model implements Searchable
{
	protected $table      = 'modules';
	protected $primaryKey = 'module_id';
	public $timestamps    = false;

	const fieldsFlaggable = [
		'identity'      => 1,
        'module'        => 2,
		'pin'           => 4,
		'numbers'       => 8,
	];

	protected $casts = [
		'module_mt_id' => 'int',
		'module_flags' => 'int'
	];

	protected $fillable = [
		'module_mt_id',
		'module_name',
		'module_version',
		'module_flags'
	];

	public function module_type()
	{
		return $this->belongsTo(ModuleType::class, 'module_mt_id');
	}

	public function accounts()
	{
		return $this->belongsToMany(Account::class, 'accounts_modules', 'am_module_id', 'am_account_id');
	}

    public function supported_modules()
    {
        // modules supported by protocol module
        return $this->belongsToMany(Module::class, 'modules_matrix', 'mm_protocol_id', 'mm_module_id');
    }

    // THIS IS PRETTY NASTY - START
	public function settings()
	{
		return $this->belongsToMany(Setting::class, 'module_settings', 'ms_module_id', 'ms_setting_id')
					->withPivot('ms_id', 'ms_value');
	}

    public function modules_settables()
    {
        return $this->hasMany(ModulesSettable::class, 'ms_module_id');
    }

    public function settables()
    {
        return $this->belongsToMany('App\Models\Setting', 'modules_settables', 'ms_module_id', 'ms_setting_id');
    }

    // THIS RELATION MAKES NO SENSE AS IT RETURNS MODULE SETTING INSTEAD OF AMS WHICH HAVE ITS OWN VALUES
    public function account_settings()
    {
        return $this->belongsToMany('App\Models\ModuleSetting', 'account_module_settings', 'ams_module_id', 'ams_setting_id');
    }

    public function module_settings()
    {
        return $this->hasMany('App\Models\ModuleSetting', 'ms_module_id', 'module_id');
    }
    // THIS IS PRETTY NASTY - END


	public function device_sites()
	{
		return $this->hasMany(DeviceSite::class, 'ds_protocol_id');
	}

    public function devices()
    {
        return $this->hasMany(Device::class, 'device_module_id');
    }

	public function funktions()
	{
		return $this->belongsToMany(\App\Models\Funktion::class, 'modules_functions', 'mf_module_id', 'mf_function_id');
	}

	public function routes()
	{
		return $this->hasMany(Route::class, 'route_module_id');
	}
	
	public function fieldIsRequired($fieldName)
	{
		if (!array_key_exists($fieldName, self::fieldsFlaggable) || $this->module_flags == 0) {
			return false;
		}
		return (($this->module_flags & self::fieldsFlaggable[$fieldName]) == self::fieldsFlaggable[$fieldName]);
	}

    /**
     * @param $query
     * @return mixed
     */
    public function scopeAvailable($query)
    {
        return $query->where('module_flags', '!=', 0);
    }

    public function getSearchResult(): SearchResult
    {
        return new \App\Searchable\SearchResult(
            $this,
            $this->module_name,
            $this->module_desc,
        );
    }
}
