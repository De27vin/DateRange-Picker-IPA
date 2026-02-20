<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Searchable\Searchable;
use App\Searchable\SearchResult;


/**
 * Class ModuleType
 * 
 * @property int $mt_id
 * @property string $mt_type
 * @property string $mt_desc
 * 
 * @property Collection|Module[] $modules
 *
 * @package App\Models
 */
class ModuleType extends Model implements Searchable
{
	protected $table = 'module_types';
	protected $primaryKey = 'mt_id';
	public $timestamps = false;

	protected $fillable = [
		'mt_type',
		'mt_desc'
	];

    const NON_DEVICE_TYPES = [
        'EVENT',
        'PROTOCOL',
        'SYSTEM',
    ];

    public function scopeDeviceTypes($query)
    {
        return $query->whereNotIn('mt_type', self::NON_DEVICE_TYPES);
    }

	public function modules()
	{
		return $this->hasMany(Module::class, 'module_mt_id');
	}

    public function getSearchResult(): SearchResult
    {
        return new \App\Searchable\SearchResult(
            $this,
            $this->mt_type,
            $this->mt_desc,
        );
    }
}
