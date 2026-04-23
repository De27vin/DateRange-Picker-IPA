<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class RealisticTimeseriesGenerator
{
    private const DEVICES_PROFILE = ['baseline' => 78.0, 'dailyAmplitude' => 9.0, 'weeklyAmplitude' => 6.0, 'trendStep' => 2.5, 'noise' => 4];
    private const ALARMS_PROFILE = ['baseline' => 34.0, 'dailyAmplitude' => 14.0, 'weeklyAmplitude' => 10.0, 'trendStep' => 3.5, 'noise' => 6];
    private const SERVICE_PROFILE = ['baseline' => 88.0, 'dailyAmplitude' => 5.0, 'weeklyAmplitude' => 4.0, 'trendStep' => 2.0, 'noise' => 3];

    public function __construct(
        private readonly TimeseriesSnapshotChartMapper $chartMapper,
    ) {
    }

    /**
     * @return array<int, array{ts_account_id: int, ts_timestamp: string, ts_data: string}>
     */
    public function generateForAccount(int $accountId, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $rows = [];
        $devicesDrift = $this->seededInitialValue('devices.enabled.' . $accountId);
        $alarmsDrift = $this->seededInitialValue('alarms.inbound_calls.' . $accountId);
        $serviceDrift = $this->seededInitialValue('service_level.periodical_calls.' . $accountId);

        for ($ts = $startUtc->startOfHour(); $ts->lte($endUtc->startOfHour()); $ts = $ts->addHour()) {
            $devicesEnabled = $this->metricValue(self::DEVICES_PROFILE, $devicesDrift, $ts);
            $devicesDrift = $devicesEnabled;

            $inboundCalls = $this->metricValue(self::ALARMS_PROFILE, $alarmsDrift, $ts);
            $alarmsDrift = $inboundCalls;

            $periodicalCalls = $this->metricValue(self::SERVICE_PROFILE, $serviceDrift, $ts);
            $serviceDrift = $periodicalCalls;

            $snapshot = [
                'devices' => [
                    'enabled' => $devicesEnabled,
                    'disabled' => 100 - $devicesEnabled,
                ],
                'alarms' => [
                    'inbound_calls' => $inboundCalls,
                    'active_alarms' => 100 - $inboundCalls,
                ],
                'alerts' => [
                    'alert_type' => $this->alertTypeValues($ts, $accountId),
                ],
                'service_level' => [
                    'periodical_calls' => $periodicalCalls,
                    'local_checks' => 100 - $periodicalCalls,
                ],
            ];

            $rows[] = [
                'ts_account_id' => $accountId,
                'ts_timestamp' => $ts->toDateTimeString(),
                'ts_data' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, int>
     */
    private function alertTypeValues(CarbonImmutable $ts, int $accountId): array
    {
        $values = [];
        $hourSeed = $ts->dayOfYear * 24 + $ts->hour;

        foreach ($this->chartMapper->supportedAlertTypes() as $index => $type) {
            $seed = abs((int) crc32($type . '.' . $accountId));
            $cycle = (($hourSeed + ($seed % 17)) % 24) / 24;
            $swing = sin(($cycle * 2 * M_PI) - M_PI_2);
            $base = ($seed % 7) + ($index % 5);
            $noise = (($seed + $hourSeed) % 5) - 2;

            $values[$type] = max(0, min(100, (int) round($base + ($swing * (($seed % 9) + 1)) + $noise)));
        }

        return $values;
    }

    private function metricValue(array $profile, float $drift, CarbonImmutable $ts): int
    {
        $nextDrift = $this->clamp(
            $drift + mt_rand((int) (-$profile['trendStep'] * 10), (int) ($profile['trendStep'] * 10)) / 10,
            0,
            100
        );

        $hourOfDay = $ts->hour;
        $dayOfWeek = $ts->dayOfWeekIso;
        $daily = sin((($hourOfDay / 24) * 2 * M_PI) - M_PI_2) * $profile['dailyAmplitude'];
        $weekly = sin((($dayOfWeek / 7) * 2 * M_PI) - M_PI_2) * $profile['weeklyAmplitude'];
        $noise = mt_rand(-$profile['noise'], $profile['noise']);

        return (int) round($this->clamp(($profile['baseline'] * 0.45) + ($nextDrift * 0.35) + $daily + $weekly + $noise, 0, 100));
    }

    private function clamp(float $value, int $min, int $max): float
    {
        return max($min, min($max, $value));
    }

    private function seededInitialValue(string $key): float
    {
        return (float) (abs((int) crc32($key)) % 101);
    }
}
