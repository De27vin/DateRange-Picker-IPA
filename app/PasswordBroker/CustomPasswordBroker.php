<?php

namespace App\PasswordBroker;

use Closure;
use Illuminate\Auth\Passwords\PasswordBroker as BasePasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class CustomPasswordBroker extends BasePasswordBroker
{
    public function sendResetLink(array $credentials, Closure $callback = null)
    {
        if (!array_key_exists('email', $credentials)) {
            return static::INVALID_USER;
        }

        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if ($this->tokens->recentlyCreatedTokenByEmail($credentials['email'])) {
            return static::RESET_THROTTLED;
        }

        $token = $this->tokens->createFromEmail($credentials['email']);

        if ($callback) {
            $callback($user, $token);
        } else {
            $user->setEmailForPasswordReset($credentials['email']);
            $user->sendPasswordResetNotification($token);
        }

        return static::RESET_LINK_SENT;
    }

    public function reset(array $credentials, Closure $callback)
    {
        $user = $this->validateReset($credentials);

        if (! $user instanceof CanResetPasswordContract) {
            return $user;
        }

        $password = $credentials['password'];

        $callback($user, $password);

        $this->tokens->deleteExistingByEmail($credentials['email']);

        return static::PASSWORD_RESET;
    }

    protected function validateReset(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return static::INVALID_USER;
        }

        if (! $this->tokens->existsByEmail($credentials['email'], $credentials['token'])) {
            return static::INVALID_TOKEN;
        }

        return $user;
    }
}
