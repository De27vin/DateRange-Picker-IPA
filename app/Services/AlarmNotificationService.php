<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceSite;
use App\Models\Session;
use App\Scopes\DevicesByAccountScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;


class AlarmNotificationService
{

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

//    public function __destruct()
//    {
//        $queries = DB::getQueryLog();
//        $totalTime = array_sum(array_column($queries, 'time'));
//        \Log::debug('Query debug in Admin\Accounts', [$totalTime, $queries]);
//    }

    public function getActiveAlarmsForAccount(int $accountId): Collection
    {
        $alarmSessions = $this->findPendingAlarmSessions($accountId);

        Log::debug('AlarmNotificationService - Found pending alarm sessions', [
            'account_id' => $accountId,
            'count' => $alarmSessions->count(),
        ]);

        if ($alarmSessions->isEmpty()) {
            return collect();
        }

        $deviceSiteIds = $this->collectDeviceSiteIds($alarmSessions);
        $deviceSites = $this->loadDeviceSites($accountId, $deviceSiteIds);

        $results = $this->filterBySettingQueue($alarmSessions, $deviceSites);

        Log::info('AlarmNotificationService - Active alarms', [
            'account_id' => $accountId,
            'alarm_count' => $results->count(),
            'checked_sessions' => $alarmSessions->count(),
        ]);

        return $results;
    }

    private function findPendingAlarmSessions(int $accountId): Collection
    {
        return Session::query()
            ->types(['ALARM'])
            ->where('session_account_id', $accountId)
            ->notEnded()
            ->where(function ($query) {
                $query->whereNotNull('session_device_id')
                    ->orWhereHas('sessions', function ($agentQuery) {
                        $agentQuery->types(['AGENT'])
                            ->whereNotNull('session_device_id');
                    });
            })
            ->whereDoesntHave('sessions', function ($agentQuery) {
                $agentQuery->types(['AGENT'])
                    ->whereHas('events', function ($eventQuery) {
                        $eventQuery->whereHas('event_type', function ($typeQuery) {
                            $typeQuery->where('et_type', 'ANSWER');
                        });
                    });
            })
            ->with([
                'device' => function ($query) {
                    $query->withoutGlobalScopes([DevicesByAccountScope::class])
                        ->with('device_site');
                },
                'agentSessions.device' => function ($query) {
                    $query->withoutGlobalScopes([DevicesByAccountScope::class])
                        ->with('device_site');
                },
            ])
            ->get();
    }

    private function loadDeviceSites(int $accountId, Collection $deviceSiteIds): Collection
    {
        return DeviceSite::withoutGlobalScopes()
            ->with(['address.location', 'numbers'])
            ->where('ds_account_id', $accountId)
            ->whereIn('ds_id', $deviceSiteIds)
            ->get()
            ->keyBy('ds_id');
    }

    private function filterBySettingQueue(Collection $alarmSessions, Collection $deviceSites): Collection
    {
        $results = collect();

        foreach ($alarmSessions as $session) {
            $device = $this->resolveDevice($session);
            if (!$device || !$device->device_ds_id) {
                continue;
            }

            $deviceSite = $deviceSites->get($device->device_ds_id);

            if (!$deviceSite) {
                continue;
            }

            $siteSettings = $this->settingsService->getPlainSiteSettings($deviceSite);

            if ($this->isCurrentRouteAgentQueue($session, $siteSettings)) {
                $results->push($this->formatAlarmForDisplay($device, $deviceSite));
            }
        }

        return $results;
    }

    private function isCurrentRouteAgentQueue(Session $session, array $siteSettings): bool
    {
        $endedAgentCount = $session->agentSessions->whereNotNull('session_end')->count();
        $currentRoute    = $endedAgentCount + 1;

        if ($currentRoute > 3) {
            return false;
        }

        $routeDest  = $siteSettings["call.alarm.route{$currentRoute}.dest"]['value']  ?? null;
        $routeTrunk = $siteSettings["call.alarm.route{$currentRoute}.trunk"]['value'] ?? null;

        Log::debug('AlarmNotificationService - route check', [
            'alarm_session_id' => $session->session_id,
            'ended_agents'     => $endedAgentCount,
            'current_route'    => $currentRoute,
            'route_dest'       => $routeDest,
            'route_trunk'      => $routeTrunk,
        ]);

        return $routeDest === 'queue' && in_array($routeTrunk, ['gateway/agent', 'gateway/liftcare_agent']);
    }

    private function formatAlarmForDisplay(Device $device, DeviceSite $deviceSite): array
    {
        return [
            'device_id' => $device->device_id,
            'device_equipment' => $device->device_equipment,
            'device_site' => [
                'ds_name' => $deviceSite->ds_name,
                'address' => [
                    'in_one_line' => $this->formatAddressOneLine($deviceSite),
                ],
                'single_number' => $deviceSite->single_number ?: ['type' => null, 'value' => null],
            ],
        ];
    }

    private function resolveDevice(Session $session): ?Device
    {
        if ($session->device) {
            return $session->device;
        }

        return $session->agentSessions
            ->sortByDesc('session_start')
            ->pluck('device')
            ->filter()
            ->first();
    }

    private function collectDeviceSiteIds(Collection $alarmSessions): Collection
    {
        return $alarmSessions->flatMap(function (Session $session) {
            $ids = [];

            if ($session->device?->device_ds_id) {
                $ids[] = $session->device->device_ds_id;
            }

            foreach ($session->agentSessions as $agentSession) {
                if ($agentSession->device?->device_ds_id) {
                    $ids[] = $agentSession->device->device_ds_id;
                }
            }

            return $ids;
        })->unique()->filter();
    }

    private function formatAddressOneLine(DeviceSite $deviceSite): string
    {
        if (!$deviceSite->address) {
            return '';
        }

        $parts = array_filter([
            $deviceSite->address->address_value ?? '',
            $deviceSite->address->location->location_value ?? '',
            $deviceSite->address->location->location_postcode ?? '',
        ]);

        return trim(implode(' ', $parts));
    }

}
