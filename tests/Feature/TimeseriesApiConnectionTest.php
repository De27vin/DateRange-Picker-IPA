<?php

namespace Tests\Feature;

use Tests\SeedsTimeseriesPoints;
use Tests\TestCase;

class TimeseriesApiConnectionTest extends TestCase {
    use SeedsTimeseriesPoints;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetTimeseriesPointsTable();
        foreach (['EquipmentChart', 'AlarmChart', 'AlertsChart', 'ServiceLevelChart'] as $chart) {
            $this->seedHourlyChartData($chart, '2025-01-01T00:00:00Z', '2026-12-31T23:00:00Z');
        }
    }

    public function test_all_supported_charts_are_accepted(): void {
        $charts = ['EquipmentChart', 'AlarmChart', 'AlertsChart', 'ServiceLevelChart'];

        foreach ($charts as $chart) {
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
        $equipment = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $equipment->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['series' => ['enabled', 'disabled']],
                ],
            ]);

        $alarm = $this->getJson('/api/timeseries?chart=AlarmChart&start=2026-01-24&end=2026-01-24');
        $alarm->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['series' => ['inbound_calls', 'active_alarms']],
                ],
            ]);

        $service = $this->getJson('/api/timeseries?chart=ServiceLevelChart&start=2026-01-24&end=2026-01-24');
        $service->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['series' => ['periodical_calls', 'local_checks']],
                ],
            ]);

        $alerts = $this->getJson('/api/timeseries?chart=AlertsChart&start=2026-01-24&end=2026-01-24');
        $alerts->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['series' => ['active_alarm', 'battery_low', 'network_malfunction', 'low_signal']],
                ],
            ]);
    }

    public function test_response_data_is_sorted_by_ts_ascending(): void {
        $res = $this->getJson('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-27');
        $res->assertOk();

        $points = $res->json('data');
        $timestamps = array_column($points, 'ts');
        $sorted = $timestamps;
        sort($sorted);

        $this->assertSame($sorted, $timestamps, 'Expected ascending ts order.');
    }

    public function test_range_over_365_days_returns_422(): void {
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
