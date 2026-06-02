<?php

namespace App\Scopes;

use App\Services\AccountContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DeviceSitesByAccountScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('ds_account_id', '=', app(AccountContext::class)->get());
    }
}
