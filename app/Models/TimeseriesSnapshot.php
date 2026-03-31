<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeseriesSnapshot extends Model
{
    protected $table = 'timeseries_snapshots';

    protected $fillable = [
        'account_id',
        'ts_utc',
        'data',
    ];

    protected $casts = [
        'account_id' => 'integer',
        'ts_utc' => 'immutable_datetime',
        'data' => 'array',
    ];
}
