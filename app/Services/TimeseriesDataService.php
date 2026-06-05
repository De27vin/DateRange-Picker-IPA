<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class TimeseriesDataService
{
    public function __construct(
        private readonly DatabaseTimeseriesLoader $loader,
    ) {
    }

    /**
     * @return array{resolution: string, data: array<int, array{ts: string, series: array<string, int>}>}
     */
    public function fetch(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $rawData = $this->loader->load($chart, $startUtc, $endUtc);
        $resolution = $this->resolutionForRange($startUtc, $endUtc);

        return [
            'resolution' => $resolution,
            'data' => $this->aggregate($rawData, $startUtc, $endUtc, $resolution),
        ];
    }

    private function resolutionForRange(CarbonImmutable $startUtc, CarbonImmutable $endUtc): string
    {
        $rangeDays = $startUtc->startOfDay()->diffInDays($endUtc->startOfDay()) + 1;

        if ($rangeDays <= 2) {
            return '1h';
        }
        if ($rangeDays <= 14) {
            return '6h';
        }
        if ($rangeDays <= 60) {
            return '1d';
        }

        return '1w';
    }

    /**
     * @param array<int, array{ts?: string, timestamp?: string, series?: array<string, int|float|string|null>}> $points
     * @return array<int, array{ts: string, series: array<string, int>}>
     */
    private function aggregate(array $points, CarbonImmutable $startUtc, CarbonImmutable $endUtc, string $resolution): array
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

        $startOfDay = $ts->startOfDay();

        return $startOfDay->subDays($startOfDay->dayOfWeekIso - 1);
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
