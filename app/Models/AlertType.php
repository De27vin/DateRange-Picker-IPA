<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AlertType
 * 
 * @property int $at_id
 * @property int $at_as_id
 * @property string $at_type
 * @property string $at_desc
 * 
 * @property AlertSeverity $alert_severity
 * @property Collection|Alert[] $alerts
 * @property Collection|DeviceAlert[] $device_alerts
 *
 * @package App\Models
 */
class AlertType extends Model
{
	protected $table      = 'alert_types';
	protected $primaryKey = 'at_id';
	public $timestamps    = false;
	protected $with = ['alert_severity'];

	protected $casts = [
		'at_as_id' => 'int'
	];

	protected $fillable = [
		'at_as_id',
		'at_type',
		'at_desc'
	];

	public function alert_severity()
	{
		return $this->belongsTo(AlertSeverity::class, 'at_as_id');
	}

	public function alerts()
	{
		return $this->hasMany(Alert::class, 'alert_at_id');
	}

	public function device_alerts()
	{
		return $this->hasMany(DeviceAlert::class, 'da_at_id');
	}

    public function getIsWarningAttribute()
    {
        return ($this->alert_severity->as_type = 'MINOR');
    }

    public function getIsErrorAttribute()
    {
        return ($this->alert_severity->as_type = 'MAJOR');
    }

    public function getNameAttribute()
    {
        return $this->at_type;
    }

    public function getIdAttribute()
    {
        return $this->at_id;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeWarnings($query)
    {
        return $query->whereHas('alert_severity', function($q){
        	$q->where('alert_severities.as_type','=','MINOR');
        });
        // return $query->where('at_as_id', '=', AlertSeverity::where('as_type', '=', 'WARNING')->pluck('as_id'));
	}

    /**
     * @param $query
     * @return mixed
     */
    public function scopeErrors($query)
    {
        return $query->whereHas('alert_severity', function($q){
        	$q->where('alert_severities.as_type','=','MAJOR');
        });
        // return $query->where('at_as_id', '=', AlertSeverity::where('as_type', '=', 'ERROR')->pluck('as_id'));
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeWarning($query)
    {
        return $query->whereHas('alert_severity', function($q){
        	$q->where('alert_severities.as_type','=','MAJOR');
        });
        // return $query->where('at_as_id', '=', AlertSeverity::where('as_type', '=', 'WARNING')->pluck('as_id'));
	}

    /**
     * @param $query
     * @return mixed
     */
    public function scopeError($query)
    {
        return $query->whereHas('alert_severity', function($q){
        	$q->where('alert_severities.as_type','=','MINOR');
        });
        // return $query->where('at_as_id', '=', AlertSeverity::where('as_type', '=', 'ERROR')->pluck('as_id'));
    }


}
