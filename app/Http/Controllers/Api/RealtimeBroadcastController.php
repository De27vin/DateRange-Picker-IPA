<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RealtimeBroadcastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RealtimeBroadcastController extends Controller
{
    private RealtimeBroadcastService $broadcastService;

    public function __construct(RealtimeBroadcastService $broadcastService)
    {
        $this->broadcastService = $broadcastService;
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'event_type' => ['required', 'string', Rule::in(['alarm_notification', 'carcall_status', 'agent_status'])],
            'account_id' => 'required|integer',
            'data' => 'array'
        ]);

        $eventType = $request->input('event_type');
        $accountId = $request->input('account_id');
        $data = $request->input('data', []);

        try {
            // Route to appropriate broadcast method based on event type
            match ($eventType) {
                'alarm_notification' => $this->handleAlarmNotification($accountId, $data),
                'carcall_status' => $this->handleCarCallStatus($accountId, $data),
                'agent_status' => $this->handleAgentStatus($accountId, $data),
            };

            return response()->json([
                'success' => true,
                'event_type' => $eventType,
                'account_id' => $accountId,
                'channel' => 'realtime.account.' . $accountId
            ]);

        } catch (\Throwable $e) {
            Log::error('Realtime broadcast failed', [
                'event_type' => $eventType,
                'account_id' => $accountId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function handleAlarmNotification(int $accountId, array $data): void
    {
        $this->broadcastService->broadcastAlarmNotification($accountId);
    }

    private function handleCarCallStatus(int $accountId, array $data): void
    {
        // Validate required fields for carcall_status
        if (!isset($data['device_id'], $data['status'])) {
            throw new \InvalidArgumentException('Missing required fields for carcall_status: device_id, status');
        }

        if (!in_array($data['status'], ['start', 'end'])) {
            throw new \InvalidArgumentException('Invalid status value. Must be "start" or "end"');
        }

        $this->broadcastService->broadcastCarCallStatus(
            $accountId,
            $data['device_id'],
            $data['status']
        );
    }

    private function handleAgentStatus(int $accountId, array $data): void
    {
        // Validate required fields for agent_status
        if (!isset($data['device_id'], $data['status'])) {
            throw new \InvalidArgumentException('Missing required fields for agent_status: device_id, status');
        }

        if (!in_array($data['status'], ['connecting', 'connected', 'disconnected'])) {
            throw new \InvalidArgumentException('Invalid status value. Must be "connecting", "connected", or "disconnected"');
        }

        $this->broadcastService->broadcastAgentStatus(
            $accountId,
            $data['device_id'],
            $data['status']
        );
    }
}
