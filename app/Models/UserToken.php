<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserToken
 * 
 * @property int $ut_id
 * @property int $ut_user_id
 * @property string $ut_agent
 * @property string $ut_token
 * @property int $ut_created
 * @property int $ut_expires
 * 
 * @property User $user
 *
 * @package App\Models
 */
class UserToken extends Model
{
	protected $table = 'user_tokens';
	protected $primaryKey = 'ut_id';
	public $timestamps = false;

	protected $casts = [
		'ut_user_id' => 'int',
		'ut_created' => 'int',
		'ut_expires' => 'int'
	];

	protected $hidden = [
		'ut_token'
	];

	protected $fillable = [
		'ut_user_id',
		'ut_agent',
		'ut_token',
		'ut_created',
		'ut_expires'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'ut_user_id');
	}
}
