<?php

namespace App\Services;

use App\Helpers\GroupCache;
use App\Models\Account;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class ChartsService
{
    public const SCOPE_DASHBOARD = 'dashboard';
    public const SCOPE_CHARTS = 'charts';
    public const SCOPES = [self::SCOPE_DASHBOARD, self::SCOPE_CHARTS];
    public const DATE_WIDGETS = ['equipment', 'overdues', 'alerts'];
    public const CHARTS = ['equipment', 'alarms', 'alerts', 'serviceLevel'];
    public const RANGE_UNITS = ['days', 'weeks', 'months', 'years'];
    private const RANGE_UNIT_MAX = [
        'days' => 365,
        'weeks' => 52,
        'months' => 12,
        'years' => 1,
    ];

    public const SYSTEM_DEFAULTS = [
        'ranges' => [
            'equipment' => ['amount' => 3, 'unit' => 'months'],
            'overdues' => ['amount' => 3, 'unit' => 'months'],
            'alerts' => ['amount' => 3, 'unit' => 'months'],
        ],
        'serviceThresholds' => [
            'redMax' => 75,
            'orangeMax' => 90,
        ],
    ];

    public const CHARTS_SYSTEM_DEFAULTS = [
        'ranges' => [
            'equipment' => ['amount' => 3, 'unit' => 'months'],
            'alarms' => ['amount' => 3, 'unit' => 'months'],
            'alerts' => ['amount' => 3, 'unit' => 'months'],
            'serviceLevel' => ['amount' => 3, 'unit' => 'months'],
        ],
    ];

    private const SETTINGS = [
        self::SCOPE_DASHBOARD => [
            'accountKey' => 'dashboard_widgets',
            'userKey' => 'dashboard_widgets_users',
            'rangeKeys' => self::DATE_WIDGETS,
            'defaults' => self::SYSTEM_DEFAULTS,
            'serviceThresholds' => true,
        ],
        self::SCOPE_CHARTS => [
            'accountKey' => 'charts_page',
            'userKey' => 'charts_page_users',
            'rangeKeys' => self::CHARTS,
            'defaults' => self::CHARTS_SYSTEM_DEFAULTS,
            'serviceThresholds' => false,
        ],
    ];

    public function __construct(
        private readonly DeviceAlertsService $alertsService,
        private readonly TimeseriesSnapshotCollector $collector,
        private readonly TimeseriesService $timeseries,
        private readonly TimeseriesSnapshotChartMapper $chartMapper,
    ) {
    }

    public function currentStats(): array
    {
        $accountId = (int) session('account.id');
        $account = Account::query()->find($accountId);

        if (!$account instanceof Account) {
            return $this->emptyStats();
        }

        $snapshot = $this->collector->buildSnapshotPayload($account);
        $alertCounts = is_array($snapshot['alerts']['alert_type'] ?? null) ? $snapshot['alerts']['alert_type'] : [];
        $grouping = $this->alertsService->getAlertsGrouping();
        $enabled = (int) ($snapshot['devices']['enabled'] ?? 0);
        $disabled = (int) ($snapshot['devices']['disabled'] ?? 0);
        $periodicalCalls = (int) ($snapshot['service_level']['periodical_calls'] ?? 0);
        $localChecks = (int) ($snapshot['service_level']['local_checks'] ?? 0);
        $critical = $this->sumAlertCounts($alertCounts, $grouping['critical'] ?? []);
        $nonCritical = $this->sumAlertCounts($alertCounts, $grouping['normal'] ?? []);

        $automatedChecks = 0;
        $physicalChecks = 0;

        if ($enabled > 0) {
            $automatedChecks = (int) round(max(0, (($enabled - $periodicalCalls - $critical) / $enabled) * 100));
            $physicalChecks = (int) round(max(0, (($enabled - $localChecks - $nonCritical) / $enabled) * 100));
        }

        return [
            'equipment' => [
                'active' => $enabled,
                'inactive' => $disabled,
            ],
            'alarms' => [
                'inbound_calls' => (int) ($snapshot['alarms']['inbound_calls'] ?? 0),
                'active_alarms' => (int) ($snapshot['alarms']['active_alarms'] ?? 0),
            ],
            'overdues' => [
                'periodic_calls' => $periodicalCalls,
                'local_checks' => $localChecks,
            ],
            'alerts' => [
                'critical' => $critical,
                'non_critical' => $nonCritical,
            ],
            'service_level' => [
                'automated_checks' => $automatedChecks,
                'physical_checks' => $physicalChecks,
            ],
        ];
    }

    public function widgetSeries(string $widget, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $bucketCount = $this->timeseries->suggestedBucketCountForRange($startUtc, $endUtc);

        return match ($widget) {
            'equipment' => [
                'bucket_count' => $bucketCount,
                'data' => $this->timeseries->bucketByLastDatapoint(
                    $this->timeseries->load('EquipmentChart', $startUtc, $endUtc),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['enabled', 'disabled']
                ),
            ],
            'overdues' => [
                'bucket_count' => $bucketCount,
                'data' => $this->timeseries->bucketByLastDatapoint(
                    $this->timeseries->load('ServiceLevelChart', $startUtc, $endUtc),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['periodical_calls', 'local_checks']
                ),
            ],
            'alerts' => [
                'bucket_count' => $bucketCount,
                'data' => $this->timeseries->bucketByLastDatapoint(
                    $this->transformAlertRows($this->timeseries->load('AlertsChart', $startUtc, $endUtc)),
                    $startUtc,
                    $endUtc,
                    $bucketCount,
                    ['critical', 'non_critical']
                ),
            ],
            default => [
                'bucket_count' => $bucketCount,
                'data' => [],
            ],
        };
    }

    public function getAccountDefaults(string $scope = self::SCOPE_DASHBOARD): array
    {
        $config = $this->configFor($scope);
        $profile = $this->getProfileData();
        $settings = is_array($profile[$config['accountKey']] ?? null)
            ? $profile[$config['accountKey']]
            : [];

        return $this->sanitizeSettings($settings, $scope);
    }

    public function getEffectiveDefaults(string $scope = self::SCOPE_DASHBOARD): array
    {
        $accountDefaults = $this->getAccountDefaults($scope);
        $userDefaults = $this->getUserDefaults($scope);

        return $this->sanitizeSettings(array_replace_recursive($accountDefaults, $userDefaults), $scope);
    }

    public function getUserDefaults(string $scope = self::SCOPE_DASHBOARD): array
    {
        $config = $this->configFor($scope);
        $profile = $this->getProfileData();
        $userId = $this->currentUserId();
        $settings = $userId && is_array($profile[$config['userKey']][$userId] ?? null)
            ? $profile[$config['userKey']][$userId]
            : [];

        return $this->sanitizeSettings(array_replace_recursive($this->getAccountDefaults($scope), $settings), $scope);
    }

    public function saveAccountDefaults(array $settings, string $scope = self::SCOPE_DASHBOARD): array
    {
        $config = $this->configFor($scope);
        $profile = $this->getProfileData();
        $profile[$config['accountKey']] = $this->sanitizeSettings($settings, $scope);
        $profile[$config['userKey']] = $this->replaceUserDefaults(
            $profile[$config['userKey']] ?? [],
            $profile[$config['accountKey']]
        );
        $this->saveProfileData($profile);

        return $profile[$config['accountKey']];
    }

    public function saveUserDefaults(array $settings, string $scope = self::SCOPE_DASHBOARD): array
    {
        $config = $this->configFor($scope);
        $profile = $this->getProfileData();
        $userId = $this->currentUserId();
        if (!$userId) {
            return $this->getAccountDefaults($scope);
        }

        $profile[$config['userKey']][$userId] = $this->sanitizeSettings($settings, $scope);
        $this->saveProfileData($profile);

        return $profile[$config['userKey']][$userId];
    }

    public function resetUserDefaults(string $scope = self::SCOPE_DASHBOARD): array
    {
        $config = $this->configFor($scope);
        $profile = $this->getProfileData();
        $userId = $this->currentUserId();
        if (!$userId) {
            return $this->getAccountDefaults($scope);
        }

        unset($profile[$config['userKey']][$userId]);
        $this->saveProfileData($profile);

        return $this->getAccountDefaults($scope);
    }

    public function sanitizeSettings(array $settings, string $scope = self::SCOPE_DASHBOARD): array
    {
        $config = $this->configFor($scope);
        $defaults = $config['defaults'];
        $ranges = [];
        foreach ($config['rangeKeys'] as $rangeKey) {
            $ranges[$rangeKey] = $this->sanitizeRange(
                $settings['ranges'][$rangeKey] ?? null,
                $defaults['ranges'][$rangeKey]
            );
        }

        $sanitized = ['ranges' => $ranges];
        if ($config['serviceThresholds']) {
            $sanitized['serviceThresholds'] = $this->sanitizeServiceThresholds($settings['serviceThresholds'] ?? []);
        }

        return $sanitized;
    }

    private function emptyStats(): array
    {
        return [
            'equipment' => ['active' => 0, 'inactive' => 0],
            'alarms' => ['inbound_calls' => 0, 'active_alarms' => 0],
            'overdues' => ['periodic_calls' => 0, 'local_checks' => 0],
            'alerts' => ['critical' => 0, 'non_critical' => 0],
            'service_level' => ['automated_checks' => 0, 'physical_checks' => 0],
        ];
    }

    /**
     * @param array<string, int> $alertCounts
     * @param array<int, string> $types
     */
    private function sumAlertCounts(array $alertCounts, array $types): int
    {
        $total = 0;

        foreach ($types as $type) {
            $total += (int) ($alertCounts[$type] ?? 0);
        }

        return $total;
    }

    private function transformAlertRows(array $rows): array
    {
        $criticalTypes = array_flip($this->alertsService->getAlertsGrouping()['critical'] ?? []);
        $normalTypes = array_flip($this->alertsService->getAlertsGrouping()['normal'] ?? []);

        return array_map(function (array $row) use ($criticalTypes, $normalTypes): array {
            $critical = 0;
            $nonCritical = 0;

            foreach (($row['series'] ?? []) as $seriesKey => $value) {
                $alertType = is_string($seriesKey) ? $this->chartMapper->alertTypeCodeForSeriesKey($seriesKey) : null;
                if ($alertType === null) {
                    continue;
                }

                if (isset($criticalTypes[$alertType])) {
                    $critical += (int) $value;
                }

                if (isset($normalTypes[$alertType])) {
                    $nonCritical += (int) $value;
                }
            }

            return [
                'ts' => $row['ts'] ?? null,
                'series' => [
                    'critical' => $critical,
                    'non_critical' => $nonCritical,
                ],
            ];
        }, $rows);
    }

    private function configFor(string $scope): array
    {
        return self::SETTINGS[$scope] ?? self::SETTINGS[self::SCOPE_DASHBOARD];
    }

    private function sanitizeRange(mixed $range, array $fallback): array
    {
        if (!is_array($range)) {
            return $fallback;
        }

        $amount = (int) ($range['amount'] ?? $fallback['amount']);
        $unit = (string) ($range['unit'] ?? $fallback['unit']);

        if ($amount < 1) {
            $amount = $fallback['amount'];
        }

        if (!in_array($unit, self::RANGE_UNITS, true)) {
            $unit = $fallback['unit'];
        }

        $amount = min($amount, self::RANGE_UNIT_MAX[$unit]);

        return [
            'amount' => $amount,
            'unit' => $unit,
        ];
    }

    private function sanitizeServiceThresholds(mixed $thresholds): array
    {
        if (!is_array($thresholds)) {
            return self::SYSTEM_DEFAULTS['serviceThresholds'];
        }

        $redMax = (int) ($thresholds['redMax'] ?? self::SYSTEM_DEFAULTS['serviceThresholds']['redMax']);
        $orangeMax = (int) ($thresholds['orangeMax'] ?? self::SYSTEM_DEFAULTS['serviceThresholds']['orangeMax']);

        $redMax = max(0, min(100, $redMax));
        $orangeMax = max($redMax, min(100, $orangeMax));

        return [
            'redMax' => $redMax,
            'orangeMax' => $orangeMax,
        ];
    }

    private function replaceUserDefaults(mixed $userDefaults, array $settings): array
    {
        if (!is_array($userDefaults)) {
            return [];
        }

        return array_fill_keys(array_keys($userDefaults), $settings);
    }

    private function getProfileData(): array
    {
        $accountId = session('account.id');
        if (empty($accountId)) {
            return [];
        }

        $profile = Account::query()
            ->where('account_id', $accountId)
            ->first()
            ?->account_translation;

        return is_array($profile) ? $profile : [];
    }

    private function saveProfileData(array $profile): void
    {
        $accountId = session('account.id');
        if (empty($accountId)) {
            return;
        }

        $account = Account::query()
            ->where('account_id', $accountId)
            ->first();

        if (!$account) {
            return;
        }

        $account->account_translation = $profile;
        $account->save();

        GroupCache::forgetGroup('profile_data');
    }

    private function currentUserId(): ?string
    {
        $userId = Auth::user()?->user_id;

        return $userId ? (string) $userId : null;
    }
}
