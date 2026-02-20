<?php

namespace Tests\Feature;

use Tests\TestCase;

class TimeseriesTest extends TestCase {
    
    public function test_valid_request_returns_24_points_for_single_day(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk()
            ->assertJsonPath('meta.points', 24)
            ->assertJsonCount(24, 'data');
    }

    public function test_valid_request_returns_96_points_for_4_days_inclusive(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-27');
        $res->assertOk()
            ->assertJsonPath('meta.points', 96)
            ->assertJsonCount(96, 'data');
    }

    public function test_values_are_int_between_0_and_100(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        $data = $res->json('data');
        foreach ($data as $point) {
            $this->assertIsInt($point['value']);
            $this->assertGreaterThanOrEqual(0, $point['value']);
            $this->assertLessThanOrEqual(100, $point['value']);
        }
    }

    public function test_invalid_chart_is_422(): void {
        $res = $this->getJson('/api/timeseries?chart=NonexistantChart&start=2026-01-24&end=2026-01-24');
        $res->assertStatus(422);
    }

    public function test_end_before_start_is_422(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-27&end=2026-01-24');
        $res->assertStatus(422);
    }
}