<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountSetting
 * 
 * @property int $as_id
 * @property int $as_account_id
 * @property int $as_setting_id
 * @property string $as_value
 * 
 * @property Account $account
 * @property Setting $setting
 *
 * @package App\Models
 */
class AccountSetting extends Model
{
	protected $table = 'account_settings';
	protected $primaryKey = 'as_id';
	public $timestamps = false;

	protected $casts = [
		'as_account_id' => 'int',
		'as_setting_id' => 'int'
	];

	protected $fillable = [
		'as_account_id',
		'as_setting_id',
		'as_value'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'as_account_id');
	}

	public function setting()
	{
		return $this->belongsTo(Setting::class, 'as_setting_id');
	}
}
