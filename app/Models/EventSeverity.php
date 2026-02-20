<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventSeverity
 * 
 * @property int $es_id
 * @property int $es_severity
 * @property string $es_type
 * 
 * @property Collection|Event[] $events
 *
 * @package App\Models
 */
class EventSeverity extends Model
{
	protected $table = 'event_severities';
	protected $primaryKey = 'es_id';
	public $timestamps = false;

	protected $casts = [
		'es_severity' => 'int'
	];

	protected $fillable = [
		'es_severity',
		'es_type'
	];

	public function events()
	{
		return $this->hasMany(Event::class, 'event_es_id');
	}
}
