<?php

namespace App\Services;

use App\Helpers\GroupCache;
use App\Models\Account;

class DashboardWidgetSettingsService
{
    public const DATE_WIDGETS = ['equipment', 'overdues', 'alerts'];
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

    public function getAccountDefaults(): array
    {
        $profile = $this->getProfileData();
        $settings = is_array($profile['dashboard_widgets'] ?? null)
            ? $profile['dashboard_widgets']
            : [];

        return $this->sanitizeSettings($settings);
    }

    public function saveAccountDefaults(array $settings): array
    {
        $profile = $this->getProfileData();
        $profile['dashboard_widgets'] = $this->sanitizeSettings($settings);
        $this->saveProfileData($profile);

        return $profile['dashboard_widgets'];
    }

    public function sanitizeSettings(array $settings): array
    {
        $ranges = [];
        foreach (self::DATE_WIDGETS as $widget) {
            $ranges[$widget] = $this->sanitizeRange(
                $settings['ranges'][$widget] ?? null,
                self::SYSTEM_DEFAULTS['ranges'][$widget]
            );
        }

        return [
            'ranges' => $ranges,
            'serviceThresholds' => $this->sanitizeServiceThresholds($settings['serviceThresholds'] ?? []),
        ];
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
}
