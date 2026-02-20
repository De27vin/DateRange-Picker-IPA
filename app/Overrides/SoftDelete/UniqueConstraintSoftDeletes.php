<?php

namespace App\Overrides\SoftDelete;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Overrides\SoftDelete\UniqueConstraintSoftDeletingScope;
use Illuminate\Support\Carbon;

trait UniqueConstraintSoftDeletes
{
    use SoftDeletes;

    public static function bootSoftDeletes(){
        static::addGlobalScope(new UniqueConstraintSoftDeletingScope);
    }

    public function restore(){
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }
        $this->{$this->getDeletedAtColumn()} = '0000-00-00 00:00:00';
        $this->exists = true;
        $result = $this->save();
        $this->fireModelEvent('restored', false);
        return $result;
    }

    public function trashed()
    {
        return ($this->{$this->getDeletedAtColumn()}) != '0000-00-00 00:00:00';
    }
}
