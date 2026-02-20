<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceLabelSetting
 * 
 * @property int $dls_id
 * @property int $dls_dl_id
 * @property int $dls_setting_id
 * @property string $dls_value
 * 
 * @property DeviceLabel $device_label
 * @property Setting $setting
 *
 * @package App\Models
 */
class DeviceLabelSetting extends Model
{
	protected $table = 'device_label_settings';
	protected $primaryKey = 'dls_id';
	public $timestamps = false;

	protected $casts = [
		'dls_dl_id' => 'int',
		'dls_setting_id' => 'int'
	];

	protected $fillable = [
		'dls_dl_id',
		'dls_setting_id',
		'dls_value'
	];

	public function device_label()
	{
		return $this->belongsTo(DeviceLabel::class, 'dls_dl_id');
	}

	public function setting()
	{
		return $this->belongsTo(Setting::class, 'dls_setting_id');
	}
}
