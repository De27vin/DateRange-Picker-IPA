<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\TimeseriesSnapshot;
use App\Services\RealisticTimeseriesGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateTimeseriesData extends Command
{
    protected $signature = 'timeseries:generate
        {--start= : UTC start timestamp, default is now minus one year at the start of the hour}
        {--end= : UTC end timestamp, default is now at the start of the hour}
        {--account=* : Limit generation to one or more account ids}
        {--truncate : Delete existing snapshot rows before generating}';
    protected $description = 'Generate one year of realistic hourly UTC snapshot data in the database';

    public function __construct(
        private readonly RealisticTimeseriesGenerator $generator,
    ) {
        parent::__construct();
    }

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
            foreach (array_chunk($this->generator->generateForAccount($accountId, $startUtc, $endUtc), 500) as $chunk) {
                TimeseriesSnapshot::query()->upsert($chunk, ['account_id', 'ts_utc'], ['data', 'updated_at']);
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
}
