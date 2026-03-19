<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class TimeseriesResolutionService
{
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
}
