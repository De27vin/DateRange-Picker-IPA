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
        private readonly ?TimeseriesAggregatorService $aggregator = null,
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
            'data' => $this->aggregator()->aggregate($rawData, $startUtc, $endUtc, $resolution),
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

    private function aggregator(): TimeseriesAggregatorService
    {
        return $this->aggregator ?? new TimeseriesAggregatorService();
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

}
