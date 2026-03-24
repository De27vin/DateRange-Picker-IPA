<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeseriesPoint extends Model
{
    protected $table = 'timeseries_points';

    protected $fillable = [
        'chart',
        'ts_utc',
        'value',
    ];

    protected $casts = [
        'ts_utc' => 'immutable_datetime',
        'value' => 'integer',
    ];
}
