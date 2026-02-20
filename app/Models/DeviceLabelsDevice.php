<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * Class DeviceLabelsDevice
 * 
 * @property int $dld_dl_id
 * @property int $dld_device_id
 * 
 * @property Device $device
 * @property DeviceLabelOld $device_label
 *
 * @package App\Models
 */
class DeviceLabelsDevice extends Model
{
	protected $table = 'device_labels_devices';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'dld_dl_id' => 'int',
		'dld_device_id' => 'int'
	];

	public function device()
	{
		return $this->belongsTo(Device::class, 'dld_device_id');
	}

	public function device_label()
	{
		return $this->belongsTo(DeviceLabelOld::class, 'dld_dl_id');
	}
}
