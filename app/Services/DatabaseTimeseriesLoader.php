<?php

namespace App\Services;

use App\Models\TimeseriesPoint;
use Carbon\CarbonImmutable;

class DatabaseTimeseriesLoader
{
    /**
     * @return array<int, array{ts: string, value: int}>
     */
    public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        return TimeseriesPoint::query()
            ->where('chart', $chart)
            ->whereBetween('ts_utc', [
                $startUtc->toDateTimeString(),
                $endUtc->toDateTimeString(),
            ])
            ->orderBy('ts_utc')
            ->get(['ts_utc', 'value'])
            ->map(static function (TimeseriesPoint $point): array {
                return [
                    'ts' => $point->ts_utc->utc()->toIso8601String(),
                    'value' => (int) $point->value,
                ];
            })
            ->all();
    }
}
