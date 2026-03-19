<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class TimeseriesDataService
{
    public function __construct(
        private readonly DummyTimeseriesGenerator $generator,
        private readonly TimeseriesResolutionService $resolutionService,
        private readonly TimeseriesAggregatorService $aggregatorService,
    ) {
    }

    /**
     * @return array{resolution: string, data: array<int, array{ts: string, value: int}>}
     */
    public function fetch(string $chart, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $rawData = $this->generator->generate($chart, $startUtc->startOfDay(), $endUtc->startOfDay());
        $resolution = $this->resolutionService->forRange($startUtc, $endUtc);
        $data = $this->aggregatorService->aggregate($rawData, $startUtc, $endUtc, $resolution);

        return [
            'resolution' => $resolution,
            'data' => $data,
        ];
    }
}
