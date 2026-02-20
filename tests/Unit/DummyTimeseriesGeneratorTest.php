<?php

namespace Tests\Unit;

use App\Services\DummyTimeseriesGenerator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DummyTimeseriesGeneratorTest extends TestCase
{
    public function test_single_day_generates_24_points(): void
    {
        $gen = new DummyTimeseriesGenerator();

        $data = $gen->generate(
            'EquipmentChart',
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
        );

        $this->assertCount(24, $data);
    }

    public function test_four_days_inclusive_generates_96_points(): void
    {
        $gen = new DummyTimeseriesGenerator();

        $data = $gen->generate(
            'EquipmentChart',
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-27'),
        );

        $this->assertCount(96, $data);
    }

    public function test_structures(): void
    {
        $gen = new DummyTimeseriesGenerator();

        $data = $gen->generate(
            'AlarmChart',
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
        );

        foreach ($data as $point) {
            $this->assertArrayHasKey('ts', $point);
            $this->assertArrayHasKey('value', $point);

            $this->assertIsString($point['ts']);
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
                $point['ts']
            );

            $this->assertIsInt($point['value']);
        }
    }

        public function test_ranges(): void
    {
        $gen = new DummyTimeseriesGenerator();

        $data = $gen->generate(
            'AlarmChart',
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
        );

        foreach ($data as $point) {         
            $this->assertGreaterThanOrEqual(0, $point['value']);
            $this->assertLessThanOrEqual(100, $point['value']);
        }
    }

    public function test_timestamps_are_hourly_and_sorted(): void
    {
        $gen = new DummyTimeseriesGenerator();

        $data = $gen->generate(
            'AlertsChart',
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
        );

        for ($i = 1; $i < count($data); $i++) {
            $prev = CarbonImmutable::parse($data[$i - 1]['ts']);
            $curr = CarbonImmutable::parse($data[$i]['ts']);

            $this->assertTrue($curr->greaterThan($prev));
            $this->assertSame(1, $prev->diffInHours($curr));
        }
    }

    public function test_value_changes_by_max_10_per_step(): void
    {
        $gen = new DummyTimeseriesGenerator();

        $data = $gen->generate(
            'ServiceLevelChart',
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
            CarbonImmutable::createFromFormat('Y-m-d', '2026-01-24'),
        );

        for ($i = 1; $i < count($data); $i++) {
            $diff = abs($data[$i]['value'] - $data[$i - 1]['value']);
            $this->assertLessThanOrEqual(10, $diff);
        }
    }
}