<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class DashboardWidgetSeriesService
{
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

    public function __construct(
        private readonly DatabaseTimeseriesLoader $loader,
        private readonly DeviceAlertsService $alertsService,
    ) {
    }

    public function build(string $widget, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $bucketCount = $this->suggestedBucketCount($startUtc, $endUtc);

        return match ($widget) {
            'equipment' => [
                'bucket_count' => $bucketCount,
                'data' => $this->bucketByLastDatapoint(
                    $this->loader->load('EquipmentChart', $startUtc, $endUtc),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['enabled', 'disabled']
                ),
            ],
            'overdues' => [
                'bucket_count' => $bucketCount,
                'data' => $this->bucketByLastDatapoint(
                    $this->loader->load('ServiceLevelChart', $startUtc, $endUtc),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['periodical_calls', 'local_checks']
                ),
            ],
            'alerts' => [
                'bucket_count' => $bucketCount,
                'data' => $this->bucketByLastDatapoint(
                    $this->transformAlertRows($this->loader->load('AlertsChart', $startUtc, $endUtc)),
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

    private function transformAlertRows(array $rows): array
    {
        $criticalTypes = array_flip($this->alertsService->getAlertsGrouping()['critical'] ?? []);
        $normalTypes = array_flip($this->alertsService->getAlertsGrouping()['normal'] ?? []);

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
