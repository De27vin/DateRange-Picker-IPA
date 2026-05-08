<?php

namespace App\Services;

use App\Helpers\GroupCache;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

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

    public function getEffectiveDefaults(): array
    {
        $accountDefaults = $this->getAccountDefaults();
        $userDefaults = $this->getUserDefaults();

        return $this->sanitizeSettings(array_replace_recursive($accountDefaults, $userDefaults));
    }

    public function getUserDefaults(): array
    {
        $profile = $this->getProfileData();
        $userId = $this->currentUserId();
        $settings = $userId && is_array($profile['charts_page_users'][$userId] ?? null)
            ? $profile['charts_page_users'][$userId]
            : [];

        return $this->sanitizeSettings(array_replace_recursive($this->getAccountDefaults(), $settings));
    }

    public function saveAccountDefaults(array $settings): array
    {
        $profile = $this->getProfileData();
        $profile['charts_page'] = $this->sanitizeSettings($settings);
        $profile['charts_page_users'] = $this->replaceUserDefaults(
            $profile['charts_page_users'] ?? [],
            $profile['charts_page']
        );
        $this->saveProfileData($profile);

        return $profile['charts_page'];
    }

    public function saveUserDefaults(array $settings): array
    {
        $profile = $this->getProfileData();
        $userId = $this->currentUserId();
        if (!$userId) {
            return $this->getAccountDefaults();
        }

        $profile['charts_page_users'][$userId] = $this->sanitizeSettings($settings);
        $this->saveProfileData($profile);

        return $profile['charts_page_users'][$userId];
    }

    public function resetUserDefaults(): array
    {
        $profile = $this->getProfileData();
        $userId = $this->currentUserId();
        if (!$userId) {
            return $this->getAccountDefaults();
        }

        unset($profile['charts_page_users'][$userId]);
        $this->saveProfileData($profile);

        return $this->getAccountDefaults();
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
