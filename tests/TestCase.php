<?php

namespace Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.foreign_key_constraints', true);
        Config::set('app.allowed_hosts', ['localhost', '127.0.0.1']);
        Config::set('cache.default', 'array');
        Config::set('session.driver', 'array');
        Config::set('queue.default', 'sync');
        $this->app->forgetInstance('cache');
        $this->app->forgetInstance('cache.store');
        $this->app->forgetInstance('session');

        Config::set('logging.default', 'null');
        Config::set('logging.channels.stack', [
            'driver' => 'stack',
            'channels' => ['null'],
            'ignore_exceptions' => false,
        ]);
        Config::set('logging.channels.single', [
            'driver' => 'monolog',
            'handler' => \Monolog\Handler\NullHandler::class,
        ]);
        Config::set('logging.channels.daily', [
            'driver' => 'monolog',
            'handler' => \Monolog\Handler\NullHandler::class,
        ]);
        Config::set('logging.channels.ipa', [
            'driver' => 'monolog',
            'handler' => \Monolog\Handler\NullHandler::class,
        ]);
        Config::set('logging.channels.proxy', [
            'driver' => 'monolog',
            'handler' => \Monolog\Handler\NullHandler::class,
        ]);
        Config::set('logging.channels.emergency', [
            'driver' => 'monolog',
            'handler' => \Monolog\Handler\NullHandler::class,
        ]);
    }
}
