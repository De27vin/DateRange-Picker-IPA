<?php

namespace Tests\Unit;

use App\Services\TimeseriesAggregatorService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class TimeseriesAggregatorServiceTest extends TestCase
{
    public function test_it_aggregates_each_series_key_independently(): void
    {
        $service = new TimeseriesAggregatorService();
        $startUtc = CarbonImmutable::parse('2026-01-01T00:00:00Z');
        $endUtc = CarbonImmutable::parse('2026-01-01T05:00:00Z');

        $result = $service->aggregate([
            ['ts' => '2026-01-01T00:00:00Z', 'series' => ['battery_low' => 10, 'network_malfunction' => 20]],
            ['ts' => '2026-01-01T01:00:00Z', 'series' => ['battery_low' => 20]],
            ['ts' => '2026-01-01T02:00:00Z', 'series' => ['network_malfunction' => 40]],
            ['ts' => '2026-01-01T03:00:00Z', 'series' => ['battery_low' => 30, 'network_malfunction' => 60]],
            ['ts' => '2026-01-01T04:00:00Z', 'series' => ['battery_low' => 40]],
            ['ts' => '2026-01-01T05:00:00Z', 'series' => ['network_malfunction' => 80]],
        ], $startUtc, $endUtc, '6h');

        $this->assertSame([
            [
                'ts' => '2026-01-01T00:00:00+00:00',
                'series' => [
                    'battery_low' => 17,
                    'network_malfunction' => 33,
                ],
            ],
        ], $result);
    }

    public function test_missing_series_keys_are_treated_as_zero_within_bucket(): void
    {
        $service = new TimeseriesAggregatorService();
        $startUtc = CarbonImmutable::parse('2026-01-01T00:00:00Z');
        $endUtc = CarbonImmutable::parse('2026-01-01T01:00:00Z');

        $result = $service->aggregate([
            ['ts' => '2026-01-01T00:00:00Z', 'series' => ['enabled' => 100, 'disabled' => 0]],
            ['ts' => '2026-01-01T01:00:00Z', 'series' => ['enabled' => 80]],
        ], $startUtc, $endUtc, '6h');

        $this->assertSame(90, $result[0]['series']['enabled']);
        $this->assertSame(0, $result[0]['series']['disabled']);
    }
}
