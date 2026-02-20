<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SmsGateway
 * 
 * @property int $smsgw_id
 * @property int|null $smsgw_host_id
 * @property string $smsgw_name
 * @property string $smsgw_context
 * @property string $smsgw_proxy
 * @property int $smsgw_port
 * @property string $smsgw_username
 * @property string $smsgw_password
 * @property string|null $smsgw_type
 * @property string|null $smsgw_prefix
 * @property int $smsgw_debug
 * @property int $smsgw_enabled
 * 
 * @property Host|null $host
 *
 * @package App\Models
 */
class SmsGateway extends Model
{
	protected $table = 'sms_gateways';
	protected $primaryKey = 'smsgw_id';
	public $timestamps = false;

	protected $casts = [
		'smsgw_host_id' => 'int',
		'smsgw_port' => 'int',
		'smsgw_debug' => 'int',
		'smsgw_enabled' => 'int'
	];

	protected $hidden = [
		'smsgw_password'
	];

	protected $fillable = [
		'smsgw_host_id',
		'smsgw_name',
		'smsgw_context',
		'smsgw_proxy',
		'smsgw_port',
		'smsgw_username',
		'smsgw_password',
		'smsgw_type',
		'smsgw_prefix',
		'smsgw_debug',
		'smsgw_enabled'
	];

	public function host()
	{
		return $this->belongsTo(Host::class, 'smsgw_host_id');
	}
}
