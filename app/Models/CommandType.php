<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommandType
 * 
 * @property int $ct_id
 * @property string $ct_type
 * @property string $ct_desc
 * 
 * @property Collection|Command[] $commands
 *
 * @package App\Models
 */
class CommandType extends Model
{
	protected $table = 'command_types';
	protected $primaryKey = 'ct_id';
	public $timestamps = false;

	protected $fillable = [
		'ct_type',
		'ct_desc'
	];

	public function commands()
	{
		return $this->hasMany(Command::class, 'command_ct_id');
	}
}
