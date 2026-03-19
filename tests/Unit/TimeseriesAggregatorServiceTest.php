<?php

namespace Tests\Unit;

use App\Services\TimeseriesAggregatorService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class TimeseriesAggregatorServiceTest extends TestCase
{
    public function test_it_keeps_hourly_points_for_hourly_resolution(): void
    {
        $service = new TimeseriesAggregatorService();

        $data = $service->aggregate(
            [
                ['ts' => '2026-01-24T00:10:00Z', 'value' => 10],
                ['ts' => '2026-01-24T01:10:00Z', 'value' => 20],
            ],
            CarbonImmutable::parse('2026-01-24T00:00:00Z'),
            CarbonImmutable::parse('2026-01-24T23:00:00Z'),
            '1h',
        );

        $this->assertCount(2, $data);
        $this->assertSame('2026-01-24T00:00:00+00:00', $data[0]['ts']);
        $this->assertSame(10, $data[0]['value']);
    }

    public function test_it_aggregates_into_six_hour_buckets(): void
    {
        $service = new TimeseriesAggregatorService();

        $data = $service->aggregate(
            [
                ['ts' => '2026-01-24T06:10:00Z', 'value' => 10],
                ['ts' => '2026-01-24T11:59:00Z', 'value' => 14],
            ],
            CarbonImmutable::parse('2026-01-24T00:00:00Z'),
            CarbonImmutable::parse('2026-01-27T23:00:00Z'),
            '6h',
        );

        $this->assertCount(1, $data);
        $this->assertSame('2026-01-24T06:00:00+00:00', $data[0]['ts']);
        $this->assertSame(12, $data[0]['value']);
    }

    public function test_it_aggregates_into_daily_buckets(): void
    {
        $service = new TimeseriesAggregatorService();

        $data = $service->aggregate(
            [
                ['ts' => '2026-01-24T00:10:00Z', 'value' => 20],
                ['ts' => '2026-01-24T23:10:00Z', 'value' => 40],
                ['ts' => '2026-01-25T00:10:00Z', 'value' => 50],
            ],
            CarbonImmutable::parse('2026-01-24T00:00:00Z'),
            CarbonImmutable::parse('2026-01-25T23:00:00Z'),
            '1d',
        );

        $this->assertCount(2, $data);
        $this->assertSame('2026-01-24T00:00:00+00:00', $data[0]['ts']);
        $this->assertSame(30, $data[0]['value']);
        $this->assertSame('2026-01-25T00:00:00+00:00', $data[1]['ts']);
        $this->assertSame(50, $data[1]['value']);
    }

    public function test_it_aggregates_into_weekly_buckets_using_monday_start(): void
    {
        $service = new TimeseriesAggregatorService();

        $data = $service->aggregate(
            [
                ['ts' => '2026-01-01T12:00:00Z', 'value' => 10],
                ['ts' => '2026-01-04T12:00:00Z', 'value' => 30],
                ['ts' => '2026-01-05T12:00:00Z', 'value' => 50],
            ],
            CarbonImmutable::parse('2026-01-01T00:00:00Z'),
            CarbonImmutable::parse('2026-01-10T23:00:00Z'),
            '1w',
        );

        $this->assertCount(2, $data);
        $this->assertSame('2025-12-29T00:00:00+00:00', $data[0]['ts']);
        $this->assertSame(20, $data[0]['value']);
        $this->assertSame('2026-01-05T00:00:00+00:00', $data[1]['ts']);
        $this->assertSame(50, $data[1]['value']);
    }
}
