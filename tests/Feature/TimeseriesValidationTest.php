<?php

namespace Tests\Feature;

use Tests\TestCase;

class TimeseriesValidationTest extends TestCase
{
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
}
