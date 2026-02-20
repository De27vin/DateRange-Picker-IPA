<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Session
 * 
 * @property int $session_id
 * @property int|null $session_ref_id
 * @property int $session_sp_id
 * @property int $session_sd_id
 * @property int $session_st_id
 * @property int|null $session_account_id
 * @property int|null $session_device_id
 * @property string $session_uuid
 * @property int $session_complete
 * @property int $session_events
 * @property int $session_warnings
 * @property int $session_errors
 * @property int $session_critical
 * @property string $session_host
 * @property Carbon $session_start
 * @property Carbon|null $session_end
 * 
 * @property Account|null $account
 * @property Device|null $device
 * @property Session|null $session
 * @property SessionDirection $session_direction
 * @property SessionPath $session_path
 * @property SessionType $session_type
 * @property Collection|Alert[] $alerts
 * @property Collection|Event[] $events
 * @property Collection|Session[] $sessions
 * @property Collection|Set[] $sets
 *
 * @package App\Models
 */
class Session extends Model
{
	protected $table = 'sessions';
	protected $primaryKey = 'session_id';
	public $timestamps = false;

	protected $casts = [
		'session_ref_id' => 'int',
		'session_sp_id' => 'int',
		'session_sd_id' => 'int',
		'session_st_id' => 'int',
		'session_account_id' => 'int',
		'session_device_id' => 'int',
		'session_complete' => 'int',
		'session_events' => 'int',
		'session_warnings' => 'int',
		'session_errors' => 'int',
		'session_critical' => 'int',
		'session_start' => 'datetime',
		'session_end' => 'datetime'
	];

	protected $fillable = [
		'session_ref_id',
		'session_sp_id',
		'session_sd_id',
		'session_st_id',
		'session_account_id',
		'session_device_id',
		'session_uuid',
		'session_complete',
		'session_events',
		'session_warnings',
		'session_errors',
		'session_critical',
		'session_host',
		'session_start',
		'session_end'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'session_account_id');
	}

	public function device()
	{
		return $this->belongsTo(Device::class, 'session_device_id');
	}

	public function session()
	{
		return $this->belongsTo(Session::class, 'session_ref_id');
	}

	public function session_direction()
	{
		return $this->belongsTo(SessionDirection::class, 'session_sd_id');
	}

	public function session_path()
	{
		return $this->belongsTo(SessionPath::class, 'session_sp_id');
	}

	public function session_type()
	{
		return $this->belongsTo(SessionType::class, 'session_st_id');
	}

	public function alerts()
	{
		return $this->hasMany(Alert::class, 'alert_session_id');
	}

    public function device_alerts()
	{
		return $this->hasMany(DeviceAlert::class, 'da_session_id');
	}

	public function events()
	{
		return $this->hasMany(Event::class, 'event_session_id');
	}

	public function sessions()
	{
		return $this->hasMany(Session::class, 'session_ref_id');
	}

	public function sets()
	{
		return $this->hasMany(Set::class, 'set_session_id');
	}

    public function comments()
	{
		return $this->hasMany(DeviceComment::class, 'dc_session_id');
	}

    public function isAlert()
    {
        return in_array($this->session_type->st_type, ['PERIODICAL','MONITOR']);
    }

    public function isSetRev()
    {
        return in_array($this->session_type->st_type, ['SET','REVIVAL']);
    }

    public function scopeTypes($query, array $types)
    {
        $sessionTypeIds = SessionType::whereIn('st_type', $types)->pluck('st_id');
        return $query->whereIntegerInRaw('session_st_id', $sessionTypeIds);
    }

    public function scopeCalls($query)
    {
        $sessionTypeIds = SessionType::whereIn('st_type', ['CALL', 'ALARM'])->pluck('st_id');
        return $query->whereIntegerInRaw('session_st_id', $sessionTypeIds);
    }

    public function scopeCarCalls($query)
    {
        $sessionTypeIds = SessionType::where('st_type', 'CARCALL')->pluck('st_id');
        return $query->whereIntegerInRaw('session_st_id', $sessionTypeIds);
    }

    public function scopeAlerts($query)
    {
        $sessionTypeIds = SessionType::whereIn('st_type', ['PERIODICAL', 'MONITOR'])->pluck('st_id');
        return $query->whereIntegerInRaw('session_st_id', $sessionTypeIds);
    }

    public function scopeSetRevivals($query)
    {
        $sessionTypeIds = SessionType::whereIn('st_type', ['SET', 'REVIVAL'])->pluck('st_id');
        return $query->whereIntegerInRaw('session_st_id', $sessionTypeIds);
    }

    public function scopeSets($query)
    {
        $sessionTypeIds = SessionType::where('st_type', 'SET')->pluck('st_id');
        return $query->whereIntegerInRaw('session_st_id', $sessionTypeIds);
    }

    public function scopeRevivals($query)
    {
        $sessionTypeIds = SessionType::where('st_type', 'REVIVAL')->pluck('st_id');
        return $query->whereIntegerInRaw('session_st_id', $sessionTypeIds);
    }

    public function scopeTriggers($query)
    {
        $sessionTypeIds = SessionType::where('st_type', 'TRIGGER')->pluck('st_id');
        return $query->whereIntegerInRaw('session_st_id', $sessionTypeIds);
    }

    public function getWarnings($deviceId)
    {
        $severityIds = [EventSeverity::where('es_type', '=', 'WARNING')->pluck('es_id')];
        return $this->getEventsBySeverity($severityIds, $deviceId);
    }

    public function getErrors($deviceId)
    {
        $severityIds = [EventSeverity::whereIn('es_type', '=', 'ERROR')->pluck('es_id')];

        return $this->getEventsBySeverity($severityIds, $deviceId);
    }

    public static function getLatestDeviceSession(int $deviceId)
    {
        return Session::query()->where('session_device_id', '=', $deviceId)->latest('session_start')->first();
    }

    public static function getLatestOnlyDeviceSiteSession(int $deviceSiteId)
    {
        return Session::query()
            ->where('session_ds_id', '=', $deviceSiteId)
            ->where('session_device_id', null)
            ->latest('session_start')
            ->first();
    }

    public static function getLatestDeviceSiteOrDevicesSession(int $deviceSiteId, array $devicesId)
    {
        return Session::query()
            ->where('session_ds_id', '=', $deviceSiteId)
            ->when($devicesId, function ($query, $devicesId) {
                return $query->orWhereIn('session_device_id', $devicesId);
            })
            ->latest('session_start')
            ->first();
    }
}
