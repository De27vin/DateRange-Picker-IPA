<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceLabelsDevice
 *
 * @property int $dld_dl_id
 * @property int $dld_ds_id
 *
 * @property DeviceSite $device_site
 * @property DeviceLabel $device_label
 *
 * @package App\Models
 */
class DeviceLabelSite extends Model
{
	protected $table = 'device_labels_sites';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'dld_dl_id' => 'int',
		'dld_ds_id' => 'int'
	];

	public function device_site()
	{
		return $this->belongsTo(DeviceSite::class, 'dld_ds_id');
	}

	public function device_label()
	{
		return $this->belongsTo(DeviceLabel::class, 'dld_dl_id');
	}
}
