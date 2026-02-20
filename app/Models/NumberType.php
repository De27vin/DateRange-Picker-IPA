<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NumberType
 * 
 * @property int $nt_id
 * @property string $nt_type
 * @property string $nt_desc
 *
 * @package App\Models
 */
class NumberType extends Model
{
	protected $table = 'number_types';
	protected $primaryKey = 'nt_id';
	public $timestamps = false;

	protected $fillable = [
		'nt_type',
		'nt_desc'
	];

	public function numbers()
	{
		return $this->hasMany(Number::class, 'number_nt_id');
	}


}
