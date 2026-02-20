<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Set
 * 
 * @property int $set_id
 * @property int $set_session_id
 * @property int $set_setting_id
 * @property string $set_value
 * @property int $set_success
 * @property Carbon $set_timestamp
 * 
 * @property Session $session
 * @property Setting $setting
 *
 * @package App\Models
 */
class Set extends Model
{
	protected $table = 'sets';
	protected $primaryKey = 'set_id';
	public $timestamps = false;

	protected $casts = [
		'set_session_id' => 'int',
		'set_setting_id' => 'int',
		'set_success' => 'int',
		'set_timestamp' => 'datetime'
	];

	protected $fillable = [
		'set_session_id',
		'set_setting_id',
		'set_value',
		'set_success',
		'set_timestamp'
	];

    // PORTING TO TEST - START
    public $with = ['setting'];
    // PORTING TO TEST - END


	public function session()
	{
		return $this->belongsTo(Session::class, 'set_session_id');
	}

	public function setting()
	{
		return $this->belongsTo(Setting::class, 'set_setting_id');
	}
}
