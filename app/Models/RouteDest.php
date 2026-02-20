<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RouteDest
 * 
 * @property int $rd_id
 * @property int $rd_route_id
 * @property string $rd_trunk
 * @property string|null $rd_dest
 * @property int $rd_timeout
 * @property int $rd_order
 * @property int $rd_enabled
 * 
 * @property Route $route
 *
 * @package App\Models
 */
class RouteDest extends Model
{
	protected $table = 'route_dests';
	protected $primaryKey = 'rd_id';
	public $timestamps = false;

	protected $casts = [
		'rd_route_id' => 'int',
		'rd_timeout' => 'int',
		'rd_order' => 'int',
		'rd_enabled' => 'int'
	];

	protected $fillable = [
		'rd_route_id',
		'rd_trunk',
		'rd_dest',
		'rd_timeout',
		'rd_order',
		'rd_enabled'
	];

	public function route()
	{
		return $this->belongsTo(Route::class, 'rd_route_id');
	}
}
