<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class TimeseriesService
{
    private const DELTA_MARKER = '_delta';
    private const ALERT_TYPE_MAP = [
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

    /**
     * @return array{resolution: string, data: array<int, array{ts: string, series: array<string, int>}>}
     */
    public function fetch(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $rawData = $this->load($chart, $startUtc, $endUtc);
        $resolution = $this->forRange($startUtc, $endUtc);

        return [
            'resolution' => $resolution,
            'data' => $this->aggregate($rawData, $startUtc, $endUtc, $resolution),
        ];
    }

    /**
     * @return array<int, array{ts: string, series: array<string, int>}>
     */
    public function load(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $accountId = (int) session('account.id');
        $snapshotData = $this->loadSnapshotStateBefore($accountId, $startUtc);

        $changes = DB::table('timeseries')
            ->where('ts_account_id', $accountId)
            ->whereBetween('ts_timestamp', [
                $startUtc->toDateTimeString(),
                $endUtc->toDateTimeString(),
            ])
            ->orderBy('ts_timestamp')
            ->get(['ts_timestamp', 'ts_data'])
            ->keyBy(fn (object $snapshot): string => CarbonImmutable::parse(
                (string) $snapshot->ts_timestamp,
                'UTC'
            )->utc()->startOfHour()->toDateTimeString());

        $points = [];
        for ($ts = $startUtc->startOfHour(); $ts->lte($endUtc); $ts = $ts->addHour()) {
            $change = $changes->get($ts->toDateTimeString());
            if ($change !== null) {
                $decoded = $this->decodeSnapshotData($change->ts_data);
                if ($decoded !== null) {
                    $snapshotData = $this->applyStoredSnapshot($snapshotData, $decoded);
                }
            }

            if ($snapshotData === null) {
                continue;
            }

            $points[] = [
                'ts' => $ts->toIso8601String(),
                'series' => $this->extractSeries($chart, $snapshotData),
            ];
        }

        return $points;
    }

    /**
     * @return array<string, int>
     */
    public function extractSeries(string $chart, array $snapshotData): array
    {
        return match ($chart) {
            'EquipmentChart' => [
                'enabled' => $this->intValue($this->path($snapshotData, ['devices', 'enabled'])),
                'disabled' => $this->intValue($this->path($snapshotData, ['devices', 'disabled'])),
            ],
            'AlarmChart' => [
                'inbound_calls' => $this->intValue($this->path($snapshotData, ['alarms', 'inbound_calls'])),
                'active_alarms' => $this->intValue($this->path($snapshotData, ['alarms', 'active_alarms'])),
            ],
            'AlertsChart' => $this->alertTypeSeries($snapshotData),
            'ServiceLevelChart' => [
                'periodical_calls' => $this->intValue($this->path($snapshotData, ['service_level', 'periodical_calls'])),
                'local_checks' => $this->intValue($this->path($snapshotData, ['service_level', 'local_checks'])),
            ],
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    public function supportedAlertTypes(): array
    {
        return array_keys(self::ALERT_TYPE_MAP);
    }

    public function alertTypeCodeForSeriesKey(string $seriesKey): ?string
    {
        return self::ALERT_TYPE_MAP[$seriesKey] ?? null;
    }

    public function forRange(CarbonImmutable $startUtc, CarbonImmutable $endUtc): string
    {
        $rangeDays = $startUtc->startOfDay()->diffInDays($endUtc->startOfDay()) + 1;

        if ($rangeDays <= 2) {
            return '1h';
        }
        if ($rangeDays <= 14) {
            return '6h';
        }
        if ($rangeDays <= 60) {
            return '1d';
        }

        return '1w';
    }

    public function suggestedBucketCountForRange(CarbonImmutable $startUtc, CarbonImmutable $endUtc): int
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

    /**
     * @param array<int, array{ts?: string, timestamp?: string, series?: array<string, int|float|string|null>}> $points
     * @param array<int, string> $seriesKeys
     * @return array<int, array{bucket_start: string, bucket_end: string, label_ts: string, point_ts: string|null, series: array<string, int>}>
     */
    public function bucketByLastDatapoint(
        array $points,
        CarbonImmutable $startUtc,
        CarbonImmutable $endUtc,
        int $bucketCount,
        array $seriesKeys
    ): array {
        $normalizedRows = [];

        foreach ($points as $point) {
            $rawTs = $point['ts'] ?? $point['timestamp'] ?? null;
            if (!is_string($rawTs) || $rawTs === '') {
                continue;
            }

            try {
                $ts = CarbonImmutable::parse($rawTs, 'UTC')->utc();
            } catch (\Throwable) {
                continue;
            }

            $normalizedRows[] = [
                'ts' => $ts,
                'series' => is_array($point['series'] ?? null) ? $point['series'] : [],
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

    /**
     * @param array<int, array{ts?: string, timestamp?: string, series?: array<string, int|float|string|null>}> $points
     * @return array<int, array{ts: string, series: array<string, int>}>
     */
    public function aggregate(array $points, CarbonImmutable $startUtc, CarbonImmutable $endUtc, string $resolution): array
    {
        if ($points === []) {
            return [];
        }

        $map = [];

        foreach ($points as $point) {
            $rawTs = $point['ts'] ?? $point['timestamp'] ?? null;
            if (!is_string($rawTs) || $rawTs === '') {
                continue;
            }

            try {
                $ts = CarbonImmutable::parse($rawTs, 'UTC')->utc();
            } catch (\Throwable) {
                continue;
            }

            if ($ts->lt($startUtc) || $ts->gt($endUtc)) {
                continue;
            }

            $bucketStart = $this->bucketStartUtc($ts, $resolution);
            $key = $bucketStart->toIso8601String();
            $series = $this->normalizeSeries($point['series'] ?? null);

            if (!isset($map[$key])) {
                $map[$key] = [
                    'ts' => $key,
                    'sum' => [],
                    'count' => 1,
                ];
            } else {
                $map[$key]['count']++;
            }

            $seriesKeys = array_unique(array_merge(array_keys($map[$key]['sum']), array_keys($series)));
            foreach ($seriesKeys as $seriesKey) {
                $map[$key]['sum'][$seriesKey] = ($map[$key]['sum'][$seriesKey] ?? 0) + ($series[$seriesKey] ?? 0);
            }
        }

        ksort($map);

        return array_map(function (array $entry): array {
            $aggregatedSeries = [];
            foreach ($entry['sum'] as $seriesKey => $sum) {
                $aggregatedSeries[$seriesKey] = (int) round($sum / $entry['count']);
            }

            return [
                'ts' => $entry['ts'],
                'series' => $aggregatedSeries,
            ];
        }, array_values($map));
    }

    private function loadSnapshotStateBefore(int $accountId, CarbonImmutable $startUtc): ?array
    {
        $chain = [];
        $rows = DB::table('timeseries')
            ->where('ts_account_id', $accountId)
            ->where('ts_timestamp', '<', $startUtc->toDateTimeString())
            ->orderByDesc('ts_timestamp')
            ->cursor();

        foreach ($rows as $row) {
            $stored = $this->decodeSnapshotData($row->ts_data);
            if ($stored === null) {
                continue;
            }

            $chain[] = $stored;
            if (!$this->isDelta($stored)) {
                break;
            }
        }

        $state = null;
        foreach (array_reverse($chain) as $stored) {
            $state = $this->applyStoredSnapshot($state, $stored);
        }

        return $state;
    }

    private function decodeSnapshotData(mixed $data): ?array
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return is_array($data) ? $data : null;
    }

    private function isDelta(array $snapshot): bool
    {
        return ($snapshot[self::DELTA_MARKER] ?? false) === true;
    }

    private function applyStoredSnapshot(?array $state, array $stored): array
    {
        if (!$this->isDelta($stored)) {
            return $stored;
        }

        unset($stored[self::DELTA_MARKER]);

        return $this->mergeDelta($state ?? [], $stored);
    }

    private function mergeDelta(array $state, array $delta): array
    {
        foreach ($delta as $key => $value) {
            if ($value === null) {
                unset($state[$key]);
            } elseif (is_array($value) && is_array($state[$key] ?? null)) {
                $state[$key] = $this->mergeDelta($state[$key], $value);
            } else {
                $state[$key] = $value;
            }
        }

        return $state;
    }

    private function bucketStartUtc(CarbonImmutable $ts, string $resolution): CarbonImmutable
    {
        if ($resolution === '1h') {
            return $ts->startOfHour();
        }

        if ($resolution === '6h') {
            $hour = (int) floor($ts->hour / 6) * 6;
            return $ts->startOfDay()->addHours($hour);
        }

        if ($resolution === '1d') {
            return $ts->startOfDay();
        }

        return $this->startOfIsoWeekUtc($ts);
    }

    private function startOfIsoWeekUtc(CarbonImmutable $ts): CarbonImmutable
    {
        $startOfDay = $ts->startOfDay();
        $isoDay = $startOfDay->dayOfWeekIso;

        return $startOfDay->subDays($isoDay - 1);
    }

    private function clamp0To100(mixed $value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return max(0, min(100, (int) round((float) $value)));
    }

    /**
     * @return array<string, int>
     */
    private function alertTypeSeries(array $snapshotData): array
    {
        $alerts = $this->path($snapshotData, ['alerts', 'alert_type']);
        $series = [];
        foreach ($this->supportedAlertTypes() as $type) {
            $legacyValue = is_array($alerts) ? ($alerts[$type] ?? null) : null;
            $rawValue = is_array($alerts) ? ($alerts[self::ALERT_TYPE_MAP[$type] ?? ''] ?? null) : null;
            $series[$type] = $this->intValue($legacyValue ?? $rawValue ?? 0);
        }

        return $series;
    }

    private function path(array $data, array $path): mixed
    {
        $value = $data;
        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private function intValue(mixed $value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return (int) round((float) $value);
    }

    /**
     * @param mixed $series
     * @return array<string, int>
     */
    private function normalizeSeries(mixed $series): array
    {
        if (!is_array($series)) {
            return [];
        }

        $normalized = [];
        foreach ($series as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $normalized[$key] = $this->clamp0To100($value);
        }

        ksort($normalized);

        return $normalized;
    }
}
