<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class InviteRole
 * 
 * @property int|null $invite_id
 * @property int|null $role_id
 *
 * @package App\Models
 */
class InviteRole extends Model
{
	protected $table = 'invite_role';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'invite_id' => 'int',
		'role_id' => 'int'
	];

	protected $fillable = [
		'invite_id',
		'role_id'
	];
}
