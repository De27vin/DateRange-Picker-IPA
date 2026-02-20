<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Route
 * 
 * @property int $route_id
 * @property int|null $route_account_id
 * @property int|null $route_language_id
 * @property int|null $route_module_id
 * @property int|null $route_function_id
 * @property string $route_name
 * @property string|null $route_anumber
 * @property string|null $route_bnumber
 * @property int $route_copycid
 * @property int $route_record
 * @property Carbon $route_start
 * @property Carbon $route_end
 * @property int $route_days
 * @property int $route_order
 * @property int $route_enabled
 * 
 * @property Account|null $account
 * @property Function|null $function
 * @property Language|null $language
 * @property Module|null $module
 * @property Collection|RouteDest[] $route_dests
 *
 * @package App\Models
 */
class Route extends Model
{
	protected $table = 'routes';
	protected $primaryKey = 'route_id';
	public $timestamps = false;

	protected $casts = [
		'route_account_id' => 'int',
		'route_language_id' => 'int',
		'route_module_id' => 'int',
		'route_function_id' => 'int',
		'route_copycid' => 'int',
		'route_record' => 'int',
		'route_start' => 'datetime',
		'route_end' => 'datetime',
		'route_days' => 'int',
		'route_order' => 'int',
		'route_enabled' => 'int'
	];

	protected $fillable = [
		'route_account_id',
		'route_language_id',
		'route_module_id',
		'route_function_id',
		'route_name',
		'route_anumber',
		'route_bnumber',
		'route_copycid',
		'route_record',
		'route_start',
		'route_end',
		'route_days',
		'route_order',
		'route_enabled'
	];

	public function account()
	{
		return $this->belongsTo(\App\Models\Account::class, 'route_account_id');
	}

	public function function()
	{
		return $this->belongsTo(\App\Models\Funktion::class, 'route_function_id');
	}

	public function language()
	{
		return $this->belongsTo(\App\Models\Language::class, 'route_language_id');
	}

	public function module()
	{
		return $this->belongsTo(\App\Models\Module::class, 'route_module_id');
	}

	public function route_dests()
	{
		return $this->hasMany(\App\Models\RouteDest::class, 'rd_route_id');
	}
}
