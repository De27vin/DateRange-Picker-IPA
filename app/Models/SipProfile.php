<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SipProfile
 * 
 * @property int $sp_id
 * @property int|null $sp_host_id
 * @property string $sp_name
 * @property string $sp_context
 * @property string $sp_sip_ip
 * @property string $sp_rtp_ip
 * @property string $sp_ext_sip_ip
 * @property string $sp_ext_rtp_ip
 * @property int $sp_port
 * @property int $sp_media_timeout
 * @property int $sp_media_hold_timeout
 * @property string $sp_dtmf_type
 * @property int $sp_dtmf_duration
 * @property int $sp_tls
 * @property int $sp_debug
 * @property int $sp_enabled
 * 
 * @property Host|null $host
 * @property Collection|SipGateway[] $sip_gateways
 *
 * @package App\Models
 */
class SipProfile extends Model
{
	protected $table = 'sip_profiles';
	protected $primaryKey = 'sp_id';
	public $timestamps = false;

	protected $casts = [
		'sp_host_id' => 'int',
		'sp_port' => 'int',
		'sp_media_timeout' => 'int',
		'sp_media_hold_timeout' => 'int',
		'sp_dtmf_duration' => 'int',
		'sp_tls' => 'int',
		'sp_debug' => 'int',
		'sp_enabled' => 'int'
	];

	protected $fillable = [
		'sp_host_id',
		'sp_name',
		'sp_context',
		'sp_sip_ip',
		'sp_rtp_ip',
		'sp_ext_sip_ip',
		'sp_ext_rtp_ip',
		'sp_port',
		'sp_media_timeout',
		'sp_media_hold_timeout',
		'sp_dtmf_type',
		'sp_dtmf_duration',
		'sp_tls',
		'sp_debug',
		'sp_enabled'
	];

	public function host()
	{
		return $this->belongsTo(Host::class, 'sp_host_id');
	}

	public function sip_gateways()
	{
		return $this->hasMany(SipGateway::class, 'sipgw_sp_id');
	}
}
