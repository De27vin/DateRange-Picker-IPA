<?php

namespace App\Services;

use App\Models\Account;
use App\Models\TimeseriesSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeseriesSnapshotCollector
{
    public function collectHourlySnapshots(?CarbonImmutable $tsUtc = null): int
    {
        $snapshotTs = ($tsUtc ?? CarbonImmutable::now('UTC'))->utc()->startOfHour();
        $rows = [];

        foreach (Account::query()->orderBy('account_id')->cursor() as $account) {
            try {
                $rows[] = $this->buildSnapshotRow($account, $snapshotTs);
            } catch (\Throwable $e) {
                Log::error('Timeseries snapshot collection failed.', [
                    'account_id' => $account->account_id,
                    'ts_utc' => $snapshotTs->toIso8601String(),
                    'exception' => $e,
                ]);
            }
        }

        if ($rows === []) {
            return 0;
        }

        TimeseriesSnapshot::query()->upsert(
            $rows,
            ['account_id', 'ts_utc'],
            ['data', 'updated_at']
        );

        return count($rows);
    }

    /**
     * @return array{account_id:int, ts_utc:string, data:string, created_at:string, updated_at:string}
     */
    public function buildSnapshotRow(Account $account, CarbonImmutable $tsUtc): array
    {
        $snapshotTs = $tsUtc->utc()->startOfHour();
        $now = CarbonImmutable::now('UTC')->toDateTimeString();
        $alertCounts = $this->currentAlertCountsForAccount((int) $account->account_id);
        $profile = is_array($account->account_translation) ? $account->account_translation : [];

        $snapshot = [
            'devices' => $this->deviceCounts((int) $account->account_id),
            'alarms' => [
                'inbound_calls' => (int) ($alertCounts['VOICE'] ?? 0),
                'active_alarms' => $this->sumAlertCounts(
                    $alertCounts,
                    array_keys(array_filter($profile['config']['alert']['alarm'] ?? [])),
                    ['ALARM']
                ),
            ],
            'alerts' => [
                'alert_type' => $alertCounts,
            ],
            'service_level' => [
                'periodical_calls' => (int) ($alertCounts['PERIODICAL'] ?? 0),
                'local_checks' => (int) ($alertCounts['TECH'] ?? 0),
            ],
        ];

        return [
            'account_id' => (int) $account->account_id,
            'ts_utc' => $snapshotTs->toDateTimeString(),
            'data' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @return array{enabled:int, disabled:int}
     */
    private function deviceCounts(int $accountId): array
    {
        $counts = DB::table('devices as d')
            ->join('device_sites as ds', 'd.device_ds_id', '=', 'ds.ds_id')
            ->selectRaw('SUM(CASE WHEN d.device_enabled = 1 THEN 1 ELSE 0 END) as enabled_count')
            ->selectRaw('SUM(CASE WHEN d.device_enabled = 0 THEN 1 ELSE 0 END) as disabled_count')
            ->where('ds.ds_account_id', $accountId)
            ->whereNull('ds.ds_deleted')
            ->where(function ($query): void {
                $query->whereNull('d.device_deleted')
                    ->orWhere('d.device_deleted', '=', '0000-00-00 00:00:00');
            })
            ->first();

        return [
            'enabled' => (int) ($counts->enabled_count ?? 0),
            'disabled' => (int) ($counts->disabled_count ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function currentAlertCountsForAccount(int $accountId): array
    {
        $alerts = DB::table('device_alerts as da')
            ->join('devices as d', 'da.da_device_id', '=', 'd.device_id')
            ->join('device_sites as ds', 'd.device_ds_id', '=', 'ds.ds_id')
            ->join('alert_types as at', 'da.da_at_id', '=', 'at.at_id')
            ->leftJoin('modules as dm', 'd.device_module_id', '=', 'dm.module_id')
            ->leftJoin('module_types as dmt', 'dm.module_mt_id', '=', 'dmt.mt_id')
            ->where('ds.ds_account_id', $accountId)
            ->whereNull('ds.ds_deleted')
            ->where('d.device_enabled', 1)
            ->where(function ($query): void {
                $query->whereNull('d.device_deleted')
                    ->orWhere('d.device_deleted', '=', '0000-00-00 00:00:00');
            })
            ->get([
                'at.at_type',
                'da.da_device_id',
                'ds.ds_id as site_id',
                'dmt.mt_type as module_type',
            ]);

        $siteDeviceMap = $this->enabledNonGatewaySiteDeviceMap($accountId);
        $grouped = [];

        foreach ($alerts as $alert) {
            $type = (string) $alert->at_type;
            $deviceIds = strtoupper((string) $alert->module_type) === 'GATEWAY'
                ? ($siteDeviceMap[(int) $alert->site_id] ?? [])
                : [(int) $alert->da_device_id];

            if (!array_key_exists($type, $grouped)) {
                $grouped[$type] = [];
            }

            foreach ($deviceIds as $deviceId) {
                $grouped[$type][$deviceId] = true;
            }
        }

        return collect($grouped)
            ->map(static fn (array $deviceIds): int => count($deviceIds))
            ->all();
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function enabledNonGatewaySiteDeviceMap(int $accountId): array
    {
        $rows = DB::table('devices as d')
            ->join('device_sites as ds', 'd.device_ds_id', '=', 'ds.ds_id')
            ->leftJoin('modules as dm', 'd.device_module_id', '=', 'dm.module_id')
            ->leftJoin('module_types as dmt', 'dm.module_mt_id', '=', 'dmt.mt_id')
            ->where('ds.ds_account_id', $accountId)
            ->whereNull('ds.ds_deleted')
            ->where('d.device_enabled', 1)
            ->where(function ($query): void {
                $query->whereNull('d.device_deleted')
                    ->orWhere('d.device_deleted', '=', '0000-00-00 00:00:00');
            })
            ->where(function ($query): void {
                $query->whereNull('dmt.mt_type')
                    ->orWhere('dmt.mt_type', '!=', 'GATEWAY');
            })
            ->get([
                'ds.ds_id as site_id',
                'd.device_id',
            ]);

        return $rows
            ->groupBy('site_id')
            ->map(static fn (Collection $devices): array => $devices
                ->pluck('device_id')
                ->map(static fn (mixed $deviceId): int => (int) $deviceId)
                ->all())
            ->all();
    }

    /**
     * @param array<string, int> $alertCounts
     * @param array<int, string> $types
     * @param array<int, string> $extraTypes
     */
    private function sumAlertCounts(array $alertCounts, array $types, array $extraTypes = []): int
    {
        $total = 0;

        foreach (array_unique(array_merge($types, $extraTypes)) as $type) {
            $total += (int) ($alertCounts[$type] ?? 0);
        }

        return $total;
    }
}
