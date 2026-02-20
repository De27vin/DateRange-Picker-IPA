<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UsersRole
 * 
 * @property int $ur_user_id
 * @property int $ur_role_id
 * @property int|null $ur_account_id
 * 
 * @property Account|null $account
 * @property Role $role
 * @property User $user
 *
 * @package App\Models
 */
class UsersRole extends Model
{
	protected $table = 'users_roles';
	public $incrementing = false;
	public $timestamps = false;
	public $with = ['user'];
	
	protected $casts = [
		'ur_user_id' => 'int',
		'ur_role_id' => 'int',
		'ur_account_id' => 'int'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'ur_account_id');
	}

	public function role()
	{
		return $this->belongsTo(Role::class, 'ur_role_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'ur_user_id');
	}
}
