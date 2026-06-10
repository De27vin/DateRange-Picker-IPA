<?php

namespace Tests;

use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.foreign_key_constraints', true);
        Config::set('app.url', 'http://localhost');
        Config::set('app.allowed_hosts', ['localhost', '127.0.0.1', 'ucp.serv24.com']);
        Config::set('cache.default', 'array');
        Config::set('cache.limiter', 'array');
        Config::set('session.driver', 'array');
        Config::set('queue.default', 'sync');
        $this->app->forgetInstance('cache');
        $this->app->forgetInstance('cache.store');
        $this->app->forgetInstance(\Illuminate\Cache\RateLimiter::class);
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

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $this->withSession([
            'account' => ['id' => 1, 'slug' => 'test-account'],
        ]);
    }

    protected function resetTimeseriesPointsTable(): void
    {
        Schema::dropIfExists('timeseries');
        Schema::create('timeseries', function (Blueprint $table): void {
            $table->unsignedInteger('ts_account_id');
            $table->timestamp('ts_timestamp');
            $table->json('ts_data');
            $table->primary(['ts_account_id', 'ts_timestamp']);
        });
    }

    protected function seedHourlyChartData(
        string $chart,
        string $startUtc,
        string $endUtc,
        ?callable $valueResolver = null,
    ): void {
        $start = CarbonImmutable::parse($startUtc, 'UTC')->utc()->startOfHour();
        $end = CarbonImmutable::parse($endUtc, 'UTC')->utc()->startOfHour();

        $rows = [];
        for ($ts = $start, $index = 0; $ts->lte($end); $ts = $ts->addHour(), $index++) {
            $snapshot = $this->baseTimeseriesSnapshot();
            $resolved = $valueResolver ? $valueResolver($ts, $index) : ($index % 101);
            $this->applyChartSeed($snapshot, $chart, $resolved);

            $snapshot['devices']['disabled'] = 100 - $snapshot['devices']['enabled'];
            $snapshot['alarms']['active_alarms'] = 100 - $snapshot['alarms']['inbound_calls'];
            $snapshot['service_level']['local_checks'] = 100 - $snapshot['service_level']['periodical_calls'];

            $rows[] = [
                'ts_account_id' => 1,
                'ts_timestamp' => $ts->toDateTimeString(),
                'ts_data' => json_encode($snapshot, JSON_THROW_ON_ERROR),
            ];

            if (count($rows) >= 1000) {
                DB::table('timeseries')->upsert($rows, ['ts_account_id', 'ts_timestamp'], ['ts_data']);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('timeseries')->upsert($rows, ['ts_account_id', 'ts_timestamp'], ['ts_data']);
        }
    }

    private function baseTimeseriesSnapshot(): array
    {
        return [
            'devices' => [
                'enabled' => 0,
                'disabled' => 100,
            ],
            'alarms' => [
                'inbound_calls' => 0,
                'active_alarms' => 100,
            ],
            'alerts' => [
                'alert_type' => [
                    'active_alarm' => 0,
                    'battery_malfunction' => 0,
                    'battery_low' => 0,
                    'button_malfunction' => 0,
                    'charge_malfunction' => 0,
                    'database_malfunction' => 0,
                    'disk_low' => 0,
                    'object_door_failure' => 0,
                    'elevator_failure' => 0,
                    'gateway_malfunction' => 0,
                    'identity_mismatch' => 0,
                    'line_alarm' => 0,
                    'object_is_under_maintenance' => 0,
                    'microphone_malfunction' => 0,
                    'network_malfunction' => 0,
                    'periodical_call_overdue' => 0,
                    'pin_mismatch' => 0,
                    'power_malfunction' => 0,
                    'ram_low' => 0,
                    'reserved_device' => 0,
                    'serial_port_malfunction' => 0,
                    'shaft_failure' => 0,
                    'low_signal' => 0,
                    'sip_registration_failure' => 0,
                    'speaker_malfunction' => 0,
                    'technician_check_overdue' => 0,
                    'voice_alarm' => 0,
                ],
            ],
            'service_level' => [
                'periodical_calls' => 0,
                'local_checks' => 100,
            ],
        ];
    }

    private function applyChartSeed(array &$snapshot, string $chart, mixed $resolved): void
    {
        if (is_array($resolved)) {
            match ($chart) {
                'EquipmentChart' => $snapshot['devices'] = array_merge($snapshot['devices'], $this->clampSeries($resolved)),
                'AlarmChart' => $snapshot['alarms'] = array_merge($snapshot['alarms'], $this->clampSeries($resolved)),
                'AlertsChart' => $snapshot['alerts']['alert_type'] = array_merge($snapshot['alerts']['alert_type'], $this->clampSeries($resolved)),
                'ServiceLevelChart' => $snapshot['service_level'] = array_merge($snapshot['service_level'], $this->clampSeries($resolved)),
                default => null,
            };

            return;
        }

        $value = max(0, min(100, (int) $resolved));

        match ($chart) {
            'EquipmentChart' => $snapshot['devices']['enabled'] = $value,
            'AlarmChart' => $snapshot['alarms']['inbound_calls'] = $value,
            'AlertsChart' => $snapshot['alerts']['alert_type'] = array_map(static fn (): int => $value, $snapshot['alerts']['alert_type']),
            'ServiceLevelChart' => $snapshot['service_level']['periodical_calls'] = $value,
            default => null,
        };
    }

    private function clampSeries(array $series): array
    {
        $clamped = [];
        foreach ($series as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $clamped[$key] = max(0, min(100, (int) $value));
        }

        return $clamped;
    }
}
