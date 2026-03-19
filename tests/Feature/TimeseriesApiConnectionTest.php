<?php

namespace Tests\Feature;

use Tests\TestCase;

class TimeseriesApiConnectionTest extends TestCase {
    public function test_all_supported_charts_are_accepted(): void {
        $charts = ['EquipmentChart', 'AlarmChart', 'AlertsChart', 'ServiceLevelChart'];

        // All configured chart names should use the same endpoint successfully
        foreach ($charts as $chart) {
            $res = $this->getJson("/api/timeseries?chart={$chart}&start=2026-01-24&end=2026-01-24");

            $res->assertOk()
                ->assertJsonPath('meta.chart', $chart)
                ->assertJsonPath('meta.resolution', '1h')
                ->assertJsonPath('meta.points', 24)
                ->assertJsonCount(24, 'data');
        }
    }

    public function test_response_data_is_sorted_by_ts_ascending(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-27');
        $res->assertOk();

        $points = $res->json('data');
        // Compare original order against a separately sorted copy
        $timestamps = array_column($points, 'ts');
        $sorted = $timestamps;
        sort($sorted);

        $this->assertSame($sorted, $timestamps, 'Expected ascending ts order.');
    }

    public function test_range_over_365_days_returns_422(): void {
        // 366 inclusive days
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2025-01-01&end=2026-01-01');

        $res->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['end'],
            ]);
    }

    public function test_range_of_exactly_365_days_is_allowed(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2025-01-01&end=2025-12-31');

        $res->assertOk()
            ->assertJsonPath('meta.resolution', '1w')
            ->assertJsonPath('meta.points', 53)
            ->assertJsonCount(53, 'data');
    }
}
