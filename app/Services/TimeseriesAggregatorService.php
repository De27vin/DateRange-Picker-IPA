<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class TimeseriesAggregatorService
{
    public function suggestedBucketCountForRange(CarbonImmutable $startUtc, CarbonImmutable $endUtc): int
    {
        $days = max(1, $startUtc->diffInDays($endUtc) + 1);

        if ($days <= 21) {
            return 5;
        }

        if ($days <= 120) {
            return 4;
        }

        return 3;
    }

    /**
     * @param array<int, array{ts?: string, timestamp?: string, series?: array<string, int|float|string|null>}> $points
     * @param array<int, string> $seriesKeys
     * @return array<int, array{bucket_start: string, bucket_end: string, label_ts: string, point_ts: string|null, series: array<string, int>}>
     */
    public function bucketByLastDatapoint(
        array $points,
        CarbonImmutable $startUtc,
        CarbonImmutable $endUtc,
        int $bucketCount,
        array $seriesKeys
    ): array {
        $normalizedRows = [];

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

            $normalizedRows[] = [
                'ts' => $ts,
                'series' => is_array($point['series'] ?? null) ? $point['series'] : [],
            ];
        }

        usort($normalizedRows, static fn (array $a, array $b): int => $a['ts']->lessThan($b['ts']) ? -1 : 1);

        $totalSeconds = max(1, $endUtc->diffInSeconds($startUtc) + 3600);
        $previousRow = null;
        $bucketed = [];

        for ($index = 0; $index < $bucketCount; $index++) {
            $bucketStartSeconds = (int) floor(($totalSeconds * $index) / $bucketCount);
            $bucketEndSeconds = $index === ($bucketCount - 1)
                ? $totalSeconds
                : (int) floor(($totalSeconds * ($index + 1)) / $bucketCount);

            $bucketStart = $startUtc->addSeconds($bucketStartSeconds);
            $bucketEnd = $startUtc->addSeconds(max($bucketEndSeconds - 1, $bucketStartSeconds))->startOfHour();

            $selectedRow = null;

            foreach ($normalizedRows as $row) {
                if ($row['ts']->lessThan($bucketStart)) {
                    $previousRow = $row;
                    continue;
                }

                if ($row['ts']->greaterThan($bucketEnd)) {
                    break;
                }

                $selectedRow = $row;
            }

            if ($selectedRow === null) {
                $selectedRow = $previousRow;
            }

            $series = [];
            foreach ($seriesKeys as $key) {
                $series[$key] = (int) (($selectedRow['series'][$key] ?? 0));
            }

            $bucketed[] = [
                'bucket_start' => $bucketStart->toIso8601String(),
                'bucket_end' => $bucketEnd->toIso8601String(),
                'label_ts' => $bucketEnd->toIso8601String(),
                'point_ts' => $selectedRow !== null ? $selectedRow['ts']->toIso8601String() : null,
                'series' => $series,
            ];

            if ($selectedRow !== null) {
                $previousRow = $selectedRow;
            }
        }

        return $bucketed;
    }

    /**
     * @param array<int, array{ts?: string, timestamp?: string, series?: array<string, int|float|string|null>}> $points
     * @return array<int, array{ts: string, series: array<string, int>}>
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
            $series = $this->normalizeSeries($point['series'] ?? null);

            if (!isset($map[$key])) {
                $map[$key] = [
                    'ts' => $key,
                    'sum' => [],
                    'count' => 1,
                ];
            } else {
                $map[$key]['count']++;
            }

            $seriesKeys = array_unique(array_merge(array_keys($map[$key]['sum']), array_keys($series)));
            foreach ($seriesKeys as $seriesKey) {
                $map[$key]['sum'][$seriesKey] = ($map[$key]['sum'][$seriesKey] ?? 0) + ($series[$seriesKey] ?? 0);
            }
        }

        ksort($map);

        return array_map(function (array $entry): array {
            $aggregatedSeries = [];
            foreach ($entry['sum'] as $seriesKey => $sum) {
                $aggregatedSeries[$seriesKey] = (int) round($sum / $entry['count']);
            }

            return [
                'ts' => $entry['ts'],
                'series' => $aggregatedSeries,
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

    /**
     * @param mixed $series
     * @return array<string, int>
     */
    private function normalizeSeries(mixed $series): array
    {
        if (!is_array($series)) {
            return [];
        }

        $normalized = [];
        foreach ($series as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $normalized[$key] = $this->clamp0To100($value);
        }

        ksort($normalized);

        return $normalized;
    }
}
