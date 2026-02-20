<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class DayRange
{
    public CarbonImmutable $startHour;
    public CarbonImmutable $endExclusiveHour;

    public function __construct(CarbonImmutable $startDay, CarbonImmutable $endDayInclusive)
    {
        if ($endDayInclusive->lt($startDay)) {
            throw new \InvalidArgumentException('End date must be after or the same as start date.');
        }

        $this->startHour = $startDay->startOfDay();
        $this->endExclusiveHour = $endDayInclusive->addDay()->startOfDay();
    }

    public function totalHours(): int
    {
        return $this->startHour->diffInHours($this->endExclusiveHour);
    }
}