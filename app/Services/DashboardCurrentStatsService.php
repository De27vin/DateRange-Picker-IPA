<?php

namespace App\Services;

use App\Models\Device;

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
                'inbound_calls' => (int) ($grouped['all']['VOICE'] ?? 0),
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
}
