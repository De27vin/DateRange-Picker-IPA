<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardWidgetSeriesRequest;
use App\Services\ChartsService;
use Illuminate\Http\JsonResponse;

class DashboardWidgetsController extends Controller
{
    public function summary(ChartsService $charts): JsonResponse
    {
        return response()->json([
            'data' => $charts->currentStats(),
        ]);
    }

    public function settings(ChartsService $charts): JsonResponse
    {
        return response()->json([
            'data' => $charts->getEffectiveDefaults(),
        ]);
    }

    public function chartsSettings(ChartsService $charts): JsonResponse
    {
        return response()->json([
            'data' => $charts->getEffectiveDefaults(ChartsService::SCOPE_CHARTS),
        ]);
    }

    public function series(
        DashboardWidgetSeriesRequest $request,
        ChartsService $charts
    ): JsonResponse {
        $widget = (string) $request->validated('widget');
        $startUtc = $request->startUtc();
        $endUtc = $request->endUtc();
        $result = $charts->widgetSeries($widget, $startUtc, $endUtc);

        return response()->json([
            'meta' => [
                'widget' => $widget,
                'start' => $startUtc->toIso8601String(),
                'end' => $endUtc->toIso8601String(),
                'bucket_count' => $result['bucket_count'],
                'points' => count($result['data']),
            ],
            'data' => $result['data'],
        ]);
    }
}
