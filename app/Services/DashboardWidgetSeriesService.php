<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class DashboardWidgetSeriesService
{
    public function __construct(
        private readonly TimeseriesService $timeseries,
        private readonly TimeseriesSnapshotChartMapper $chartMapper,
        private readonly DeviceAlertsService $alertsService,
    ) {
    }

    public function build(string $widget, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $bucketCount = $this->timeseries->suggestedBucketCountForRange($startUtc, $endUtc);

        return match ($widget) {
            'equipment' => [
                'bucket_count' => $bucketCount,
                'data' => $this->timeseries->bucketByLastDatapoint(
                    $this->timeseries->load('EquipmentChart', $startUtc, $endUtc),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['enabled', 'disabled']
                ),
            ],
            'overdues' => [
                'bucket_count' => $bucketCount,
                'data' => $this->timeseries->bucketByLastDatapoint(
                    $this->timeseries->load('ServiceLevelChart', $startUtc, $endUtc),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['periodical_calls', 'local_checks']
                ),
            ],
            'alerts' => [
                'bucket_count' => $bucketCount,
                'data' => $this->timeseries->bucketByLastDatapoint(
                    $this->transformAlertRows($this->timeseries->load('AlertsChart', $startUtc, $endUtc)),
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

    private function transformAlertRows(array $rows): array
    {
        $criticalTypes = array_flip($this->alertsService->getAlertsGrouping()['critical'] ?? []);
        $normalTypes = array_flip($this->alertsService->getAlertsGrouping()['normal'] ?? []);

        return array_map(function (array $row) use ($criticalTypes, $normalTypes): array {
            $critical = 0;
            $nonCritical = 0;

            foreach (($row['series'] ?? []) as $seriesKey => $value) {
                $alertType = is_string($seriesKey) ? $this->chartMapper->alertTypeCodeForSeriesKey($seriesKey) : null;
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
}
