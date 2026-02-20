<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldConfig extends Model
{
    protected $table = 'custom_field_configs';
    protected $primaryKey = 'cfc_id';
    public $timestamps = false;

    protected $fillable = [
        'cfc_name',
        'cfc_account_id',
        'cfc_protocol_id',
        'cfc_is_device',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'cfc_account_id', 'account_id');
    }

    public function protocol()
    {
        return $this->belongsTo(Module::class, 'cfc_protocol_id', 'module_id');
    }
}
