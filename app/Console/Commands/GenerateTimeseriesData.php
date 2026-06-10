<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\TimeseriesSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateTimeseriesData extends Command
{
    private const DEVICES_PROFILE = ['baseline' => 78.0, 'dailyAmplitude' => 9.0, 'weeklyAmplitude' => 6.0, 'trendStep' => 2.5, 'noise' => 4];
    private const ALARMS_PROFILE = ['baseline' => 34.0, 'dailyAmplitude' => 14.0, 'weeklyAmplitude' => 10.0, 'trendStep' => 3.5, 'noise' => 6];
    private const SERVICE_PROFILE = ['baseline' => 88.0, 'dailyAmplitude' => 5.0, 'weeklyAmplitude' => 4.0, 'trendStep' => 2.0, 'noise' => 3];
    private const ALERT_TYPES = [
        'active_alarm',
        'battery_malfunction',
        'battery_low',
        'button_malfunction',
        'charge_malfunction',
        'database_malfunction',
        'disk_low',
        'object_door_failure',
        'elevator_failure',
        'gateway_malfunction',
        'identity_mismatch',
        'line_alarm',
        'object_is_under_maintenance',
        'microphone_malfunction',
        'network_malfunction',
        'periodical_call_overdue',
        'pin_mismatch',
        'power_malfunction',
        'ram_low',
        'reserved_device',
        'serial_port_malfunction',
        'shaft_failure',
        'low_signal',
        'sip_registration_failure',
        'speaker_malfunction',
        'technician_check_overdue',
        'voice_alarm',
    ];

    protected $signature = 'timeseries:generate
        {--start= : UTC start timestamp, default is now minus one year at the start of the hour}
        {--end= : UTC end timestamp, default is now at the start of the hour}
        {--account=* : Limit generation to one or more account ids}
        {--truncate : Delete existing snapshot rows before generating}';
    protected $description = 'Generate one year of realistic hourly UTC snapshot data in the database';

    public function handle(): int
    {
        try {
            $startUtc = $this->resolveStartUtc();
            $endUtc = $this->resolveEndUtc();
            $accountIds = $this->resolveAccountIds();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        if ($endUtc->lt($startUtc)) {
            $this->error('The end option must be after or equal to start.');
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            TimeseriesSnapshot::query()->delete();
            $this->info('Deleted existing timeseries snapshot data.');
        }

        foreach ($accountIds as $accountId) {
            foreach (array_chunk($this->generateForAccount($accountId, $startUtc, $endUtc), 500) as $chunk) {
                TimeseriesSnapshot::query()->upsert($chunk, ['ts_account_id', 'ts_timestamp'], ['ts_data']);
            }

            $this->line(sprintf(
                'account %d: %d hourly snapshots generated from %s to %s',
                $accountId,
                $endUtc->diffInHours($startUtc) + 1,
                $startUtc->toIso8601String(),
                $endUtc->toIso8601String()
            ));
        }

        $this->info('Timeseries generation completed.');

        return self::SUCCESS;
    }

    private function resolveStartUtc(): CarbonImmutable
    {
        $start = $this->option('start');
        if (is_string($start) && $start !== '') {
            return CarbonImmutable::parse($start, 'UTC')->utc()->startOfHour();
        }

        return CarbonImmutable::now('UTC')->subYear()->startOfHour();
    }

    private function resolveEndUtc(): CarbonImmutable
    {
        $end = $this->option('end');
        if (is_string($end) && $end !== '') {
            return CarbonImmutable::parse($end, 'UTC')->utc()->startOfHour();
        }

        return CarbonImmutable::now('UTC')->startOfHour();
    }

    /**
     * @return array<int, int>
     */
    private function resolveAccountIds(): array
    {
        $accounts = array_values(array_filter(
            array_map(static fn (mixed $accountId): int => (int) $accountId, $this->option('account')),
            static fn (int $accountId): bool => $accountId > 0
        ));

        if ($accounts !== []) {
            return $accounts;
        }

        $allAccountIds = Account::query()
            ->orderBy('account_id')
            ->pluck('account_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($allAccountIds === []) {
            throw new \InvalidArgumentException('No accounts found. Use --account=<id> or create an account first.');
        }

        return $allAccountIds;
    }

    /**
     * @return array<int, array{ts_account_id: int, ts_timestamp: string, ts_data: string}>
     */
    private function generateForAccount(int $accountId, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
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

        foreach (self::ALERT_TYPES as $index => $type) {
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

        $daily = sin((($ts->hour / 24) * 2 * M_PI) - M_PI_2) * $profile['dailyAmplitude'];
        $weekly = sin((($ts->dayOfWeekIso / 7) * 2 * M_PI) - M_PI_2) * $profile['weeklyAmplitude'];
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
