<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SessionPath
 * 
 * @property int $sp_id
 * @property string $sp_type
 * @property string $sp_desc
 * 
 * @property Collection|Session[] $sessions
 *
 * @package App\Models
 */
class SessionPath extends Model
{
	protected $table = 'session_paths';
	protected $primaryKey = 'sp_id';
	public $timestamps = false;

	protected $fillable = [
		'sp_type',
		'sp_desc'
	];

	public function sessions()
	{
		return $this->hasMany(Session::class, 'session_sp_id');
	}
}
