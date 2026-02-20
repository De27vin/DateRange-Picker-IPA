<?php
namespace App\Services;


use App\Models\Device;
use App\Models\Session;
use Illuminate\Support\Arr;

class SessionHistoryService
{

    public function getSessionDetail(int $accountId, int $sessionId)
    {
        return Session::with([
            'session_type',
            'session_path',
            'session_direction',
            'alerts.alert_type.alert_severity',
            'sets.setting',
            'events.event_type',
            'events.event_severity'
            ])
            ->where('session_id', $sessionId)
            ->where('session_account_id', $accountId)
            ->orderByDesc('session_id')
            ->first();
    }

    public function getSessionsDetailBulk(array $sessionIds, array $historyFilter, array $dateSpan)
    {
        $startDate = $dateSpan['start_date'];
        $endDate   = $dateSpan['end_date'];

        return Session::with('session_type', 'alerts', 'sets', 'events')
            ->whereIn('session_id', $sessionIds)
            ->whereBetween('session_start', [$startDate, $endDate])
            ->orderByDesc('session_id')
            ->get();
    }

}