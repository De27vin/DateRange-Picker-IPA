<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountsCountry
 * 
 * @property int $ac_account_id
 * @property int $ac_country_id
 * 
 * @property Account $account
 * @property Country $country
 *
 * @package App\Models
 */
class AccountsCountry extends Model
{
	protected $table = 'accounts_countries';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'ac_account_id' => 'int',
		'ac_country_id' => 'int'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'ac_account_id');
	}

	public function country()
	{
		return $this->belongsTo(Country::class, 'ac_country_id');
	}
}
