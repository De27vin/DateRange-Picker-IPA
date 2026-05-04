<?php

namespace App\Services;

use App\Helpers\GroupCache;
use App\Models\Account;

class ChartsSettingsService
{
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
            'alarms' => ['amount' => 3, 'unit' => 'months'],
            'alerts' => ['amount' => 3, 'unit' => 'months'],
            'serviceLevel' => ['amount' => 3, 'unit' => 'months'],
        ],
    ];

    public function getAccountDefaults(): array
    {
        $profile = $this->getProfileData();
        $settings = is_array($profile['charts_page'] ?? null)
            ? $profile['charts_page']
            : [];

        return $this->sanitizeSettings($settings);
    }

    public function saveAccountDefaults(array $settings): array
    {
        $profile = $this->getProfileData();
        $profile['charts_page'] = $this->sanitizeSettings($settings);
        $this->saveProfileData($profile);

        return $profile['charts_page'];
    }

    public function sanitizeSettings(array $settings): array
    {
        $ranges = [];
        foreach (self::CHARTS as $chart) {
            $ranges[$chart] = $this->sanitizeRange(
                $settings['ranges'][$chart] ?? null,
                self::SYSTEM_DEFAULTS['ranges'][$chart]
            );
        }

        return ['ranges' => $ranges];
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
