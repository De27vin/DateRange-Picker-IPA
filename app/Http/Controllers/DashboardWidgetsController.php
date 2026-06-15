<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\DashboardWidgetSettingsService;
use App\Services\DatabaseTimeseriesLoader;
use App\Services\DeviceAlertsService;
use App\Services\TimeseriesSnapshotCollector;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DashboardWidgetsController extends Controller
{
    private const WIDGETS = [
        'equipment',
        'overdues',
        'alerts',
    ];

    private const ALERT_SERIES_TO_TYPE = [
        'active_alarm' => 'ALARM',
        'battery_malfunction' => 'BATDEF',
        'battery_low' => 'BATLOW',
        'button_malfunction' => 'BUTTON',
        'charge_malfunction' => 'CHARGE',
        'database_malfunction' => 'DB',
        'disk_low' => 'DISK',
        'object_door_failure' => 'LOCATION',
        'elevator_failure' => 'ELEVATOR',
        'gateway_malfunction' => 'GATEWAY',
        'identity_mismatch' => 'IDENTITY',
        'line_alarm' => 'LINE',
        'object_is_under_maintenance' => 'MAINTENANCE',
        'microphone_malfunction' => 'MIC',
        'network_malfunction' => 'NETWORK',
        'periodical_call_overdue' => 'PERIODICAL',
        'pin_mismatch' => 'PIN',
        'power_malfunction' => 'POWER',
        'ram_low' => 'RAM',
        'reserved_device' => 'RESERVE',
        'serial_port_malfunction' => 'SERIAL',
        'shaft_failure' => 'SHAFT',
        'low_signal' => 'SIGNAL',
        'sip_registration_failure' => 'SIP',
        'speaker_malfunction' => 'SPEAKER',
        'technician_check_overdue' => 'TECH',
        'voice_alarm' => 'VOICE',
    ];

    public function summary(
        DeviceAlertsService $alertsService,
        TimeseriesSnapshotCollector $collector
    ): JsonResponse {
        return response()->json([
            'data' => $this->currentStats($alertsService, $collector),
        ]);
    }

    public function settings(DashboardWidgetSettingsService $settings): JsonResponse
    {
        return response()->json([
            'data' => $settings->getEffectiveDefaults(),
        ]);
    }

    public function chartsSettings(DashboardWidgetSettingsService $settings): JsonResponse
    {
        return response()->json([
            'data' => $settings->getEffectiveDefaults(DashboardWidgetSettingsService::SCOPE_CHARTS),
        ]);
    }

    public function series(
        Request $request,
        DatabaseTimeseriesLoader $loader,
        DeviceAlertsService $alertsService
    ): JsonResponse {
        ['widget' => $widget, 'startUtc' => $startUtc, 'endUtc' => $endUtc] = $this->validateSeriesRange($request);
        $result = $this->buildSeries($loader, $alertsService, $widget, $startUtc, $endUtc);

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

    private function validateSeriesRange(Request $request): array
    {
        $validated = $request->validate([
            'widget' => ['required', 'string', Rule::in(self::WIDGETS)],
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d'],
        ]);

        $startUtc = CarbonImmutable::createFromFormat('Y-m-d', (string) $validated['start'], 'UTC')
            ->utc()
            ->startOfDay();
        $requestedEnd = CarbonImmutable::createFromFormat('Y-m-d', (string) $validated['end'], 'UTC')
            ->utc()
            ->endOfDay()
            ->startOfHour();
        $nowFloorHour = CarbonImmutable::now('UTC')->startOfHour();
        $endUtc = $requestedEnd->greaterThan($nowFloorHour) ? $nowFloorHour : $requestedEnd;

        if ($endUtc->lessThan($startUtc)) {
            throw ValidationException::withMessages([
                'end' => 'The end date must be the same as or after start.',
            ]);
        }

        if ($endUtc->diffInDays($startUtc) > 365) {
            throw ValidationException::withMessages([
                'end' => 'The selected range must be 365 days or less.',
            ]);
        }

        return [
            'widget' => (string) $validated['widget'],
            'startUtc' => $startUtc,
            'endUtc' => $endUtc,
        ];
    }

    private function currentStats(
        DeviceAlertsService $alertsService,
        TimeseriesSnapshotCollector $collector
    ): array {
        $accountId = (int) session('account.id');
        $account = Account::query()->find($accountId);

        if (!$account instanceof Account) {
            return $this->emptyStats();
        }

        $snapshot = $collector->buildSnapshotPayload($account);
        $alertCounts = is_array($snapshot['alerts']['alert_type'] ?? null) ? $snapshot['alerts']['alert_type'] : [];
        $grouping = $alertsService->getAlertsGrouping();
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

    private function buildSeries(
        DatabaseTimeseriesLoader $loader,
        DeviceAlertsService $alertsService,
        string $widget,
        CarbonImmutable $startUtc,
        CarbonImmutable $endUtc
    ): array {
        $bucketCount = $this->suggestedBucketCount($startUtc, $endUtc);

        return match ($widget) {
            'equipment' => [
                'bucket_count' => $bucketCount,
                'data' => $this->bucketByLastDatapoint(
                    $loader->load('EquipmentChart', $startUtc, $endUtc),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['enabled', 'disabled']
                ),
            ],
            'overdues' => [
                'bucket_count' => $bucketCount,
                'data' => $this->bucketByLastDatapoint(
                    $loader->load('ServiceLevelChart', $startUtc, $endUtc),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['periodical_calls', 'local_checks']
                ),
            ],
            'alerts' => [
                'bucket_count' => $bucketCount,
                'data' => $this->bucketByLastDatapoint(
                    $this->transformAlertRows($loader->load('AlertsChart', $startUtc, $endUtc), $alertsService),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['critical', 'non_critical']
                ),
            ],
            default => [
                'bucket_count' => $bucketCount,
                'data' => [],
            ],
        };
    }

    private function suggestedBucketCount(CarbonImmutable $startUtc, CarbonImmutable $endUtc): int
    {
        $days = max(1, $startUtc->diffInDays($endUtc) + 1);

        if ($days <= 21) {
            return 5;
        }

        if ($days <= 120) {
            return 4;
        }

        return 3;
    }

    private function transformAlertRows(array $rows, DeviceAlertsService $alertsService): array
    {
        $criticalTypes = array_flip($alertsService->getAlertsGrouping()['critical'] ?? []);
        $normalTypes = array_flip($alertsService->getAlertsGrouping()['normal'] ?? []);

        return array_map(function (array $row) use ($criticalTypes, $normalTypes): array {
            $critical = 0;
            $nonCritical = 0;

            foreach (($row['series'] ?? []) as $seriesKey => $value) {
                $alertType = self::ALERT_SERIES_TO_TYPE[$seriesKey] ?? null;
                if ($alertType === null) {
                    continue;
                }

                if (isset($criticalTypes[$alertType])) {
                    $critical += (int) $value;
                }

                if (isset($normalTypes[$alertType])) {
                    $nonCritical += (int) $value;
                }
            }

            return [
                'ts' => $row['ts'] ?? null,
                'series' => [
                    'critical' => $critical,
                    'non_critical' => $nonCritical,
                ],
            ];
        }, $rows);
    }

    private function bucketByLastDatapoint(
        array $rows,
        CarbonImmutable $startUtc,
        CarbonImmutable $endUtc,
        int $bucketCount,
        array $seriesKeys
    ): array {
        $normalizedRows = [];

        foreach ($rows as $row) {
            if (!is_string($row['ts'] ?? null)) {
                continue;
            }

            $normalizedRows[] = [
                'ts' => CarbonImmutable::parse($row['ts'], 'UTC')->utc(),
                'series' => is_array($row['series'] ?? null) ? $row['series'] : [],
            ];
        }

        usort($normalizedRows, static fn (array $a, array $b): int => $a['ts']->lessThan($b['ts']) ? -1 : 1);

        $totalSeconds = max(1, $endUtc->diffInSeconds($startUtc) + 3600);
        $previousRow = null;
        $bucketed = [];

        for ($index = 0; $index < $bucketCount; $index++) {
            $bucketStartSeconds = (int) floor(($totalSeconds * $index) / $bucketCount);
            $bucketEndSeconds = $index === ($bucketCount - 1)
                ? $totalSeconds
                : (int) floor(($totalSeconds * ($index + 1)) / $bucketCount);

            $bucketStart = $startUtc->addSeconds($bucketStartSeconds);
            $bucketEnd = $startUtc->addSeconds(max($bucketEndSeconds - 1, $bucketStartSeconds))->startOfHour();

            $selectedRow = null;

            foreach ($normalizedRows as $row) {
                if ($row['ts']->lessThan($bucketStart)) {
                    $previousRow = $row;
                    continue;
                }

                if ($row['ts']->greaterThan($bucketEnd)) {
                    break;
                }

                $selectedRow = $row;
            }

            if ($selectedRow === null) {
                $selectedRow = $previousRow;
            }

            $series = [];
            foreach ($seriesKeys as $key) {
                $series[$key] = (int) (($selectedRow['series'][$key] ?? 0));
            }

            $bucketed[] = [
                'bucket_start' => $bucketStart->toIso8601String(),
                'bucket_end' => $bucketEnd->toIso8601String(),
                'label_ts' => $bucketEnd->toIso8601String(),
                'point_ts' => $selectedRow !== null ? $selectedRow['ts']->toIso8601String() : null,
                'series' => $series,
            ];

            if ($selectedRow !== null) {
                $previousRow = $selectedRow;
            }
        }

        return $bucketed;
    }
}
