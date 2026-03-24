<?php

namespace App\Console\Commands;

use App\Models\TimeseriesPoint;
use App\Services\RealisticTimeseriesGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateTimeseriesData extends Command
{
    protected $signature = 'timeseries:generate
        {--start= : UTC start timestamp, default is now minus one year at the start of the hour}
        {--end= : UTC end timestamp, default is now at the start of the hour}
        {--chart=* : Limit generation to one or more supported chart names}
        {--truncate : Delete existing timeseries rows before generating}';
    protected $description = 'Generate one year of realistic hourly UTC time-series test data in the database';

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
            $charts = $this->resolveCharts();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        if ($endUtc->lt($startUtc)) {
            $this->error('The end option must be after or equal to start.');
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            TimeseriesPoint::query()->delete();
            $this->info('Deleted existing timeseries data.');
        }

        foreach ($charts as $chart) {
            foreach (array_chunk($this->generator->generate($chart, $startUtc, $endUtc), 500) as $chunk) {
                TimeseriesPoint::query()->upsert($chunk, ['chart', 'ts_utc'], ['value', 'updated_at']);
            }

            $this->line(sprintf(
                '%s: %d hourly points generated from %s to %s',
                $chart,
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
     * @return array<int, string>
     */
    private function resolveCharts(): array
    {
        $charts = array_values(array_filter($this->option('chart'), static fn (mixed $chart): bool => is_string($chart) && $chart !== ''));
        if ($charts === []) {
            return $this->generator->supportedCharts();
        }

        $supported = $this->generator->supportedCharts();
        $invalid = array_values(array_diff($charts, $supported));

        if ($invalid !== []) {
            throw new \InvalidArgumentException('Unsupported chart option(s): ' . implode(', ', $invalid));
        }

        return $charts;
    }
}
