<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserSession
 * 
 * @property string $us_id
 * @property int $us_last_active
 * @property string $us_contents
 *
 * @package App\Models
 */
class UserSession extends Model
{
	protected $table = 'user_sessions';
	protected $primaryKey = 'us_id';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'us_last_active' => 'int'
	];

	protected $fillable = [
		'us_last_active',
		'us_contents'
	];
}
