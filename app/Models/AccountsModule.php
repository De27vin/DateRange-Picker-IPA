<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountsModule
 * 
 * @property int $am_account_id
 * @property int $am_module_id
 * 
 * @property Account $account
 * @property Module $module
 *
 * @package App\Models
 */
class AccountsModule extends Model
{
	protected $table = 'accounts_modules';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'am_account_id' => 'int',
		'am_module_id' => 'int'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'am_account_id');
	}

	public function module()
	{
		return $this->belongsTo(Module::class, 'am_module_id');
	}
}
