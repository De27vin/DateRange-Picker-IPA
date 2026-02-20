<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ClassType
 * 
 * @property int $ct_id
 * @property string $ct_type
 * @property string $ct_desc
 *
 * @package App\Models
 */
class ClassType extends Model
{
	protected $table = 'class_types';
	protected $primaryKey = 'ct_id';
	public $timestamps = false;

	protected $fillable = [
		'ct_type',
		'ct_desc'
	];
}
