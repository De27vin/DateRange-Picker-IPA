<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeseriesSnapshot extends Model
{
    protected $table = 'timeseries';
    protected $primaryKey = 'ts_timestamp';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ts_account_id',
        'ts_timestamp',
        'ts_data',
    ];

    protected $casts = [
        'ts_account_id' => 'integer',
        'ts_timestamp' => 'immutable_datetime',
        'ts_data' => 'array',
    ];
}
