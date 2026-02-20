<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class DummyTimeseriesGenerator
{
    /**
     * @return array<int, array{ts: string, value: int}>
     */
    public function generate(string $chart, CarbonImmutable $startDay, CarbonImmutable $endDayInclusive): array
    {
        // Timeseries generated in UTC to avoid timezone issues
        $start = $startDay->shiftTimezone('UTC')->startOfDay();
        $endExclusive = $endDayInclusive->shiftTimezone('UTC')->addDay()->startOfDay();

        // Seed is random for reproducibility, based on chart and date range
        $seedStr = $chart . '|' . $start->format('Y-m-d') . '|' . $endDayInclusive->format('Y-m-d');
        $seed = $this->seedToInt($seedStr);
        mt_srand($seed);

        $value = mt_rand(20, 80);

        $data = [];
        for ($ts = $start; $ts->lt($endExclusive); $ts = $ts->addHour()) {
            // Trend: random change between -10 and +10 per hour, clamped to 0-100
            $delta = mt_rand(-10, 10); // Max change +/-10 per hour
            $value = $this->clamp($value + $delta, 0, 100);

            $data[] = [
                'ts' => $ts->toIso8601String(), // timestamp example: 2026-01-24T00:00:00+00:00
                'value' => (int) $value,
            ];
        }

        return $data;
    }

    private function clamp(int $v, int $min, int $max): int
    {
        return max($min, min($max, $v));
    }

    private function seedToInt(string $s): int
    {
        // crc32 can deliver negative ints, abs() makes it stable
        return abs((int) crc32($s));
    }
}