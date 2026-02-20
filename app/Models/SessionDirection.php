<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SessionDirection
 * 
 * @property int $sd_id
 * @property string $sd_type
 * @property string $sd_desc
 * 
 * @property Collection|Session[] $sessions
 *
 * @package App\Models
 */
class SessionDirection extends Model
{
	protected $table = 'session_directions';
	protected $primaryKey = 'sd_id';
	public $timestamps = false;

	protected $fillable = [
		'sd_type',
		'sd_desc'
	];

	public function sessions()
	{
		return $this->hasMany(Session::class, 'session_sd_id');
	}
}
