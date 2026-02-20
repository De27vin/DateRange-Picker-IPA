<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceSiteSetting
 *
 * @property int $dss_id
 * @property int $dss_ds_id
 * @property int $dss_setting_id
 * @property string $dss_value
 *
 * @property DeviceSite $device_site
 * @property Setting $setting
 *
 * @package App\Models
 */
class DeviceSiteSetting extends Model
{
    protected $table = 'device_site_settings';
    protected $primaryKey = 'dss_id';
    public $timestamps = false;

    protected $casts = [
        'dss_ds_id' => 'int',
        'dss_setting_id' => 'int'
    ];

    protected $fillable = [
        'dss_ds_id',
        'dss_setting_id',
        'dss_value'
    ];

    public function device_site()
    {
        return $this->belongsTo(DeviceSite::class, 'dss_ds_id');
    }

    public function setting()
    {
        return $this->belongsTo(Setting::class, 'dss_setting_id');
    }
}
