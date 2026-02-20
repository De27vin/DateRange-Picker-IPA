<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceAlert
 * 
 * @property int $da_id
 * @property int $da_device_id
 * @property int $da_at_id
 * @property string|null $da_value
 * @property Carbon $da_timestamp
 * 
 * @property AlertType $alert_type
 * @property Device $device
 *
 * @package App\Models
 */
class DeviceAlert extends Model
{
	protected $table      = 'device_alerts';
	protected $primaryKey = 'da_id';
	protected $with       = ['alert_type'];
	public $timestamps    = false;
	protected $warnings;
	protected $errors;

	protected $casts = [
		'da_device_id' => 'int',
		'da_at_id' => 'int',
		'da_timestamp' => 'datetime'
	];

	protected $fillable = [
		'da_device_id',
		'da_at_id',
		'da_value',
		'da_timestamp'
	];

    public function session()
    {
        return $this->belongsTo(Session::class, 'da_session_id');
    }

	public function alert_type()
	{
		return $this->belongsTo(AlertType::class, 'da_at_id');
	}

	public function warnings()
	{
		return $this->belongsTo(AlertType::class, 'da_at_id')->warnings();
	}

	public function errors()
	{
		return $this->belongsTo(AlertType::class, 'da_at_id')->errors();
	}

	public function device()
	{
		return $this->belongsTo(Device::class, 'da_device_id');
	}

    public function scopeMissing($query)
    {
        return $query->whereHas('alert_type', function($q){
            return $q->where('alert_types.at_type','=','PERIODICAL');
            // return $q->where('alert_types.at_type','=','PERIODICAL');
        });
    }

	public function warning()
	{
		return $this->belongsTo(AlertType::class, 'da_at_id');
	}

    public function scopeWarnings($query)
    {
        return $query->whereHas('alert_type', function($q){
            return $q->where('alert_types.at_type','=','PERIODICAL');
            // return $q->where('alert_types.at_type','=','PERIODICAL');
        });
    }

    public function scopeErrors($query)
    {
        return $query->whereHas('alert_type', function($q){
            return $q->where('alert_types.at_type','=','PERIODICAL');
            // return $q->where('alert_types.at_type','=','PERIODICAL');
        });
    }

}
