<?php

namespace App\Overrides\SoftDelete;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UniqueConstraintSoftDeletingScope extends SoftDeletingScope implements Scope
{
    public function apply(Builder $builder, Model $model){
        $builder->where($model->getQualifiedDeletedAtColumn(), '0000-00-00 00:00:00');
    }

    protected function addRestore(Builder $builder)
    {
        $builder->macro('restore', function (Builder $builder) {
            $builder->withTrashed();

            return $builder->update([$builder->getModel()->getDeletedAtColumn() => '0000-00-00 00:00:00']);
        });
    }

    protected function addWithoutTrashed(Builder $builder){
        $builder->macro('withoutTrashed', function (Builder $builder) {
            $model = $builder->getModel();
            $builder->withoutGlobalScope($this)->where($model->getQualifiedDeletedAtColumn(), '0000-00-00 00:00:00');
            return $builder;
        });
    }

    protected function addOnlyTrashed(Builder $builder)
    {
        $builder->macro('onlyTrashed', function (Builder $builder) {
            $model = $builder->getModel();
            $builder->withoutGlobalScope($this)->where($model->getQualifiedDeletedAtColumn(), '!=', '0000-00-00 00:00:00');
            return $builder;
        });
    }
}
