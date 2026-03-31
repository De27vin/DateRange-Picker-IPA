<?php

namespace App\Services;

use App\Models\TimeseriesSnapshot;
use Carbon\CarbonImmutable;

class DatabaseTimeseriesLoader
{
    private readonly TimeseriesSnapshotChartMapper $chartMapper;

    public function __construct(?TimeseriesSnapshotChartMapper $chartMapper = null)
    {
        $this->chartMapper = $chartMapper ?? new TimeseriesSnapshotChartMapper();
    }

    /**
     * @return array<int, array{ts: string, series: array<string, int>}>
     */
    public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $accountId = (int) session('account.id');

        return TimeseriesSnapshot::query()
            ->where('account_id', $accountId)
            ->whereBetween('ts_utc', [
                $startUtc->toDateTimeString(),
                $endUtc->toDateTimeString(),
            ])
            ->orderBy('ts_utc')
            ->get(['ts_utc', 'data'])
            ->map(function (TimeseriesSnapshot $snapshot) use ($chart): array {
                return [
                    'ts' => $snapshot->ts_utc->utc()->toIso8601String(),
                    'series' => $this->chartMapper->extractSeries($chart, $snapshot->data ?? []),
                ];
            })
            ->all();
    }
}
