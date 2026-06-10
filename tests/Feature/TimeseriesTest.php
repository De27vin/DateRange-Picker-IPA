<?php

namespace Tests\Feature;

use App\Services\DatabaseTimeseriesLoader;
use App\Services\TimeseriesDataService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class TimeseriesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetTimeseriesPointsTable();
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_valid_request_returns_24_points_for_single_day(): void
    {
        $this->seedHourlyChartData('EquipmentChart', '2026-01-24T00:00:00Z', '2026-01-24T23:00:00Z');

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');

        $res->assertOk()
            ->assertJsonPath('meta.points', 24)
            ->assertJsonCount(24, 'data');
    }

    public function test_valid_request_returns_16_points_for_4_days_inclusive(): void
    {
        $this->seedHourlyChartData('EquipmentChart', '2026-01-24T00:00:00Z', '2026-01-27T23:00:00Z');

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-27');

        $res->assertOk()
            ->assertJsonPath('meta.resolution', '6h')
            ->assertJsonPath('meta.points', 16)
            ->assertJsonCount(16, 'data');
    }

    public function test_values_are_int_between_0_and_100(): void
    {
        $this->seedHourlyChartData('EquipmentChart', '2026-01-24T00:00:00Z', '2026-01-24T23:00:00Z');

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        foreach ($res->json('data') as $point) {
            $this->assertIsArray($point['series']);
            foreach ($point['series'] as $value) {
                $this->assertIsInt($value);
                $this->assertGreaterThanOrEqual(0, $value);
                $this->assertLessThanOrEqual(100, $value);
            }
        }
    }

    public function test_all_supported_charts_are_accepted(): void
    {
        $this->seedSupportedCharts();

        foreach (['EquipmentChart', 'AlarmChart', 'AlertsChart', 'ServiceLevelChart'] as $chart) {
            $res = $this->getJson("/api/timeseries?chart={$chart}&start=2026-01-24&end=2026-01-24");

            $res->assertOk()
                ->assertJsonPath('meta.chart', $chart)
                ->assertJsonPath('meta.resolution', '1h')
                ->assertJsonPath('meta.points', 24)
                ->assertJsonCount(24, 'data');
        }
    }

    public function test_chart_response_contains_expected_series_keys(): void
    {
        $this->seedSupportedCharts();

        $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24')
            ->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['series' => ['enabled', 'disabled']]]]);

        $this->getJson('/api/timeseries?chart=AlarmChart&start=2026-01-24&end=2026-01-24')
            ->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['series' => ['inbound_calls', 'active_alarms']]]]);

        $this->getJson('/api/timeseries?chart=ServiceLevelChart&start=2026-01-24&end=2026-01-24')
            ->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['series' => ['periodical_calls', 'local_checks']]]]);

        $this->getJson('/api/timeseries?chart=AlertsChart&start=2026-01-24&end=2026-01-24')
            ->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['series' => ['active_alarm', 'battery_low', 'network_malfunction', 'low_signal']]]]);
    }

    public function test_response_data_is_sorted_by_ts_ascending(): void
    {
        $this->seedHourlyChartData('EquipmentChart', '2026-01-24T00:00:00Z', '2026-01-27T23:00:00Z');

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-27');
        $res->assertOk();

        $timestamps = array_column($res->json('data'), 'ts');
        $sorted = $timestamps;
        sort($sorted);

        $this->assertSame($sorted, $timestamps, 'Expected ascending ts order.');
    }

    public function test_range_of_exactly_365_days_is_allowed(): void
    {
        $this->seedHourlyChartData('EquipmentChart', '2025-01-01T00:00:00Z', '2025-12-31T23:00:00Z');

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2025-01-01&end=2025-12-31');

        $res->assertOk()
            ->assertJsonPath('meta.resolution', '1w')
            ->assertJsonPath('meta.points', 53)
            ->assertJsonCount(53, 'data');
    }

    public function test_response_has_expected_json_structure(): void
    {
        $this->seedHourlyChartData('EquipmentChart', '2026-01-24T00:00:00Z', '2026-01-24T23:00:00Z');

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk()
            ->assertJsonStructure([
                'meta' => ['chart', 'start', 'end', 'resolution', 'points'],
                'data' => [
                    '*' => ['ts', 'series'],
                ],
            ]);

        $this->assertIsInt($res->json('meta.points'));
        $this->assertCount($res->json('meta.points'), $res->json('data'));
        $this->assertContains($res->json('meta.resolution'), ['1h', '6h', '1d', '1w']);
    }

    public function test_response_values_are_typed_and_in_range(): void
    {
        $this->seedHourlyChartData('AlarmChart', '2026-01-24T00:00:00Z', '2026-01-24T23:00:00Z');

        $res = $this->getJson('/api/timeseries?chart=AlarmChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        foreach ($res->json('data') as $point) {
            $this->assertIsString($point['ts']);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $point['ts']);
            $this->assertIsArray($point['series']);

            foreach ($point['series'] as $value) {
                $this->assertIsInt($value);
                $this->assertGreaterThanOrEqual(0, $value);
                $this->assertLessThanOrEqual(100, $value);
            }
        }
    }

    public function test_alerts_chart_returns_individual_alert_type_series(): void
    {
        $this->seedHourlyChartData('AlertsChart', '2026-01-24T00:00:00Z', '2026-01-24T23:00:00Z', static function ($ts, $index): array {
            return [
                'battery_low' => $index,
                'network_malfunction' => $index + 1,
                'low_signal' => $index + 2,
            ];
        });

        $this->getJson('/api/timeseries?chart=AlertsChart&start=2026-01-24&end=2026-01-24')
            ->assertOk()
            ->assertJsonPath('data.0.series.battery_low', 0)
            ->assertJsonPath('data.0.series.network_malfunction', 1)
            ->assertJsonPath('data.0.series.low_signal', 2)
            ->assertJsonPath('data.0.series.active_alarm', 0);
    }

    public function test_missing_parameter_returns_422_with_errors(): void
    {
        $this->getJson('/api/timeseries')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['chart', 'start', 'end'],
            ]);
    }

    public function test_missing_start_returns_422(): void
    {
        $this->getJson('/api/timeseries?chart=EquipmentChart&end=2026-01-24T23:59:59Z')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['start'],
            ]);
    }

    public function test_missing_end_returns_422(): void
    {
        $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24T00:00:00Z')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }

    public function test_invalid_chart_returns_422(): void
    {
        $this->getJson('/api/timeseries?chart=InvalidChart&start=2026-01-24T00:00:00Z&end=2026-01-24T23:59:59Z')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['chart'],
            ]);
    }

    public function test_end_before_start_returns_422(): void
    {
        $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-25T00:00:00Z&end=2026-01-24T23:59:59Z')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }

    public function test_range_over_365_days_returns_422(): void
    {
        $this->getJson('/api/timeseries?chart=EquipmentChart&start=2025-01-01T00:00:00Z&end=2026-01-01T00:00:01Z')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }

    public function test_365_day_timestamp_range_returns_200(): void
    {
        $this->seedHourlyChartData('EquipmentChart', '2025-01-01T00:00:00Z', '2026-01-01T00:00:00Z');

        $this->getJson('/api/timeseries?chart=EquipmentChart&start=2025-01-01T00:00:00Z&end=2026-01-01T00:00:00Z')
            ->assertOk()
            ->assertJsonStructure([
                'meta' => ['chart', 'start', 'end', 'resolution', 'points'],
                'data',
            ]);
    }

    public function test_today_end_is_allowed_and_returns_200(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-02-26T08:39:12Z'));
        $this->seedHourlyChartData('EquipmentChart', '2026-02-26T00:00:00Z', '2026-02-26T08:00:00Z');

        $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-02-26T00:00:00Z&end=2026-02-26T23:00:00Z')
            ->assertOk()
            ->assertJsonPath('meta.end', '2026-02-26T08:00:00+00:00');
    }

    public function test_future_end_is_clamped_to_now_floor_hour_utc(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-02-26T08:39:12Z'));
        $this->seedHourlyChartData('EquipmentChart', '2026-02-26T00:00:00Z', '2026-02-26T08:00:00Z');

        $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-02-26T00:00:00Z&end=2026-02-27T12:00:00Z')
            ->assertOk()
            ->assertJsonPath('meta.end', '2026-02-26T08:00:00+00:00');
    }

    public function test_end_before_start_after_clamping_returns_422(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-02-26T08:39:12Z'));

        $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-02-26T09:00:00Z&end=2026-02-26T23:00:00Z')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }

    public function test_it_uses_container_bound_loader_and_returns_its_payload(): void
    {
        $fake = new class extends DatabaseTimeseriesLoader {
            public int $calls = 0;
            public ?string $chart = null;
            public ?CarbonImmutable $startUtc = null;
            public ?CarbonImmutable $endUtc = null;

            public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
            {
                $this->calls++;
                $this->chart = $chart;
                $this->startUtc = $startUtc;
                $this->endUtc = $endUtc;

                return [
                    ['ts' => '2026-01-24T00:00:00+00:00', 'series' => ['enabled' => 7, 'disabled' => 93]],
                    ['ts' => '2026-01-24T01:00:00+00:00', 'series' => ['enabled' => 9, 'disabled' => 91]],
                ];
            }
        };

        $this->app->forgetInstance(TimeseriesDataService::class);
        $this->app->instance(DatabaseTimeseriesLoader::class, $fake);

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');

        $res->assertOk()
            ->assertJsonPath('meta.chart', 'EquipmentChart')
            ->assertJsonPath('meta.start', '2026-01-24T00:00:00+00:00')
            ->assertJsonPath('meta.end', '2026-01-24T23:00:00+00:00')
            ->assertJsonPath('meta.resolution', '1h')
            ->assertJsonPath('meta.points', 2)
            ->assertJsonPath('data.0.series.enabled', 7)
            ->assertJsonPath('data.0.series.disabled', 93)
            ->assertJsonPath('data.1.series.enabled', 9)
            ->assertJsonPath('data.1.series.disabled', 91);

        $this->assertSame(1, $fake->calls);
        $this->assertSame('EquipmentChart', $fake->chart);
        $this->assertSame('2026-01-24 00:00:00', $fake->startUtc?->toDateTimeString());
        $this->assertSame('2026-01-24 23:00:00', $fake->endUtc?->toDateTimeString());
    }

    public function test_it_passes_hour_normalized_utc_range_to_loader(): void
    {
        $fake = new class extends DatabaseTimeseriesLoader {
            public ?CarbonImmutable $startUtc = null;
            public ?CarbonImmutable $endUtc = null;

            public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
            {
                $this->startUtc = $startUtc;
                $this->endUtc = $endUtc;

                return [];
            }
        };

        $this->app->forgetInstance(TimeseriesDataService::class);
        $this->app->instance(DatabaseTimeseriesLoader::class, $fake);

        $res = $this->getJson('/api/timeseries?chart=AlarmChart&start=2026-02-01&end=2026-02-03');

        $res->assertOk()
            ->assertJsonPath('meta.start', '2026-02-01T00:00:00+00:00')
            ->assertJsonPath('meta.end', '2026-02-03T23:00:00+00:00')
            ->assertJsonPath('meta.points', 0);

        $this->assertSame(0, $fake->startUtc?->hour);
        $this->assertSame(0, $fake->startUtc?->minute);
        $this->assertSame(23, $fake->endUtc?->hour);
        $this->assertSame(0, $fake->endUtc?->minute);
    }

    public function test_validation_failure_prevents_loader_execution(): void
    {
        $fake = new class extends DatabaseTimeseriesLoader {
            public int $calls = 0;

            public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
            {
                $this->calls++;
                return [];
            }
        };

        $this->app->forgetInstance(TimeseriesDataService::class);
        $this->app->instance(DatabaseTimeseriesLoader::class, $fake);

        $this->getJson('/api/timeseries?chart=InvalidChart&start=2026-01-24&end=2026-01-24')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['chart'],
            ]);

        $this->assertSame(0, $fake->calls);
    }

    private function seedSupportedCharts(
        string $start = '2026-01-24T00:00:00Z',
        string $end = '2026-01-24T23:00:00Z'
    ): void {
        foreach (['EquipmentChart', 'AlarmChart', 'AlertsChart', 'ServiceLevelChart'] as $chart) {
            $this->seedHourlyChartData($chart, $start, $end);
        }
    }
}
