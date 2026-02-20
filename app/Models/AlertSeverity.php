<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AlertSeverity
 * 
 * @property int $as_id
 * @property int $as_severity
 * @property string $as_type
 * 
 * @property Collection|AlertType[] $alert_types
 *
 * @package App\Models
 */
class AlertSeverity extends Model
{
	protected $table = 'alert_severities';
	protected $primaryKey = 'as_id';
	public $timestamps = false;

	protected $casts = [
		'as_severity' => 'int'
	];

	protected $fillable = [
		'as_severity',
		'as_type'
	];

	public function alert_types()
	{
		return $this->hasMany(AlertType::class, 'at_as_id');
	}

    public function device_alerts()
    {
        return $this->hasManyThrough(
            DeviceAlert::class,
            AlertType::class,
            'at_as_id',
            'da_at_id',
            'as_id',
            'at_id' 
        );
    }



}
