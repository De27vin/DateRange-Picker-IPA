<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DatabaseTimeseriesLoaderService
{
    private const DELTA_MARKER = '_delta';

    public function __construct(
        private readonly ?TimeseriesSnapshotChartMapper $chartMapper = null,
        private readonly ?TimeseriesResolutionService $resolutionService = null,
    ) {
    }

    /**
     * @return array{resolution: string, data: array<int, array{ts: string, series: array<string, int>}>}
     */
    public function fetch(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $rawData = $this->load($chart, $startUtc, $endUtc);
        $resolution = $this->resolutionService()->forRange($startUtc, $endUtc);

        return [
            'resolution' => $resolution,
            'data' => $this->aggregate($rawData, $startUtc, $endUtc, $resolution),
        ];
    }

    /**
     * @return array<int, array{ts: string, series: array<string, int>}>
     */
    public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $accountId = (int) session('account.id');
        $snapshotData = $this->loadSnapshotStateBefore($accountId, $startUtc);

        $changes = DB::table('timeseries')
            ->where('ts_account_id', $accountId)
            ->whereBetween('ts_timestamp', [
                $startUtc->toDateTimeString(),
                $endUtc->toDateTimeString(),
            ])
            ->orderBy('ts_timestamp')
            ->get(['ts_timestamp', 'ts_data'])
            ->keyBy(fn (object $snapshot): string => CarbonImmutable::parse(
                (string) $snapshot->ts_timestamp,
                'UTC'
            )->utc()->startOfHour()->toDateTimeString());

        $points = [];
        for ($ts = $startUtc->startOfHour(); $ts->lte($endUtc); $ts = $ts->addHour()) {
            $change = $changes->get($ts->toDateTimeString());
            if ($change !== null) {
                $decoded = $this->decodeSnapshotData($change->ts_data);
                if ($decoded !== null) {
                    $snapshotData = $this->applyStoredSnapshot($snapshotData, $decoded);
                }
            }

            if ($snapshotData === null) {
                continue;
            }

            $points[] = [
                'ts' => $ts->toIso8601String(),
                'series' => $this->chartMapper()->extractSeries($chart, $snapshotData),
            ];
        }

        return $points;
    }

    private function chartMapper(): TimeseriesSnapshotChartMapper
    {
        return $this->chartMapper ?? new TimeseriesSnapshotChartMapper();
    }

    private function resolutionService(): TimeseriesResolutionService
    {
        return $this->resolutionService ?? new TimeseriesResolutionService();
    }

    private function loadSnapshotStateBefore(int $accountId, CarbonImmutable $startUtc): ?array
    {
        $chain = [];
        $rows = DB::table('timeseries')
            ->where('ts_account_id', $accountId)
            ->where('ts_timestamp', '<', $startUtc->toDateTimeString())
            ->orderByDesc('ts_timestamp')
            ->cursor();

        foreach ($rows as $row) {
            $stored = $this->decodeSnapshotData($row->ts_data);
            if ($stored === null) {
                continue;
            }

            $chain[] = $stored;
            if (!$this->isDelta($stored)) {
                break;
            }
        }

        $state = null;
        foreach (array_reverse($chain) as $stored) {
            $state = $this->applyStoredSnapshot($state, $stored);
        }

        return $state;
    }

    private function decodeSnapshotData(mixed $data): ?array
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return is_array($data) ? $data : null;
    }

    private function isDelta(array $snapshot): bool
    {
        return ($snapshot[self::DELTA_MARKER] ?? false) === true;
    }

    private function applyStoredSnapshot(?array $state, array $stored): array
    {
        if (!$this->isDelta($stored)) {
            return $stored;
        }

        unset($stored[self::DELTA_MARKER]);

        return $this->mergeDelta($state ?? [], $stored);
    }

    private function mergeDelta(array $state, array $delta): array
    {
        foreach ($delta as $key => $value) {
            if ($value === null) {
                unset($state[$key]);
            } elseif (is_array($value) && is_array($state[$key] ?? null)) {
                $state[$key] = $this->mergeDelta($state[$key], $value);
            } else {
                $state[$key] = $value;
            }
        }

        return $state;
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
