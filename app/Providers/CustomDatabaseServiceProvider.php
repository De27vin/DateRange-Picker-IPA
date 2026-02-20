<?php

namespace App\Providers;

use App\Overrides\MySqlConnector\CustomMySqlConnector;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Support\ServiceProvider;

class CustomDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('db.connector.mysql', function () {
            return new CustomMySqlConnector();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
