<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class RealisticTimeseriesGenerator
{
    private const CHART_PROFILES = [
        'EquipmentChart' => [
            'baseline' => 78.0,
            'dailyAmplitude' => 9.0,
            'weeklyAmplitude' => 6.0,
            'trendStep' => 2.5,
            'noise' => 4,
        ],
        'AlarmChart' => [
            'baseline' => 34.0,
            'dailyAmplitude' => 14.0,
            'weeklyAmplitude' => 10.0,
            'trendStep' => 3.5,
            'noise' => 6,
        ],
        'AlertsChart' => [
            'baseline' => 42.0,
            'dailyAmplitude' => 11.0,
            'weeklyAmplitude' => 8.0,
            'trendStep' => 4.0,
            'noise' => 7,
        ],
        'ServiceLevelChart' => [
            'baseline' => 88.0,
            'dailyAmplitude' => 5.0,
            'weeklyAmplitude' => 4.0,
            'trendStep' => 2.0,
            'noise' => 3,
        ],
    ];

    /**
     * @return array<int, array{chart: string, ts_utc: string, value: int, created_at: string, updated_at: string}>
     */
    public function generate(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $profile = self::CHART_PROFILES[$chart] ?? self::CHART_PROFILES['EquipmentChart'];
        $points = [];
        $drift = $this->seededInitialValue($chart);

        for ($ts = $startUtc->startOfHour(); $ts->lte($endUtc->startOfHour()); $ts = $ts->addHour()) {
            $drift = $this->clamp(
                $drift + mt_rand((int) (-$profile['trendStep'] * 10), (int) ($profile['trendStep'] * 10)) / 10,
                0,
                100
            );

            $hourOfDay = $ts->hour;
            $dayOfWeek = $ts->dayOfWeekIso;
            $daily = sin((($hourOfDay / 24) * 2 * M_PI) - M_PI_2) * $profile['dailyAmplitude'];
            $weekly = sin((($dayOfWeek / 7) * 2 * M_PI) - M_PI_2) * $profile['weeklyAmplitude'];
            $noise = mt_rand(-$profile['noise'], $profile['noise']);
            $value = (int) round($this->clamp(($profile['baseline'] * 0.45) + ($drift * 0.35) + $daily + $weekly + $noise, 0, 100));

            $points[] = [
                'chart' => $chart,
                'ts_utc' => $ts->toDateTimeString(),
                'value' => $value,
                'created_at' => $ts->toDateTimeString(),
                'updated_at' => $ts->toDateTimeString(),
            ];
        }

        return $points;
    }

    /**
     * @return array<int, string>
     */
    public function supportedCharts(): array
    {
        return array_keys(self::CHART_PROFILES);
    }

    private function clamp(float $value, int $min, int $max): float
    {
        return max($min, min($max, $value));
    }

    private function seededInitialValue(string $chart): float
    {
        return (float) (abs((int) crc32($chart)) % 101);
    }
}
