<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SipGateway
 * 
 * @property int $sipgw_id
 * @property int $sipgw_sp_id
 * @property int|null $sipgw_host_id
 * @property string $sipgw_name
 * @property string $sipgw_context
 * @property string $sipgw_proxy
 * @property string|null $sipgw_username
 * @property string|null $sipgw_password
 * @property int $sipgw_register
 * @property string $sipgw_register_transport
 * @property int $sipgw_register_expires
 * @property int $sipgw_ping
 * @property int $sipgw_enabled
 * 
 * @property Host|null $host
 * @property SipProfile $sip_profile
 *
 * @package App\Models
 */
class SipGateway extends Model
{
	protected $table = 'sip_gateways';
	protected $primaryKey = 'sipgw_id';
	public $timestamps = false;

	protected $casts = [
		'sipgw_sp_id' => 'int',
		'sipgw_host_id' => 'int',
		'sipgw_register' => 'int',
		'sipgw_register_expires' => 'int',
		'sipgw_ping' => 'int',
		'sipgw_enabled' => 'int'
	];

	protected $hidden = [
		'sipgw_password'
	];

	protected $fillable = [
		'sipgw_sp_id',
		'sipgw_host_id',
		'sipgw_name',
		'sipgw_context',
		'sipgw_proxy',
		'sipgw_username',
		'sipgw_password',
		'sipgw_register',
		'sipgw_register_transport',
		'sipgw_register_expires',
		'sipgw_ping',
		'sipgw_enabled'
	];

	public function host()
	{
		return $this->belongsTo(Host::class, 'sipgw_host_id');
	}

	public function sip_profile()
	{
		return $this->belongsTo(SipProfile::class, 'sipgw_sp_id');
	}
}
