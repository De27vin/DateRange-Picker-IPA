<?php

namespace App\Services;

use App\Helpers\GroupCache;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

class DashboardWidgetSettingsService
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
