<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AlarmNotificationService;
use App\Traits\DevicesTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlarmBroadcastController extends Controller
{
    use DevicesTrait;

    private AlarmNotificationService $alarmService;

    public function __construct(AlarmNotificationService $alarmService)
    {
        $this->alarmService = $alarmService;
    }

    public function getCurrentAlarms(Request $request)
    {
        try {
            $accountId = session('account.id');

            if (!$accountId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No account session found'
                ], 401);
            }

            $activeAlarms = $this->alarmService->getActiveAlarmsForAccount($accountId);

            return response()->json([
                'success' => true,
                'account_id' => $accountId,
                'alarms' => $activeAlarms->toArray(),
                'count' => $activeAlarms->count()
            ]);

        } catch (\Throwable $e) {
            Log::error('AlarmBroadcastController - Get current alarms failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}