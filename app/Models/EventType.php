<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventType
 * 
 * @property int $et_id
 * @property string $et_type
 * @property string $et_desc
 * 
 * @property Collection|Event[] $events
 *
 * @package App\Models
 */
class EventType extends Model
{
	protected $table = 'event_types';
	protected $primaryKey = 'et_id';
	public $timestamps = false;

	protected $fillable = [
		'et_type',
		'et_desc'
	];

	public function events()
	{
		return $this->hasMany(Event::class, 'event_et_id');
	}
}
