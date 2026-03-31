<?php

namespace Tests\Feature;

use Tests\SeedsTimeseriesPoints;
use Tests\TestCase;

class TimeseriesTest extends TestCase {
    use SeedsTimeseriesPoints;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetTimeseriesPointsTable();
        $this->seedHourlyChartData('EquipmentChart', '2025-01-01T00:00:00Z', '2026-12-31T23:00:00Z');
    }
    
    public function test_valid_request_returns_24_points_for_single_day(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk()
            ->assertJsonPath('meta.points', 24)
            ->assertJsonCount(24, 'data');
    }

    public function test_valid_request_returns_16_points_for_4_days_inclusive(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-27');
        $res->assertOk()
            ->assertJsonPath('meta.resolution', '6h')
            ->assertJsonPath('meta.points', 16)
            ->assertJsonCount(16, 'data');
    }

    public function test_values_are_int_between_0_and_100(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        $data = $res->json('data');
        foreach ($data as $point) {
            $this->assertIsArray($point['series']);
            foreach ($point['series'] as $value) {
                $this->assertIsInt($value);
                $this->assertGreaterThanOrEqual(0, $value);
                $this->assertLessThanOrEqual(100, $value);
            }
        }
    }

    // General tests for request validation, not specific
    public function test_invalid_chart_is_422(): void {
        $res = $this->getJson('/api/timeseries?chart=NonexistantChart&start=2026-01-24&end=2026-01-24');
        $res->assertStatus(422);
    }

    public function test_end_before_start_is_422(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-27&end=2026-01-24');
        $res->assertStatus(422);
    }
}
