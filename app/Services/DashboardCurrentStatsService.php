<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\DB;

class DashboardCurrentStatsService
{
    public function __construct(
        private readonly DeviceAlertsService $alertsService,
    ) {
    }

    public function get(): array
    {
        $accountId = (int) session('account.id');
        $grouped = $this->alertsService->getGroupedAlertsCounts($accountId);
        $enabled = Device::enabled()->count();
        $disabled = Device::disabled()->count();
        $periodicalCalls = (int) ($grouped['all']['PERIODICAL'] ?? 0);
        $localChecks = (int) ($grouped['all']['TECH'] ?? 0);
        $critical = (int) array_sum($grouped['critical'] ?? []);
        $nonCritical = (int) array_sum($grouped['normal'] ?? []);

        $automatedChecks = 0;
        $physicalChecks = 0;

        if ($enabled > 0) {
            $automatedChecks = (int) round(max(0, (($enabled - $periodicalCalls - $critical) / $enabled) * 100));
            $physicalChecks = (int) round(max(0, (($enabled - $localChecks - $nonCritical) / $enabled) * 100));
        }

        return [
            'equipment' => [
                'active' => $enabled,
                'inactive' => $disabled,
            ],
            'alarms' => [
                'inbound_calls' => $this->activeAlarmSessionCount($accountId),
                'active_alarms' => (int) array_sum($grouped['alarming'] ?? []),
            ],
            'overdues' => [
                'periodic_calls' => $periodicalCalls,
                'local_checks' => $localChecks,
            ],
            'alerts' => [
                'critical' => $critical,
                'non_critical' => $nonCritical,
            ],
            'service_level' => [
                'automated_checks' => $automatedChecks,
                'physical_checks' => $physicalChecks,
            ],
        ];
    }

    private function activeAlarmSessionCount(int $accountId): int
    {
        return (int) DB::table('sessions as s')
            ->join('session_types as st', 's.session_st_id', '=', 'st.st_id')
            ->where('s.session_account_id', $accountId)
            ->where('st.st_type', 'ALARM')
            ->whereNull('s.session_end')
            ->where(function ($query): void {
                $query->whereNotNull('s.session_device_id')
                    ->orWhereExists(function ($sub): void {
                        $sub->from('sessions as child')
                            ->join('session_types as child_st', 'child.session_st_id', '=', 'child_st.st_id')
                            ->whereColumn('child.session_ref_id', 's.session_id')
                            ->where('child_st.st_type', 'AGENT')
                            ->whereNotNull('child.session_device_id');
                    });
            })
            ->count();
    }
}
