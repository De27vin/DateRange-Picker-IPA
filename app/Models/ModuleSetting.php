<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModuleSetting
 * 
 * @property int $ms_id
 * @property int $ms_module_id
 * @property int $ms_setting_id
 * @property string $ms_value
 * 
 * @property Module $module
 * @property Setting $setting
 *
 * @package App\Models
 */
class  ModuleSetting extends Model
{
	protected $table = 'module_settings';
	protected $primaryKey = 'ms_id';
	public $timestamps = false;

	protected $casts = [
		'ms_module_id' => 'int',
		'ms_setting_id' => 'int'
	];

	protected $fillable = [
		'ms_module_id',
		'ms_setting_id',
		'ms_value'
	];

	public function module()
	{
		return $this->belongsTo(Module::class, 'ms_module_id');
	}

	public function setting()
	{
		return $this->belongsTo(Setting::class, 'ms_setting_id');
	}
}
