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
        $chart = $request->string('chart')->toString();

        $startDay = CarbonImmutable::createFromFormat('Y-m-d', $request->input('start'))->startOfDay();
        $endDay   = CarbonImmutable::createFromFormat('Y-m-d', $request->input('end'))->startOfDay();

        $data = $generator->generate($chart, $startDay, $endDay);

        return response()->json([
            'meta' => [
                'chart' => $chart,
                'start' => $startDay->toDateString(),
                'end' => $endDay->toDateString(),
                'resolution' => '1h',
                'points' => count($data),
            ],
            'data' => $data,
        ]);
    }
}