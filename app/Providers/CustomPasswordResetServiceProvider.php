<?php

namespace App\Providers;

use App\PasswordBroker\CustomPasswordBrokerManager;
use Illuminate\Support\ServiceProvider;

class CustomPasswordResetServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->registerPasswordBrokerManager();
    }

    protected function registerPasswordBrokerManager()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new CustomPasswordBrokerManager($app);
        });
    }

    public function provides()
    {
        return ['auth.password'];
    }
}