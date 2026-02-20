<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceFilter extends Model
{
    use \Sushi\Sushi;

    protected $rows = [
        [
            'type'   => 'enabled',
            'name' => 'device_enabled',
            'description' => 'enabled devices',
        ],
        [
            'type'   => 'disabled',
            'name' => 'device_disabled',
            'description' => 'disabled devices',
        ],
        [
            'type'   => 'deleted',
            'name' => 'device_deleted',
            'description' => 'deleted devices',
        ],
        [
            'type'   => 'warning',
            'name' => 'device_has_warning',
            'description' => 'warning devices',
        ],
        [
            'type'   => 'error',
            'name' => 'device_has_error',
            'description' => 'error devices',
        ],
        [
            'type'   => 'overdue',
            'name' => 'overdue',
            'description' => 'overdue devices',
        ],
    ];
}