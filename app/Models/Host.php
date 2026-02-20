<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Host
 * 
 * @property int $host_id
 * @property string $host_fqdn
 * @property int $host_order
 * @property int $host_active
 * 
 * @property Collection|Setting[] $settings
 * @property Collection|SipGateway[] $sip_gateways
 * @property Collection|SipProfile[] $sip_profiles
 * @property Collection|SmsGateway[] $sms_gateways
 *
 * @package App\Models
 */
class Host extends Model
{
	protected $table = 'hosts';
	protected $primaryKey = 'host_id';
	public $timestamps = false;

	protected $casts = [
		'host_order' => 'int',
		'host_active' => 'int'
	];

	protected $fillable = [
		'host_fqdn',
		'host_order',
		'host_active'
	];

	public function settings()
	{
		return $this->belongsToMany(Setting::class, 'host_settings', 'hs_host_id', 'hs_setting_id')
					->withPivot('hs_id', 'hs_value');
	}

	public function sip_gateways()
	{
		return $this->hasMany(SipGateway::class, 'sipgw_host_id');
	}

	public function sip_profiles()
	{
		return $this->hasMany(SipProfile::class, 'sp_host_id');
	}

	public function sms_gateways()
	{
		return $this->hasMany(SmsGateway::class, 'smsgw_host_id');
	}
}
