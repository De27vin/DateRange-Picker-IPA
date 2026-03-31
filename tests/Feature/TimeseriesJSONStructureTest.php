<?php

namespace Tests\Feature;

use Tests\SeedsTimeseriesPoints;
use Tests\TestCase;

class TimeseriesJSONStructureTest extends TestCase {
    use SeedsTimeseriesPoints;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetTimeseriesPointsTable();
        $this->seedHourlyChartData('EquipmentChart', '2026-01-24T00:00:00Z', '2026-01-24T23:00:00Z');
        $this->seedHourlyChartData('AlarmChart', '2026-01-24T00:00:00Z', '2026-01-24T23:00:00Z');
    }

    public function test_response_has_expected_json_structure(): void {
        $res = $this->getJSON('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        // Verify both the metadata block and the data points payload
        $res->assertJSONStructure([
            'meta' => ['chart', 'start', 'end', 'resolution', 'points'],
            'data' => [
                '*' => ['ts', 'series'],
            ],
        ]);

        $points = $res->JSON('meta.points');
        $data = $res->JSON('data');

        $this->assertIsInt($points);
        $this->assertCount($points, $data);
        $this->assertContains($res->json('meta.resolution'), ['1h', '6h', '1d', '1w']);
    }

    public function test_values_are_int(): void {
        $res = $this->getJSON('/api/timeseries?chart=AlarmChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        foreach ($res->JSON('data') as $point) {
            $this->assertIsString($point['ts']);
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
                $point['ts']
            );

            $this->assertIsArray($point['series']);
            foreach ($point['series'] as $value) {
                $this->assertIsInt($value);
            }
        }
    }

    public function test_values_are_in_range(): void {
        $res = $this->getJSON('/api/timeseries?chart=AlarmChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        foreach ($res->JSON('data') as $point) {
            foreach ($point['series'] as $value) {
                $this->assertGreaterThanOrEqual(0, $value);
                $this->assertLessThanOrEqual(100, $value);
            }
        }
    }

    public function test_alerts_chart_returns_individual_alert_type_series(): void
    {
        $this->resetTimeseriesPointsTable();
        $this->seedHourlyChartData('AlertsChart', '2026-01-24T00:00:00Z', '2026-01-24T23:00:00Z', static function ($ts, $index): array {
            return [
                'battery_low' => $index,
                'network_malfunction' => $index + 1,
                'low_signal' => $index + 2,
            ];
        });

        $res = $this->getJSON('/api/timeseries?chart=AlertsChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk()
            ->assertJsonPath('data.0.series.battery_low', 0)
            ->assertJsonPath('data.0.series.network_malfunction', 1)
            ->assertJsonPath('data.0.series.low_signal', 2)
            ->assertJsonPath('data.0.series.active_alarm', 0);
    }

    public function test_missing_parameter_returns_422_with_errors(): void {
        $res = $this->getJSON('/api/timeseries');
        $res->assertStatus(422);

        $res->assertJSONStructure([
            'message',
            'errors' => ['chart', 'start', 'end'],
        ]);
    }

    public function test_invalid_chart_returns_422(): void {
        $res = $this->getJSON('/api/timeseries?chart=NonexistentChart&start=2026-01-24&end=2026-01-24');
        $res->assertStatus(422);

        $res->assertJSONStructure([
            'message', 
            'errors' => ['chart']
        ]);
    }

    public function test_end_before_start_returns_422(): void {
        $res = $this->getJSON('/api/timeseries?chart=EquipmentChart&start=2026-01-27&end=2026-01-24');
        $res->assertStatus(422);
        $res->assertJSONStructure([
            'message', 
            'errors' => ['end']
        ]);
    }
}
