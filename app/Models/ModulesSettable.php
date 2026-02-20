<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModulesSettable
 * 
 * @property int $ms_module_id
 * @property int $ms_setting_id
 * 
 * @property Module $module
 * @property Setting $setting
 *
 * @package App\Models
 */
class ModulesSettable extends Model
{
	protected $table = 'modules_settables';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'ms_module_id' => 'int',
		'ms_setting_id' => 'int'
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
