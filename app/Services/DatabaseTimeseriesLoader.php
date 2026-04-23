<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

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

        return DB::table('timeseries')
            ->where('ts_account_id', $accountId)
            ->whereBetween('ts_timestamp', [
                $startUtc->toDateTimeString(),
                $endUtc->toDateTimeString(),
            ])
            ->orderBy('ts_timestamp')
            ->get(['ts_timestamp', 'ts_data'])
            ->map(function (object $snapshot) use ($chart): array {
                $data = $snapshot->ts_data;
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    $data = is_array($decoded) ? $decoded : [];
                }

                return [
                    'ts' => CarbonImmutable::parse((string) $snapshot->ts_timestamp, 'UTC')->utc()->toIso8601String(),
                    'series' => $this->chartMapper->extractSeries($chart, is_array($data) ? $data : []),
                ];
            })
            ->all();
    }
}
