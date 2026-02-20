<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 * 
 * @property int $role_id
 * @property string $role_type
 * @property string $role_desc
 * 
 * @property Collection|Setting[] $settings
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Role extends Model
{
	protected $table      = 'roles';
	protected $primaryKey = 'role_id';
	public $timestamps    = false;
	public $order         = ['site', 'admin', 'callcenter', 'agent', 'user', 'mobile', 'login'];

	protected $fillable = [
		'role_type',
		'role_desc'
	];
	public function settings()
	{
		return $this->hasMany(Setting::class, 'setting_write_role_id');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'users_roles', 'ur_role_id', 'ur_user_id')
					->withPivot('ur_account_id');
	}

	public function invites()
	{
		return $this->belongsToMany(Invite::class, 'users_roles', 'ir_role_id', 'ur_invite_id');
	}
}
