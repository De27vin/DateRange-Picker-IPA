<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeseriesRequest;
use App\Services\DummyTimeseriesGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class TimeSeriesController extends Controller
{
    public function fetch(TimeseriesRequest $request, DummyTimeseriesGenerator $generator): JsonResponse
    {
        $chart = $request->validated('chart');
        $startUtc = $request->startUtc();
        $endUtc = $request->endUtc();
        $startDay = $startUtc->startOfDay();
        $endDay = $endUtc->startOfDay();

        $data = $generator->generate($chart, $startDay, $endDay);
        $data = array_values(array_filter($data, function (array $row) use ($startUtc, $endUtc): bool {
            $ts = CarbonImmutable::parse((string) ($row['ts'] ?? ''), 'UTC');
            return $ts->greaterThanOrEqualTo($startUtc) && $ts->lessThanOrEqualTo($endUtc);
        }));

        return response()->json([
            'meta' => [
                'chart' => $chart,
                'start' => $startUtc->toIso8601String(),
                'end' => $endUtc->toIso8601String(),
                'resolution' => '1h',
                'points' => count($data),
            ],
            'data' => $data,
        ]);
    }
}
