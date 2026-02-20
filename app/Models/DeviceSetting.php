<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceSetting
 * 
 * @property int $ds_id
 * @property int $ds_device_id
 * @property int $ds_setting_id
 * @property string $ds_value
 * 
 * @property Device $device
 * @property Setting $setting
 *
 * @package App\Models
 */
class DeviceSetting extends Model
{
	protected $table = 'device_settings';
	protected $primaryKey = 'ds_id';
	public $timestamps = false;

	protected $casts = [
		'ds_device_id' => 'int',
		'ds_setting_id' => 'int'
	];

	protected $fillable = [
		'ds_device_id',
		'ds_setting_id',
		'ds_value'
	];

	public function device()
	{
		return $this->belongsTo(Device::class, 'ds_device_id');
	}

	public function setting()
	{
		return $this->belongsTo(Setting::class, 'ds_setting_id');
	}
}
