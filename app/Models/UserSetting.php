<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserSetting
 * 
 * @property int $us_id
 * @property int $us_user_id
 * @property int $us_setting_id
 * @property string $us_value
 * 
 * @property Setting $setting
 * @property User $user
 *
 * @package App\Models
 */
class UserSetting extends Model
{
	protected $table = 'user_settings';
	protected $primaryKey = 'us_id';
	public $timestamps = false;

	protected $casts = [
		'us_user_id' => 'int',
		'us_setting_id' => 'int'
	];

	protected $fillable = [
		'us_user_id',
		'us_setting_id',
		'us_value'
	];

	public function setting()
	{
		return $this->belongsTo(Setting::class, 'us_setting_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'us_user_id');
	}
}
