<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Tests\SeedsTimeseriesPoints;
use Tests\TestCase;

class TimeseriesValidationTest extends TestCase
{
    use SeedsTimeseriesPoints;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetTimeseriesPointsTable();
        $this->seedHourlyChartData('EquipmentChart', '2025-01-01T00:00:00Z', '2026-12-31T23:00:00Z');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_missing_start_returns_422(): void
    {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&end=2026-01-24T23:59:59Z');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['start'],
            ]);
    }

    public function test_missing_end_returns_422(): void
    {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24T00:00:00Z');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }

    public function test_end_before_start_returns_422(): void
    {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-25T00:00:00Z&end=2026-01-24T23:59:59Z');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }

    public function test_range_over_365_days_returns_422(): void
    {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2025-01-01T00:00:00Z&end=2026-01-01T00:00:01Z');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }

    public function test_range_of_exactly_365_days_returns_200(): void
    {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2025-01-01T00:00:00Z&end=2026-01-01T00:00:00Z');

        $res->assertOk()
            ->assertJsonStructure([
                'meta' => ['chart', 'start', 'end', 'resolution', 'points'],
                'data',
            ]);
    }

    public function test_invalid_chart_returns_422(): void
    {
        $res = $this->getJson('/api/timeseries?chart=InvalidChart&start=2026-01-24T00:00:00Z&end=2026-01-24T23:59:59Z');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['chart'],
            ]);
    }

    public function test_today_end_is_allowed_and_returns_200(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-02-26T08:39:12Z'));

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-02-26T00:00:00Z&end=2026-02-26T23:00:00Z');

        $res->assertOk()
            ->assertJsonPath('meta.end', '2026-02-26T08:00:00+00:00');
    }

    public function test_future_end_is_clamped_to_now_floor_hour_utc(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-02-26T08:39:12Z'));

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-02-26T00:00:00Z&end=2026-02-27T12:00:00Z');

        $res->assertOk()
            ->assertJsonPath('meta.end', '2026-02-26T08:00:00+00:00');
    }

    public function test_end_before_start_after_clamping_returns_422(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-02-26T08:39:12Z'));

        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-02-26T09:00:00Z&end=2026-02-26T23:00:00Z');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }
}
