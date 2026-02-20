<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceGatewayType
 *
 * @deprecated
 *
 * @property int $dgt_id
 * @property string $dgt_type
 * @property string $dgt_desc
 * 
 * @property Collection|DeviceGateway[] $device_gateways
 *
 * @package App\Models
 */
class DeviceGatewayType extends Model
{
	protected $table = 'device_gateway_types';
	protected $primaryKey = 'dgt_id';
	public $timestamps = false;

	protected $fillable = [
		'dgt_type',
		'dgt_desc'
	];

	public function device_gateways()
	{
		return $this->hasMany(DeviceGateway::class, 'dg_dgt_id');
	}
}
