<?php

namespace Tests\Feature;

use Tests\TestCase;

class TimeseriesJSONStructureTest extends TestCase {
    public function test_response_has_expected_json_structure(): void {
        $res = $this->getJSON('/api/timeseries?chart=EquipmentChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        // Verify both the metadata block and the data points payload
        $res->assertJSONStructure([
            'meta' => ['chart', 'start', 'end', 'resolution', 'points'],
            'data' => [
                '*' => ['ts', 'value'],
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
            // Timestamp must stay in ISO-like format for frontend parsing
            $this->assertIsString($point['ts']);
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
                $point['ts']
            );

            $this->assertIsInt($point['value']);
        }
    }

    public function test_values_are_in_range(): void {
        $res = $this->getJSON('/api/timeseries?chart=AlarmChart&start=2026-01-24&end=2026-01-24');
        $res->assertOk();

        foreach ($res->JSON('data') as $point) {
            $this->assertGreaterThanOrEqual(0, $point['value']);
            $this->assertLessThanOrEqual(100, $point['value']);
        }
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
