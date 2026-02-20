<?php

namespace App\Traits;

use App\Helpers\GroupCache;
use App\Models\Account;
use App\Models\AccountSetting;
use App\Models\AlertType;
use App\Models\DeviceSetting;
use App\Models\ModuleSetting;
use App\Models\Language;
use App\Models\Module;
use App\Models\Setting;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

trait TranslationsTrait
{
    private array $profileData = [];

    public function getProfileData()
    {
        if (!empty($this->profileData)) {
            return $this->profileData;
        }
        $accountId = session('account.id');
        $cacheKey = __CLASS__ . __METHOD__ . $accountId;
        $this->profileData = GroupCache::rememberForever('profile_data', $cacheKey, function() use ($accountId) {
            return Account::query()->where('account_id', '=', $accountId)->first()?->account_translation;
        });
        return $this->profileData;
    }

    public function saveProfileData($profile)
    {
        $accountId = session('account.id');
        $updatedAccount = Account::query()->where('account_id', '=', $accountId)->first();
        $updatedAccount->account_translation = $profile;
        $updatedAccount->save();

        $this->profileData = $profile;
        GroupCache::forgetGroup('profile_data');
    }

    public function getTranslations(array $subjectsWithPrefixes = []): array
    {
        $subjectsWithPaths = [
            'settings' => ['device', 'setting'],
            'initial' => ['device', 'initial'],
            'call' => ['call'],
            'form' => ['device', 'field'],
            'alert' => ['alert', 'type'],
        ];

        $profileData = $this->getProfileData();
        $languages = array_keys($profileData['languages']);
        array_unshift($languages, 'default');
        $translationsBuilder = $this->getTranslationsBuilder();
        $requestedSubjects = !empty($subjectsWithPrefixes) ?
            array_intersect_key($subjectsWithPaths, $subjectsWithPrefixes) :
            $subjectsWithPaths;

        $outputTranslations = [];
        foreach ($requestedSubjects as $subject => $path) {
            foreach ($languages as $lang) {
                $languageTranslations = $this->arrayAccessor($path, $profileData['translations'][$lang]);
                $languageTranslations = $translationsBuilder($languageTranslations);
                foreach (array_keys($languageTranslations) as $translationKey) {
                    $prefix = $subjectsWithPrefixes[$subject] ?? '';
                    $outputKey = !empty($prefix) ? $prefix.'_'.$translationKey : $translationKey;
                    $outputTranslations[$outputKey][$lang] = $languageTranslations[$translationKey];
                }
            }
        }

        return $outputTranslations;
    }

    public function getAlertTypeDisplayStates()
    {
        return $this->getProfileData()['config']['alert']['display'] ?? [];
    }

    public function getAlertCriticalityStates()
    {
        return $this->getProfileData()['config']['alert']['critical'] ?? [];
    }

    public function getAlertAlarmalityStates()
    {
        return $this->getProfileData()['config']['alert']['alarm'] ?? [];
    }

    public function getModules()
    {
        return Module::whereHas('module_type', function ($query) {
                $query->where('module_types.mt_type','!=','EVENT');
              })
            ->with('module_type')
            ->where('module_name','!=','SYSTEM')
            ->orderBy('module_id')
            ->pluck('module_name', 'module_id')
            ->toArray();
    }

    private function arrayAccessor($path, $array)
    {
        foreach($path as $key) {
            $array = $array ? ($array[$key] ?? null) : null;
        }
        return $array;
    }

    private function getTranslationsBuilder(): Closure
    {
        $translationsBuilder = function ($inputTranslations, $prefixKey = null) use (&$translationsBuilder) {
            $outputKeyValues = [];
            foreach ($inputTranslations as $key => $value) {
                $newKey = empty($prefixKey) ? $key : ($prefixKey.'_'.$key);
                if (is_array($value)) {
                    $outputKeyValues = array_merge($outputKeyValues, $translationsBuilder($value, $newKey));
                } else {
                    $outputKeyValues[$newKey] = $value;
                }
            }
            return $outputKeyValues;
        };

        return $translationsBuilder;
    }

    private function getSettingTranslations(string $locale = 'default', string $prefix = ''): array
    {
        $outputTranslations = [];
        $settingTranslations = $this->getTranslations(['settings' => $prefix]);

        foreach ($settingTranslations as $field => $translations) {
            if (!empty($translations[$locale])) {
                $outputTranslations[$field] = $translations[$locale];
            } elseif (!empty($translations['default'])) {
                $outputTranslations[$field] = $translations['default'];
            } else {
                $outputTranslations[$field] = __('settings.label.device_'.$field);
            }
        }
        return $outputTranslations;
    }
    private function getFieldTranslations(string $locale = 'default', ?string $prefix = null): array
    {
        $outputTranslations = [];
        $formTranslations = $this->getTranslations(['form' => $prefix]);

        foreach ($formTranslations as $field => $translations) {
            if (!empty($translations[$locale])) {
                $outputTranslations[$field] = $translations[$locale];
            } elseif (!empty($translations['default'])) {
                $outputTranslations[$field] = $translations['default'];
            } else {
                $outputTranslations[$field] = __('settings.label.device_field_'.Str::after($field, $prefix.'_'));
            }
        }
        return $outputTranslations;
    }

    private function getAlertTranslations(string $locale = 'default'): array
    {
        $outputTranslations = [];
        $alertTranslations = $this->getTranslations(['alert' => '']);

        foreach ($alertTranslations as $field => $translations) {
            if (!empty($translations[$locale])) {
                $outputTranslations[$field] = $translations[$locale];
            } elseif (!empty($translations['default'])) {
                $outputTranslations[$field] = $translations['default'];
            } else {
                $outputTranslations[$field] = __($field);
            }
        }
        return $outputTranslations;
    }

    public function getSettingDefaultTranslation(string $setting) : string
    {
        $translation = __('settings.label.'.$setting);
        if ($translation !== 'settings.label.'.$setting) {
            return $translation;
        }
        $translation = __('settings.label.device_'.$setting);
        if ($translation !== 'settings.label.device_'.$setting) {
            return $translation;
        }
        return $setting;
    }
}
