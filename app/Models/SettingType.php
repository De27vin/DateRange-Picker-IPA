<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SettingType
 * 
 * @property int $st_id
 * @property string $st_type
 * @property string $st_desc
 * 
 * @property Collection|Setting[] $settings
 *
 * @package App\Models
 */
class SettingType extends Model
{
	protected $table = 'setting_types';
	protected $primaryKey = 'st_id';
	public $timestamps = false;

	protected $fillable = [
		'st_type',
		'st_desc'
	];

	public function settings()
	{
		return $this->hasMany(Setting::class, 'setting_st_id');
	}
}
