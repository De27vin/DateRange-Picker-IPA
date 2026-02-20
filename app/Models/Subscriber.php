<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Subscriber
 * 
 * @property int $id
 * @property string $username
 * @property string $domain
 * @property string $password
 * @property string $email_address
 * @property string $ha1
 * @property string $ha1b
 * @property string|null $rpid
 *
 * @package App\Models
 */
class Subscriber extends Model
{
	protected $table = 'subscriber';
	public $timestamps = false;

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'username',
		'domain',
		'password',
		'email_address',
		'ha1',
		'ha1b',
		'rpid'
	];
}
