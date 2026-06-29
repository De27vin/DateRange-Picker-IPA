<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeseriesRequest;
use App\Services\DatabaseTimeseriesLoaderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TimeSeriesController extends Controller
{
    public function fetch(TimeseriesRequest $request, DatabaseTimeseriesLoaderService $timeseriesData): JsonResponse
    {
        $chart = $request->validated('chart');
        $startUtc = $request->startUtc();
        $endUtc = $request->endUtc();
        $result = $timeseriesData->fetch($chart, $startUtc, $endUtc);
        $data = $result['data'];

        $requestId = $request->attributes->get('request_id') ?: $request->header('X-Request-Id');
        Log::info('timeseries.request', [
            'event' => 'timeseries.request',
            'request_id' => $requestId,
            'chart' => $chart,
            'start' => $startUtc->toIso8601String(),
            'end' => $endUtc->toIso8601String(),
            'clamped' => $request->endWasClamped(),
            'status' => 200,
        ]);

        return response()->json([
            'meta' => [
                'chart' => $chart,
                'start' => $startUtc->toIso8601String(),
                'end' => $endUtc->toIso8601String(),
                'resolution' => $result['resolution'],
                'points' => count($data),
            ],
            'data' => $data,
        ]);
    }
}
