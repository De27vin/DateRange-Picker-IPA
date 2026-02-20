<?php

namespace App\Traits\Validation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * @mixin Model
 * @mixin Component
 * @method validate(array $rules, array $messages = [], array $attributes = [])
 * @method notify(string $type, string $message)
 */
trait ValidatesWithNotifies
{
    protected function validateWithNotify($rules, $messages = [], $attributes = [])
    {
        try {
            return $this->validate($rules, $messages, $attributes);
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $error) {
                $this->notify('error', $error);
            }
            throw $e;
        }
    }
}