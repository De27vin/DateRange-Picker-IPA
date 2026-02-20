<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Dtmf
 * 
 * @property int $dtmf_id
 * @property string $dtmf_digit
 * 
 * @property Collection|AccountsCommand[] $accounts_commands
 *
 * @package App\Models
 */
class Dtmf extends Model
{
	protected $table = 'dtmf';
	protected $primaryKey = 'dtmf_id';
	public $timestamps = false;

	protected $fillable = [
		'dtmf_digit'
	];

	public function accounts_commands()
	{
		return $this->hasMany(AccountsCommand::class, 'ac_dtmf_id');
	}
}
