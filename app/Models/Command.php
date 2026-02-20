<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Command
 * 
 * @property int $command_id
 * @property int $command_ct_id
 * @property int $command_key
 * 
 * @property CommandType $command_type
 * @property Collection|Account[] $accounts
 *
 * @package App\Models
 */
class Command extends Model
{
	protected $table = 'commands';
	protected $primaryKey = 'command_id';
	public $timestamps = false;

	protected $casts = [
		'command_ct_id' => 'int',
		'command_key' => 'int'
	];

	protected $fillable = [
		'command_ct_id',
		'command_key'
	];

	public function command_type()
	{
		return $this->belongsTo(CommandType::class, 'command_ct_id');
	}

	public function accounts()
	{
		return $this->belongsToMany(Account::class, 'accounts_commands', 'ac_command_id', 'ac_account_id')
					->withPivot('ac_dtmf_id');
	}

	public function class_type()
	{
		// $classification = ClassType::
		return $this->belongsTo(ClassType::class, 'command_key');
	}

	public function scopeClassification($query)
	{
		return $query->where('command_ct_id','=',1);
	}

}
