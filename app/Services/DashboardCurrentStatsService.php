<?php

namespace App\Services;

use App\Models\Account;

class DashboardCurrentStatsService
{
    public function __construct(
        private readonly DeviceAlertsService $alertsService,
        private readonly TimeseriesSnapshotCollector $collector,
    ) {
    }

    public function get(): array
    {
        $accountId = (int) session('account.id');
        $account = Account::query()->find($accountId);

        if (!$account instanceof Account) {
            return $this->emptyStats();
        }

        $snapshot = $this->collector->buildSnapshotPayload($account);
        $alertCounts = is_array($snapshot['alerts']['alert_type'] ?? null) ? $snapshot['alerts']['alert_type'] : [];
        $grouping = $this->alertsService->getAlertsGrouping();
        $enabled = (int) ($snapshot['devices']['enabled'] ?? 0);
        $disabled = (int) ($snapshot['devices']['disabled'] ?? 0);
        $periodicalCalls = (int) ($snapshot['service_level']['periodical_calls'] ?? 0);
        $localChecks = (int) ($snapshot['service_level']['local_checks'] ?? 0);
        $critical = $this->sumAlertCounts($alertCounts, $grouping['critical'] ?? []);
        $nonCritical = $this->sumAlertCounts($alertCounts, $grouping['normal'] ?? []);

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
                'inbound_calls' => (int) ($snapshot['alarms']['inbound_calls'] ?? 0),
                'active_alarms' => (int) ($snapshot['alarms']['active_alarms'] ?? 0),
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

    private function emptyStats(): array
    {
        return [
            'equipment' => ['active' => 0, 'inactive' => 0],
            'alarms' => ['inbound_calls' => 0, 'active_alarms' => 0],
            'overdues' => ['periodic_calls' => 0, 'local_checks' => 0],
            'alerts' => ['critical' => 0, 'non_critical' => 0],
            'service_level' => ['automated_checks' => 0, 'physical_checks' => 0],
        ];
    }

    /**
     * @param array<string, int> $alertCounts
     * @param array<int, string> $types
     */
    private function sumAlertCounts(array $alertCounts, array $types): int
    {
        $total = 0;

        foreach ($types as $type) {
            $total += (int) ($alertCounts[$type] ?? 0);
        }

        return $total;
    }
}
