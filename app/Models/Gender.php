<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Gender
 * 
 * @property int $gender_id
 * @property string $gender_type
 * @property string $gender_desc
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Gender extends Model
{
	protected $table = 'genders';
	protected $primaryKey = 'gender_id';
	public $timestamps = false;

	protected $fillable = [
		'gender_type',
		'gender_desc'
	];

	public function users()
	{
		return $this->hasMany(User::class, 'user_gender_id');
	}
}
