<?php

namespace Tests\Unit;

use App\Services\TimeseriesResolutionService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class TimeseriesResolutionServiceTest extends TestCase
{
    public function test_it_selects_hourly_resolution_for_up_to_two_days(): void
    {
        $service = new TimeseriesResolutionService();

        $resolution = $service->forRange(
            CarbonImmutable::parse('2026-01-24T00:00:00Z'),
            CarbonImmutable::parse('2026-01-25T23:00:00Z'),
        );

        $this->assertSame('1h', $resolution);
    }

    public function test_it_selects_six_hour_resolution_for_up_to_fourteen_days(): void
    {
        $service = new TimeseriesResolutionService();

        $resolution = $service->forRange(
            CarbonImmutable::parse('2026-01-24T00:00:00Z'),
            CarbonImmutable::parse('2026-02-06T23:00:00Z'),
        );

        $this->assertSame('6h', $resolution);
    }

    public function test_it_selects_daily_resolution_for_up_to_sixty_days(): void
    {
        $service = new TimeseriesResolutionService();

        $resolution = $service->forRange(
            CarbonImmutable::parse('2026-01-01T00:00:00Z'),
            CarbonImmutable::parse('2026-02-15T23:00:00Z'),
        );

        $this->assertSame('1d', $resolution);
    }

    public function test_it_selects_weekly_resolution_for_long_ranges(): void
    {
        $service = new TimeseriesResolutionService();

        $resolution = $service->forRange(
            CarbonImmutable::parse('2026-01-01T00:00:00Z'),
            CarbonImmutable::parse('2026-04-15T23:00:00Z'),
        );

        $this->assertSame('1w', $resolution);
    }
}
