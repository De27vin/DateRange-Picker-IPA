<?php

namespace App\Http\Requests\Traits;

trait TrimStrings
{
    protected function trimStrings(array $input): array
    {
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        return $input;
    }
}