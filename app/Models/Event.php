<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 * 
 * @property int $event_id
 * @property int $event_session_id
 * @property int $event_et_id
 * @property int $event_es_id
 * @property string|null $event_value
 * @property Carbon $event_timestamp
 * 
 * @property EventSeverity $event_severity
 * @property EventType $event_type
 * @property Session $session
 *
 * @package App\Models
 */
class Event extends Model
{
	protected $table = 'events';
	protected $primaryKey = 'event_id';
	public $timestamps = false;

	protected $casts = [
		'event_session_id' => 'int',
		'event_et_id' => 'int',
		'event_es_id' => 'int',
		'event_timestamp' => 'datetime'
	];
	// public $with = ['event_severity:es_id,es_type', 'event_type:et_id,et_type'];
	
	protected $fillable = [
		'event_session_id',
		'event_et_id',
		'event_es_id',
		'event_value',
		'event_timestamp'
	];

    // PORTING TO TEST - START
    public $with = ['event_type', 'event_severity'];
    // PORTING TO TEST - END

	public function event_severity()
	{
		return $this->belongsTo(EventSeverity::class, 'event_es_id');
	}

	public function event_type()
	{
		return $this->belongsTo(EventType::class, 'event_et_id');
	}

	public function session()
	{
		return $this->belongsTo(Session::class, 'event_session_id');
	}


    /**
     * @param $query
     * @return mixed
     */
    public function scopeWarnings($query)
    {
        return $query->where('event_es_id', '=', EventSeverity::where('es_type', '=', 'WARNING')->pluck('es_id'));
    }


    /**
     * @param $query
     * @return mixed
     */
    public function scopeErrors($query)
    {
        return $query->where('event_es_id', '=', EventSeverity::where('es_type', '=', 'ERROR')->pluck('es_id'));
    }


}
