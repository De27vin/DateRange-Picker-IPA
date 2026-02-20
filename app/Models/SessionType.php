<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SessionType
 * 
 * @property int $st_id
 * @property string $st_type
 * @property string $st_desc
 * 
 * @property Collection|Session[] $sessions
 *
 * @package App\Models
 */
class SessionType extends Model
{
	protected $table = 'session_types';
	protected $primaryKey = 'st_id';
	public $timestamps = false;

	protected $fillable = [
		'st_type',
		'st_desc'
	];

	public function sessions()
	{
		return $this->hasMany(Session::class, 'session_st_id');
	}
}
