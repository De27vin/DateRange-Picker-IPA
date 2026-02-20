<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Email
 * 
 * @property int $email_id
 * @property int|null $email_user_id
 * @property string $email_address
 * @property Carbon $email_created
 * @property Carbon|null $email_verified
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class Email extends Model
{
	protected $table = 'emails';
	protected $primaryKey = 'email_id';
	public $timestamps = false;

	protected $casts = [
		'email_user_id' => 'int',
		'email_created' => 'datetime',
		'email_verified' => 'datetime'
	];

	protected $fillable = [
		'email_user_id',
		'email_address',
		'email_created',
		'email_verified'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'email_user_id');
	}

	public function getEmailAttribute()
	{
	    return $this->getAttribute('email_address');
	}

}
