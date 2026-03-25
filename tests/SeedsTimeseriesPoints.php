<?php

namespace Tests;

use App\Models\TimeseriesPoint;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait SeedsTimeseriesPoints
{
    protected function resetTimeseriesPointsTable(): void
    {
        Schema::dropIfExists('timeseries_points');
        Schema::create('timeseries_points', function (Blueprint $table): void {
            $table->id();
            $table->string('chart', 64);
            $table->timestamp('ts_utc');
            $table->unsignedTinyInteger('value');
            $table->timestamps();
            $table->unique(['chart', 'ts_utc']);
            $table->index(['chart', 'ts_utc', 'value']);
        });
    }

    protected function seedHourlyChartData(
        string $chart,
        string $startUtc,
        string $endUtc,
        ?callable $valueResolver = null,
    ): void {
        $start = CarbonImmutable::parse($startUtc, 'UTC')->utc()->startOfHour();
        $end = CarbonImmutable::parse($endUtc, 'UTC')->utc()->startOfHour();
        $rows = [];
        $index = 0;

        for ($ts = $start; $ts->lte($end); $ts = $ts->addHour(), $index++) {
            $value = $valueResolver ? (int) $valueResolver($ts, $index) : ($index % 101);

            $rows[] = [
                'chart' => $chart,
                'ts_utc' => $ts->toDateTimeString(),
                'value' => max(0, min(100, $value)),
                'created_at' => $ts->toDateTimeString(),
                'updated_at' => $ts->toDateTimeString(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            TimeseriesPoint::query()->insert($chunk);
        }
    }
}
