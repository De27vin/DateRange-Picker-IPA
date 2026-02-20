<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HostSetting
 * 
 * @property int $hs_id
 * @property int $hs_host_id
 * @property int $hs_setting_id
 * @property string $hs_value
 * 
 * @property Host $host
 * @property Setting $setting
 *
 * @package App\Models
 */
class HostSetting extends Model
{
	protected $table = 'host_settings';
	protected $primaryKey = 'hs_id';
	public $timestamps = false;

	protected $casts = [
		'hs_host_id' => 'int',
		'hs_setting_id' => 'int'
	];

	protected $fillable = [
		'hs_host_id',
		'hs_setting_id',
		'hs_value'
	];

	public function host()
	{
		return $this->belongsTo(Host::class, 'hs_host_id');
	}

	public function setting()
	{
		return $this->belongsTo(Setting::class, 'hs_setting_id');
	}
}
