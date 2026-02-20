<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Alert
 * 
 * @property int $alert_id
 * @property int $alert_session_id
 * @property int $alert_at_id
 * @property string|null $alert_value
 * @property int $alert_active
 * @property Carbon $alert_timestamp
 * 
 * @property AlertType $alert_type
 * @property Session $session
 *
 * @package App\Models
 */
class Alert extends Model
{
	protected $table = 'alerts';
	protected $primaryKey = 'alert_id';
	public $timestamps = false;

	protected $casts = [
		'alert_session_id' => 'int',
		'alert_at_id' => 'int',
		'alert_active' => 'int',
		'alert_timestamp' => 'datetime'
	];

	protected $fillable = [
		'alert_session_id',
		'alert_at_id',
		'alert_value',
		'alert_active',
		'alert_timestamp'
	];

    // PORTING TO TEST - START
    public $with = ['alert_type'];
    // PORTING TO TEST - END

	public function alert_type()
	{
		return $this->belongsTo(AlertType::class, 'alert_at_id');
	}

	public function session()
	{
		return $this->belongsTo(Session::class, 'alert_session_id');
	}

}
