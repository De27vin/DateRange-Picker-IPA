<?php

namespace App\Http\Controllers;

use App\Services\ChartsSettingsService;
use Illuminate\Http\JsonResponse;

class ChartsSettingsController extends Controller
{
    public function settings(ChartsSettingsService $settings): JsonResponse
    {
        return response()->json([
            'data' => $settings->getEffectiveDefaults(),
        ]);
    }
}
