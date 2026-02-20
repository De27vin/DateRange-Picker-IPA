<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModulesFunction
 * 
 * @property int $mf_module_id
 * @property int $mf_function_id
 * 
 * @property Function $function
 * @property Module $module
 *
 * @package App\Models
 */
class ModulesFunktion extends Model
{
	protected $table = 'modules_functions';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'mf_module_id' => 'int',
		'mf_function_id' => 'int'
	];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function funktions()
    {
        return $this->belongsToMany(Funktion::class, 'mf_function_id', 'function_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'mf_module_id', 'module_id');
    }
}
