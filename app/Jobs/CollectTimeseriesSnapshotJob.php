<?php

namespace App\Jobs;

use App\Services\TimeseriesSnapshotCollector;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CollectTimeseriesSnapshotJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(TimeseriesSnapshotCollector $collector): void
    {
        $collector->collectHourlySnapshots(CarbonImmutable::now('UTC')->startOfHour());
    }
}
