<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ModuleType
 *
 * @property int $dt_id
 * @property string $dt_type
 * @property string $dt_desc
 *
 * @property Collection|Device[] $devices
 *
 * @package App\Models
 */
class DeviceType extends Model
{
    protected $table = 'device_types';
    protected $primaryKey = 'dt_id';
    public $timestamps = false;

    protected $fillable = [
        'dt_type',
        'dt_desc'
    ];

    public function devices()
    {
        return $this->hasMany(Device::class, 'device_dt_id');
    }
}
