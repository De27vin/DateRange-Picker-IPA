<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountModuleSetting
 * 
 * @property int $ams_id
 * @property int $ams_account_id
 * @property int $ams_module_id
 * @property int $ams_setting_id
 * @property string $ams_value
 * 
 * @property Account $account
 * @property Module $module
 * @property Setting $setting
 *
 * @package App\Models
 */
class AccountModuleSetting extends Model
{
	protected $table = 'account_module_settings';
	protected $primaryKey = 'ams_id';
	public $timestamps = false;

	protected $casts = [
		'ams_account_id' => 'int',
		'ams_module_id' => 'int',
		'ams_setting_id' => 'int'
	];

	protected $fillable = [
		'ams_account_id',
		'ams_module_id',
		'ams_setting_id',
		'ams_value'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'ams_account_id');
	}

	public function module()
	{
		return $this->belongsTo(Module::class, 'ams_module_id');
	}

	public function setting()
	{
		return $this->belongsTo(Setting::class, 'ams_setting_id');
	}
}
