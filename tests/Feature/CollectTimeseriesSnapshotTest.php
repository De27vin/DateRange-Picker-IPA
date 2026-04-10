<?php

namespace Tests\Feature;

use App\Models\TimeseriesSnapshot;
use App\Services\TimeseriesSnapshotCollector;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CollectTimeseriesSnapshotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedSnapshotFixtures();
    }

    public function test_it_collects_one_snapshot_per_account_per_hour(): void
    {
        $collector = $this->app->make(TimeseriesSnapshotCollector::class);
        $tsUtc = CarbonImmutable::parse('2026-04-07 10:37:21', 'UTC');

        $written = $collector->collectHourlySnapshots($tsUtc);

        $this->assertSame(1, $written);

        $snapshot = TimeseriesSnapshot::query()->where('account_id', 1)->firstOrFail();

        $this->assertSame('2026-04-07 10:00:00', $snapshot->ts_utc->utc()->toDateTimeString());
        $this->assertSame([
            'devices' => [
                'enabled' => 3,
                'disabled' => 1,
            ],
            'alarms' => [
                'inbound_calls' => 1,
                'active_alarms' => 2,
            ],
            'alerts' => [
                'alert_type' => [
                    'ALARM' => 1,
                    'VOICE' => 1,
                    'BATDEF' => 1,
                    'TECH' => 2,
                ],
            ],
            'service_level' => [
                'periodical_calls' => 0,
                'local_checks' => 2,
            ],
        ], $snapshot->data);
    }

    public function test_it_upserts_the_same_hour_instead_of_creating_duplicates(): void
    {
        $collector = $this->app->make(TimeseriesSnapshotCollector::class);
        $tsUtc = CarbonImmutable::parse('2026-04-07 10:05:00', 'UTC');

        $collector->collectHourlySnapshots($tsUtc);
        $collector->collectHourlySnapshots($tsUtc->addMinutes(20));

        $this->assertSame(1, TimeseriesSnapshot::query()->count());
    }

    private function createSchema(): void
    {
        Schema::create('accounts', function (Blueprint $table): void {
            $table->unsignedBigInteger('account_id')->primary();
            $table->string('account_name');
            $table->string('account_slug');
            $table->json('account_translation')->nullable();
            $table->integer('account_enabled')->default(1);
            $table->timestamp('account_created')->nullable();
            $table->timestamp('account_modified')->nullable();
        });

        Schema::create('device_sites', function (Blueprint $table): void {
            $table->unsignedBigInteger('ds_id')->primary();
            $table->unsignedBigInteger('ds_account_id');
            $table->unsignedBigInteger('ds_protocol_id')->nullable();
            $table->timestamp('ds_deleted')->nullable();
        });

        Schema::create('module_types', function (Blueprint $table): void {
            $table->unsignedBigInteger('mt_id')->primary();
            $table->string('mt_type');
        });

        Schema::create('modules', function (Blueprint $table): void {
            $table->unsignedBigInteger('module_id')->primary();
            $table->unsignedBigInteger('module_mt_id')->nullable();
            $table->string('module_name')->nullable();
        });

        Schema::create('devices', function (Blueprint $table): void {
            $table->unsignedBigInteger('device_id')->primary();
            $table->unsignedBigInteger('device_ds_id');
            $table->unsignedBigInteger('device_account_id');
            $table->unsignedBigInteger('device_module_id')->nullable();
            $table->boolean('device_enabled')->default(true);
            $table->timestamp('device_deleted')->nullable();
        });

        Schema::create('alert_types', function (Blueprint $table): void {
            $table->unsignedBigInteger('at_id')->primary();
            $table->string('at_type');
        });

        Schema::create('device_alerts', function (Blueprint $table): void {
            $table->unsignedBigInteger('da_id')->primary();
            $table->unsignedBigInteger('da_device_id');
            $table->unsignedBigInteger('da_at_id');
            $table->timestamp('da_timestamp')->nullable();
        });

        Schema::create('timeseries_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->timestamp('ts_utc');
            $table->json('data');
            $table->timestamps();

            $table->unique(['account_id', 'ts_utc']);
        });
    }

    private function seedSnapshotFixtures(): void
    {
        $profile = [
            'config' => [
                'alert' => [
                    'display' => [
                        'ALARM' => true,
                        'VOICE' => true,
                        'BATDEF' => true,
                        'TECH' => true,
                    ],
                    'critical' => [
                        'ALARM' => true,
                        'BATDEF' => true,
                    ],
                    'alarm' => [
                        'VOICE' => true,
                    ],
                ],
            ],
        ];

        \DB::table('accounts')->insert([
            'account_id' => 1,
            'account_name' => 'Account A',
            'account_slug' => 'account-a',
            'account_translation' => json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'account_enabled' => 1,
        ]);

        \DB::table('device_sites')->insert([
            ['ds_id' => 10, 'ds_account_id' => 1, 'ds_protocol_id' => 1],
        ]);

        \DB::table('module_types')->insert([
            ['mt_id' => 1, 'mt_type' => 'GATEWAY'],
            ['mt_id' => 2, 'mt_type' => 'TELEALARM'],
        ]);

        \DB::table('modules')->insert([
            ['module_id' => 100, 'module_mt_id' => 1, 'module_name' => 'Gateway'],
            ['module_id' => 101, 'module_mt_id' => 2, 'module_name' => 'Device'],
        ]);

        \DB::table('devices')->insert([
            ['device_id' => 1000, 'device_ds_id' => 10, 'device_account_id' => 1, 'device_module_id' => 100, 'device_enabled' => 1],
            ['device_id' => 1001, 'device_ds_id' => 10, 'device_account_id' => 1, 'device_module_id' => 101, 'device_enabled' => 1],
            ['device_id' => 1002, 'device_ds_id' => 10, 'device_account_id' => 1, 'device_module_id' => 101, 'device_enabled' => 1],
            ['device_id' => 1003, 'device_ds_id' => 10, 'device_account_id' => 1, 'device_module_id' => 101, 'device_enabled' => 0],
        ]);

        \DB::table('alert_types')->insert([
            ['at_id' => 1, 'at_type' => 'ALARM'],
            ['at_id' => 2, 'at_type' => 'VOICE'],
            ['at_id' => 3, 'at_type' => 'BATDEF'],
            ['at_id' => 4, 'at_type' => 'TECH'],
        ]);

        \DB::table('device_alerts')->insert([
            ['da_id' => 1, 'da_device_id' => 1001, 'da_at_id' => 1],
            ['da_id' => 2, 'da_device_id' => 1002, 'da_at_id' => 2],
            ['da_id' => 3, 'da_device_id' => 1001, 'da_at_id' => 3],
            ['da_id' => 4, 'da_device_id' => 1000, 'da_at_id' => 4],
        ]);
    }
}
