<?php

namespace App\Services;

use App\Models\AlertType;
use App\Models\DeviceAlert;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DeviceAlertsService
{
    use TranslationsTrait;

    public function getAlertsGrouping(): array
    {
        $visibleAlerts = array_filter($this->getAlertTypeDisplayStates());
        $visibleAlerts = array_merge($visibleAlerts, ['ALARM' => true, 'PERIODICAL' => true]);
        ksort($visibleAlerts);

        $criticalAlerts = array_filter($this->getAlertCriticalityStates());
        $criticalAlerts = array_merge($criticalAlerts, ['ALARM' => true, 'PERIODICAL' => true]);
        ksort($criticalAlerts);

        $alarmingAlerts = array_filter($this->getAlertAlarmalityStates());
        $alarmingAlerts = array_merge($alarmingAlerts, ['ALARM' => true]);
        ksort($alarmingAlerts);

        $normalAlerts = array_diff_key($visibleAlerts, $criticalAlerts);
        ksort($normalAlerts);

        return [
            'all' => array_keys($this->getAlertTypeDisplayStates()),
            'visible' => array_keys($visibleAlerts),
            'hidden' => array_keys(array_diff_key($this->getAlertTypeDisplayStates(), $criticalAlerts, $normalAlerts)),
            'critical' => array_keys($criticalAlerts),
            'alarming' => array_keys($alarmingAlerts),
            'normal' => array_keys($normalAlerts),
            'focus' => ['ALARM', 'PERIODICAL'],
        ];
    }

    public function getGroupedAlertsCounts(int $accountId)
    {
        $alertsGrouping = $this->getAlertsGrouping();
        $allAlertsCount = $this->getAllAlertCounts($accountId);

        $groupedCounts = [];
        foreach ($alertsGrouping as $group => $alerts) {
            $groupedCounts[$group] = array_intersect_key($allAlertsCount, array_flip($alerts));
            ksort($groupedCounts[$group]);
        }
        return $groupedCounts;
    }

    public function getAllAlertCounts(int $accountId)
    {
        $allAlertTypes = AlertType::all()->pluck('at_type')->toArray();
        $alertsCount = $this->getAlertCountsForTypes($accountId, $allAlertTypes);
        $zeroForInit = array_fill_keys($allAlertTypes,0);

        return array_merge($zeroForInit, $alertsCount);
    }

public function getAlertCountsForTypes(int $accountId, array $alertTypes)
{
    $alerts = DeviceAlert::query()
        ->select('device_alerts.*')
        ->with(['device.module.module_type', 'device.device_site.devices.module.module_type', 'alert_type'])
        ->whereHas('device', function($q) use ($accountId) {
            $q->enabled()->whereHas('device_site', function($qq) use ($accountId) {
                $qq->where('ds_account_id', '=', $accountId);
            });
        })
        ->whereHas('alert_type', function($q) use ($alertTypes) {
            $q->whereIn('at_type', $alertTypes);
        })
        ->get();

    $resultCounts = collect();

    foreach ($alertTypes as $alertType) {
        $alertsOfType = $alerts->filter(fn($alert) => $alert->alert_type->at_type === $alertType);
        $affectedDeviceIds = collect();

        foreach ($alertsOfType as $alert) {
            if ($alert->device->module?->module_type?->mt_type === 'GATEWAY') {
                $siteDevices = $alert->device->device_site->devices
                    ->reject(fn($device) => $device->module?->module_type?->mt_type === 'GATEWAY')
                    ->pluck('device_id');
                $affectedDeviceIds = $affectedDeviceIds->concat($siteDevices);
            } else {
                $affectedDeviceIds->push($alert->da_device_id);
            }
        }

        $resultCounts[$alertType] = $affectedDeviceIds->unique()->count();
    }

    return $resultCounts->toArray();
}

    /**
     * improved performance and ignoring multiple alerts of the same type
     */
    public function getAlertCountsForTypesNew(int $accountId, array $alertTypes)
    {
        return DeviceAlert::query()
            ->select('alert_types.at_type', DB::raw('COUNT(distinct device_alerts.da_device_id) as device_count'))
            ->join('devices', 'device_alerts.da_device_id', '=', 'devices.device_id')
            ->join('device_sites', 'devices.device_ds_id', '=', 'device_sites.ds_id')
            ->join('alert_types', 'device_alerts.da_at_id', '=', 'alert_types.at_id')
            ->where('device_sites.ds_account_id', '=', $accountId)
            ->where('device_sites.ds_deleted', '=', null)
            ->whereIn('alert_types.at_type', $alertTypes)
            ->where('devices.device_enabled', '=', true)
            ->where('devices.device_deleted', '=', '0000-00-00 00:00:00')
            ->groupBy('alert_types.at_type')
            ->get()
            ->pluck('device_count', 'at_type')
            ->toArray();
    }

    public function getCurrentAlertsForAccount(int $accountId)
    {
        return DB::table('device_alerts as da')
            ->join('sessions as s', 'da.da_session_id', '=', 's.session_id')
            ->join('devices as d', 'da.da_device_id', '=', 'd.device_id')
            ->join('alert_types as at', 'da.da_at_id', '=', 'at.at_id')
            ->where('d.device_account_id', $accountId)
            ->where('s.session_account_id', $accountId)
            ->select(
                'da.da_id',
                'at.at_type',
                'da.da_value',
                's.session_uuid',
                'd.device_equipment',
                'da.da_timestamp'
            )->get();
    }
}