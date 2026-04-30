<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardWidgetSeriesRequest;
use App\Services\DashboardCurrentStatsService;
use App\Services\DashboardWidgetSettingsService;
use App\Services\DashboardWidgetSeriesService;
use Illuminate\Http\JsonResponse;

class DashboardWidgetsController extends Controller
{
    public function summary(DashboardCurrentStatsService $stats): JsonResponse
    {
        return response()->json([
            'data' => $stats->get(),
        ]);
    }

    public function settings(DashboardWidgetSettingsService $settings): JsonResponse
    {
        return response()->json([
            'data' => $settings->getAccountDefaults(),
        ]);
    }

    public function series(
        DashboardWidgetSeriesRequest $request,
        DashboardWidgetSeriesService $series
    ): JsonResponse {
        $widget = (string) $request->validated('widget');
        $startUtc = $request->startUtc();
        $endUtc = $request->endUtc();
        $result = $series->build($widget, $startUtc, $endUtc);

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
