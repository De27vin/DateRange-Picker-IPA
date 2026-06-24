<?php

namespace App\Console\Commands;

use App\Services\TimeseriesSnapshotCollector;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CollectTimeseriesSnapshots extends Command
{
    protected $signature = 'timeseries:collect {--ts= : UTC timestamp to collect for, rounded down to the hour}';
    protected $description = 'Collect changed hourly timeseries snapshots from live database data';

    public function __construct(
        private readonly TimeseriesSnapshotCollector $collector,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tsUtc = $this->resolveTimestamp();
        $written = $this->collector->collectHourlySnapshots($tsUtc);

        $this->info(sprintf(
            'Stored %d changed account snapshots for %s.',
            $written,
            $tsUtc->toIso8601String()
        ));

        return self::SUCCESS;
    }

    private function resolveTimestamp(): CarbonImmutable
    {
        $ts = $this->option('ts');

        if (is_string($ts) && $ts !== '') {
            try {
                return CarbonImmutable::parse($ts, 'UTC')->utc()->startOfHour();
            } catch (\Exception) {
                $this->error("Invalid timestamp: {$ts}");
                exit(self::FAILURE);
            }
        }

        return CarbonImmutable::now('UTC')->startOfHour();
    }
}
