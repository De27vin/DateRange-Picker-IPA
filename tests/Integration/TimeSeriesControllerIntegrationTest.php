<?php

namespace Tests\Integration;

use App\Services\DatabaseTimeseriesLoader;
use App\Services\TimeseriesDataService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class TimeSeriesControllerIntegrationTest extends TestCase {
    public function test_it_uses_container_bound_loader_and_returns_its_payload(): void {
        $fake = new class extends DatabaseTimeseriesLoader {
            public int $calls = 0;
            public ?string $chart = null;
            public ?CarbonImmutable $startUtc = null;
            public ?CarbonImmutable $endUtc = null;

            public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array {
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

    public function test_it_passes_hour_normalized_utc_range_to_loader(): void {
        $fake = new class extends DatabaseTimeseriesLoader {
            public ?CarbonImmutable $startUtc = null;
            public ?CarbonImmutable $endUtc = null;

            public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array {
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

    public function test_validation_failure_prevents_loader_execution(): void {
        $fake = new class extends DatabaseTimeseriesLoader {
            public int $calls = 0;

            public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array {
                $this->calls++;
                return [];
            }
        };

        $this->app->forgetInstance(TimeseriesDataService::class);
        $this->app->instance(DatabaseTimeseriesLoader::class, $fake);

        $res = $this->getJson('/api/timeseries?chart=InvalidChart&start=2026-01-24&end=2026-01-24');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['chart'],
            ]);

        $this->assertSame(0, $fake->calls);
    }
}
