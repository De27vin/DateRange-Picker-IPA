<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Function
 * 
 * @property int $function_id
 * @property string $function_call
 * 
 * @property Collection|Module[] $modules
 * @property Collection|Route[] $routes
 *
 * @package App\Models
 */
class Funktion extends Model
{
	protected $table = 'functions';
	protected $primaryKey = 'function_id';
	public $timestamps = false;

	protected $fillable = [
		'function_call'
	];

	public function modules()
	{
		return $this->belongsToMany(Module::class, 'modules_functions', 'mf_function_id', 'mf_module_id');
	}

	public function routes()
	{
		return $this->hasMany(Route::class, 'route_function_id');
	}
}
