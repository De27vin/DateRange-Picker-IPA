<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountsCommand
 * 
 * @property int $ac_account_id
 * @property int $ac_command_id
 * @property int $ac_dtmf_id
 * 
 * @property Account $account
 * @property Command $command
 * @property Dtmf $dtmf
 *
 * @package App\Models
 */
class AccountsCommand extends Model
{
	protected $table = 'accounts_commands';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'ac_account_id' => 'int',
		'ac_command_id' => 'int',
		'ac_dtmf_id' => 'int'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'ac_account_id');
	}

	public function command()
	{
		return $this->belongsTo(Command::class, 'ac_command_id');
	}

	public function dtmf()
	{
		return $this->belongsTo(Dtmf::class, 'ac_dtmf_id');
	}
}
