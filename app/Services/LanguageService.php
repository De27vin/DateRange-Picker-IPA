<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Language;
use App\Models\Locale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LanguageService
{
    public function getAvailableLocales(): array
    {
        return Locale::query()
            ->where('locale_parent_id', '=', null)
            ->get()
            ->pluck('locale_id', 'locale_code')
            ->mapWithKeys(function ($key, $item) {
                $languageCode = Str::before($item, '_');
                return [$languageCode => ['id' => $key, 'code' => $languageCode]];
            })
            ->unique('code')
            ->toArray();
    }

    public function getUserLanguage(?User $user = null, ?Account $account = null): string
    {
        $user = $user ?? Auth::user();
        $account = $account ?? app(UserContextService::class)->getCurrentAccount();

        if (!$account) {
            return 'en';
        }

        $accountLanguage = $this->resolveAccountLanguage($account);

        if ($user && $user->user_locale_id != null) {
            return $this->resolveUserLanguage($user);
        }

        return $accountLanguage;
    }

    public function switchUserLanguage(User $user, string $langCode): bool
    {
        $availableLocales = $this->getAvailableLocales();

        if (!array_key_exists($langCode, $availableLocales)) {
            return false;
        }

        $localeId = $availableLocales[$langCode]['id'];

        $affected = DB::table('users')
            ->where('user_id', '=', $user->user_id)
            ->update(['user_locale_id' => $localeId]);

        session(['locale' => $langCode]);

        return true;
    }

    public function getEnabledLanguages(): array
    {
        return Language::query()
            ->where('language_enabled', '=', true)
            ->pluck('language_code')
            ->toArray();
    }

    private function resolveAccountLanguage(Account $account): string
    {
        if ($account->account_locale_id != null) {
            $accountLocale = Locale::with('language')
                ->where('locale_parent_id', '=', null)
                ->where('locale_id', '=', $account->account_locale_id)
                ->first();

            $accountLanguage = $accountLocale?->language?->language_code;

            if ($accountLanguage) {
                return $accountLanguage;
            }
        }

        $availableLocales = $this->getAvailableLocales();
        if (array_key_exists('en', $availableLocales)) {
            $account->account_locale_id = $availableLocales['en']['id'];
            $account->save();
        }

        return 'en';
    }

    private function resolveUserLanguage(User $user): string
    {
        $userLocale = Locale::with('language')
            ->where('locale_parent_id', '=', null)
            ->where('locale_id', '=', $user->user_locale_id)
            ->first();

        return $userLocale?->language?->language_code ?? 'en';
    }
}
