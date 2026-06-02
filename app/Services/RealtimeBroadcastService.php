<?php

namespace App\Services;

use App\Events\RealtimeEventBroadcast;
use Illuminate\Support\Facades\Log;

class RealtimeBroadcastService
{
    private AlarmNotificationService $alarmService;

    public function __construct(AlarmNotificationService $alarmService)
    {
        $this->alarmService = $alarmService;
    }

    public function broadcast(string $eventType, int $accountId, array $data): void
    {
        $event = new RealtimeEventBroadcast($eventType, $accountId, $data);
        event($event);

        Log::info('Realtime event broadcast', [
            'event_type' => $eventType,
            'account_id' => $accountId,
            'channel' => 'realtime.account.' . $accountId,
            'data' => $data,
        ]);
    }

    public function broadcastAlarmNotification(int $accountId): void
    {
        $currentAlarmCalls = $this->alarmService->getActiveAlarmsForAccount($accountId);

        $this->broadcast('alarm_notification', $accountId, [
            'alarmCalls' => $currentAlarmCalls->toArray(),
            'count' => $currentAlarmCalls->count()
        ]);

        Log::info('Alarm notification broadcast triggered', [
            'account_id' => $accountId,
            'alarm_count' => $currentAlarmCalls->count()
        ]);
    }

    public function broadcastCarCallStatus(
        int $accountId,
        int $deviceId,
        string $status // 'start' or 'end'
    ): void {
        $this->broadcast('carcall_status', $accountId, [
            'device_id' => $deviceId,
            'status' => $status // 'start' or 'end'
        ]);
    }

    public function broadcastAgentStatus(
        int $accountId,
        int $deviceId,
        string $status // 'connecting', 'connected', or 'disconnected'
    ): void {
        $this->broadcast('agent_status', $accountId, [
            'device_id' => $deviceId,
            'status' => $status // 'connecting', 'connected', or 'disconnected'
        ]);
    }
}
