<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait TrimInputs
{
    protected function trimStringsInArrayRecursively(array $input): array
    {
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        return $input;
    }

    protected function trimStringsInCollectionRecursively(Collection $input): Collection
    {
        return $input->map(function ($item) {
            if (is_string($item)) {
                return trim($item);
            } elseif (is_array($item)) {
                return $this->trimStringsInCollectionRecursively(collect($item))->toArray();
            } elseif ($item instanceof Collection) {
                return $this->trimStringsInCollectionRecursively($item);
            }

            return $item;
        });
    }
}