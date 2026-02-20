<?php

namespace Tests\Unit;

use App\Services\DayRange;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DayRangeTest extends TestCase {

    public function test_single_day(): void {

        $range = new DayRange(
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
        );

        $this->assertEquals(
            CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-01-24 00:00:00'),
            $range->startHour
        );

        $this->assertEquals(
            CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-01-25 00:00:00'),
            $range->endExclusiveHour
        );
    }

    public function test_multiple_days(): void {
        $range = new DayRange(
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-27'),
        );

        $this->assertEquals(
            CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-01-24 00:00:00'),
            $range->startHour
        );

        $this->assertEquals(
            CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-01-28 00:00:00'),
            $range->endExclusiveHour
        );
    }
}