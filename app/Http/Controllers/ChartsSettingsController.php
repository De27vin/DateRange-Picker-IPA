<?php

namespace App\Http\Controllers;

use App\Services\DashboardWidgetSettingsService;
use Illuminate\Http\JsonResponse;

class ChartsSettingsController extends Controller
{
    public function settings(DashboardWidgetSettingsService $settings): JsonResponse
    {
        return response()->json([
            'data' => $settings->getEffectiveDefaults(DashboardWidgetSettingsService::SCOPE_CHARTS),
        ]);
    }
}
