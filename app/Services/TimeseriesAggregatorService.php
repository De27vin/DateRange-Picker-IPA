<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class TimeseriesAggregatorService
{
    /**
     * @param array<int, array{ts?: string, timestamp?: string, value?: int|float|string|null, enabled?: int|float|string|null}> $points
     * @return array<int, array{ts: string, value: int}>
     */
    public function aggregate(array $points, CarbonImmutable $startUtc, CarbonImmutable $endUtc, string $resolution): array
    {
        if ($points === []) {
            return [];
        }

        $map = [];

        foreach ($points as $point) {
            $rawTs = $point['ts'] ?? $point['timestamp'] ?? null;
            if (!is_string($rawTs) || $rawTs === '') {
                continue;
            }

            try {
                $ts = CarbonImmutable::parse($rawTs, 'UTC')->utc();
            } catch (\Throwable) {
                continue;
            }

            if ($ts->lt($startUtc) || $ts->gt($endUtc)) {
                continue;
            }

            $bucketStart = $this->bucketStartUtc($ts, $resolution);
            $key = $bucketStart->toIso8601String();
            $value = $this->clamp0To100($point['value'] ?? $point['enabled'] ?? null);

            if (!isset($map[$key])) {
                $map[$key] = [
                    'ts' => $key,
                    'sum' => $value,
                    'count' => 1,
                ];
                continue;
            }

            $map[$key]['sum'] += $value;
            $map[$key]['count']++;
        }

        ksort($map);

        return array_map(static function (array $entry): array {
            return [
                'ts' => $entry['ts'],
                'value' => (int) round($entry['sum'] / $entry['count']),
            ];
        }, array_values($map));
    }

    private function bucketStartUtc(CarbonImmutable $ts, string $resolution): CarbonImmutable
    {
        if ($resolution === '1h') {
            return $ts->startOfHour();
        }

        if ($resolution === '6h') {
            $hour = (int) floor($ts->hour / 6) * 6;
            return $ts->startOfDay()->addHours($hour);
        }

        if ($resolution === '1d') {
            return $ts->startOfDay();
        }

        return $this->startOfIsoWeekUtc($ts);
    }

    private function startOfIsoWeekUtc(CarbonImmutable $ts): CarbonImmutable
    {
        $startOfDay = $ts->startOfDay();
        $isoDay = $startOfDay->dayOfWeekIso;

        return $startOfDay->subDays($isoDay - 1);
    }

    private function clamp0To100(mixed $value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return max(0, min(100, (int) round((float) $value)));
    }
}
