<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Invite
 * 
 * @property int $id
 * @property int|null $account_id
 * @property string $token
 * @property string $email
 * @property string $firstname
 * @property string|null $lastname
 * @property string $status
 * @property Carbon $valid_till
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Invite extends Model
{
	protected $table = 'invites';
	protected $primaryKey = 'invite_id';
	protected $with       = ['roles'];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'invite_created';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'invite_modified';

	protected $casts = [
		'invite_expire' => 'datetime',
		'invite_created' => 'datetime',
		'invite_modified' => 'datetime'
	];

	protected $hidden = [
		'invite_token'
	];

	protected $fillable = [
		'invite_account_id',
		'invite_token',
		'invite_email',
		'invite_firstname',
		'invite_lastname',
		'invite_state',
		'invite_ext',
		'invite_expire',
		'invite_created',
		'invite_modified'
	];

	public function roles()
	{
        return $this->belongsToMany(Role::class, 'invites_roles', 'ir_invite_id', 'ir_role_id');
	}


}
