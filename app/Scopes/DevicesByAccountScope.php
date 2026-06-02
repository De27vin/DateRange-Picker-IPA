<?php

namespace App\Scopes;

use App\Services\AccountContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DevicesByAccountScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $accountId = app(AccountContext::class)->get();

        $builder->where('device_account_id', '=', $accountId);
        $builder->whereHas('device_site', function ($query) use ($accountId) {
            $query->where('device_sites.ds_account_id', '=', $accountId);
        });
    }
}
