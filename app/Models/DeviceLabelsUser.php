<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * Class DeviceLabelsUser
 * 
 * @property int $dlu_dl_id
 * @property int $dlu_user_id
 * 
 * @property DeviceLabelOld $device_label
 * @property User $user
 *
 * @package App\Models
 */
class DeviceLabelsUser extends Model
{
	protected $table = 'device_labels_users';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'dlu_dl_id' => 'int',
		'dlu_user_id' => 'int'
	];

	public function device_label()
	{
		return $this->belongsTo(DeviceLabelOld::class, 'dlu_dl_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'dlu_user_id');
	}
}
