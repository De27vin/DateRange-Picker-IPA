<?php
namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;


class CustomUserProvider extends EloquentUserProvider { 

    private $method_to_email_model;

    public function __construct(HasherContract $hasher, $model, $method_to_email_model)
    {

        parent::__construct($hasher, $model);

        $this->method_to_email_model = $method_to_email_model;
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) || (count($credentials) === 1 && array_key_exists('password', $credentials))) {
            return;
        }

        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'email')) {
                $query->orWhereHas($this->method_to_email_model, function ($q) use ($value) {
                    $q->where('email_address', $value);
                });
            }
        }
        return $query->first();
    } 

}