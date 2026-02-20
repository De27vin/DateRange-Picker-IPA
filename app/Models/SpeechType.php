<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpeechType
 * 
 * @property int $st_id
 * @property string $st_type
 * @property string $st_desc
 *
 * @package App\Models
 */
class SpeechType extends Model
{
	protected $table = 'speech_types';
	protected $primaryKey = 'st_id';
	public $timestamps = false;

	protected $fillable = [
		'st_type',
		'st_desc'
	];
}
