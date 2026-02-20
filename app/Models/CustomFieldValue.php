<?php

namespace App\Models;

use App\Searchable\Searchable;
use App\Searchable\SearchResult;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model implements Searchable
{
    protected $table = 'custom_field_values';
    protected $primaryKey = 'cfv_id';
    public $timestamps = false;

    protected $fillable = [
        'cfv_cfc_id',
        'cfv_device_id',
        'cfv_ds_id',
        'cfv_value',
    ];

    public function config()
    {
        return $this->belongsTo(CustomFieldConfig::class, 'cfv_cfc_id', 'cfc_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'cfv_device_id', 'device_id');
    }

    public function deviceSite()
    {
        return $this->belongsTo(DeviceSite::class, 'cfv_ds_id', 'ds_id');
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->cfv_value
        );
    }
}
