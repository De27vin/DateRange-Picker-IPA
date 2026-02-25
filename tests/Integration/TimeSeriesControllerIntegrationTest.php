<?php

namespace Tests\Integration;

use App\Services\DummyTimeseriesGenerator;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class TimeSeriesControllerIntegrationTest extends TestCase {
    public function test_it_uses_container_bound_generator_and_returns_its_payload(): void {
        $fake = new class extends DummyTimeseriesGenerator {
            public int $calls = 0;
            public ?string $chart = null;
            public ?CarbonImmutable $startDay = null;
            public ?CarbonImmutable $endDay = null;

            public function generate(string $chart, CarbonImmutable $startDay, CarbonImmutable $endDayInclusive): array {
                $this->calls++;
                $this->chart = $chart;
                $this->startDay = $startDay;
                $this->endDay = $endDayInclusive;

                return [
                    ['ts' => '2026-01-24T00:00:00+00:00', 'value' => 7],
                    ['ts' => '2026-01-24T01:00:00+00:00', 'value' => 9],
                ];
            }
        };

        $this->app->instance(DummyTimeseriesGenerator::class, $fake);

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');

        $res->assertOk()
            ->assertJsonPath('meta.chart', 'EquipmentChart')
            ->assertJsonPath('meta.start', '2026-01-24')
            ->assertJsonPath('meta.end', '2026-01-24')
            ->assertJsonPath('meta.resolution', '1h')
            ->assertJsonPath('meta.points', 2)
            ->assertJsonPath('data.0.value', 7)
            ->assertJsonPath('data.1.value', 9);

        $this->assertSame(1, $fake->calls);
        $this->assertSame('EquipmentChart', $fake->chart);
        $this->assertSame('2026-01-24 00:00:00', $fake->startDay?->toDateTimeString());
        $this->assertSame('2026-01-24 00:00:00', $fake->endDay?->toDateTimeString());
    }

    public function test_it_normalizes_start_and_end_dates_to_start_of_day_before_generation(): void {
        $fake = new class extends DummyTimeseriesGenerator {
            public ?CarbonImmutable $startDay = null;
            public ?CarbonImmutable $endDay = null;

            public function generate(string $chart, CarbonImmutable $startDay, CarbonImmutable $endDayInclusive): array {
                $this->startDay = $startDay;
                $this->endDay = $endDayInclusive;

                return [];
            }
        };

        $this->app->instance(DummyTimeseriesGenerator::class, $fake);

        $res = $this->getJson('/api/timeseries?chart=AlarmChart&start=2026-02-01&end=2026-02-03');

        $res->assertOk()
            ->assertJsonPath('meta.start', '2026-02-01')
            ->assertJsonPath('meta.end', '2026-02-03')
            ->assertJsonPath('meta.points', 0);

        $this->assertSame(0, $fake->startDay?->hour);
        $this->assertSame(0, $fake->startDay?->minute);
        $this->assertSame(0, $fake->endDay?->hour);
        $this->assertSame(0, $fake->endDay?->minute);
    }

    public function test_validation_failure_prevents_generator_execution(): void {
        $fake = new class extends DummyTimeseriesGenerator {
            public int $calls = 0;

            public function generate(string $chart, CarbonImmutable $startDay, CarbonImmutable $endDayInclusive): array {
                $this->calls++;
                return [];
            }
        };

        $this->app->instance(DummyTimeseriesGenerator::class, $fake);

        $res = $this->getJson('/api/timeseries?chart=InvalidChart&start=2026-01-24&end=2026-01-24');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['chart'],
            ]);

        $this->assertSame(0, $fake->calls);
    }
}
