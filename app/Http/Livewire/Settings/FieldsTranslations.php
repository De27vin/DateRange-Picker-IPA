<?php

namespace App\Http\Livewire\Settings;

use App\Helpers\GroupCache;
use App\Models\AlertType;
use App\Models\Language;
use App\Traits\TranslationsTrait;
use Livewire\Component;
use App\Traits\DeviceFormTrait;
use Illuminate\Support\Str;
use App\Traits\TrimInputs;

class FieldsTranslations extends Component
{
    use DeviceFormTrait;
    use TranslationsTrait;
    use TrimInputs;

    public $breadcrumb = ['UCP', 'Settings', 'Devices'];
    public $visibility;
    public $showEditModal = false;
    public $module = null;
    public $option;
    public $deviceTypeId;
    public $deviceFieldEdit;

    // PORTING TO TEST - START
    public array $deviceFieldsTranslations = [];
    public array $deviceSettingsTranslations = [];
    // PORTING TO TEST - END

    /////////////////////// ALERT TYPES /////////////////////
    public $alertTypes;
    public $criticality;
    public $alarmality;
    public array $alertTranslations = [];
    public $alertLabelsTranslations;
    public $locale;
    public $languages;
    public $canWriteSettings = false;
    public $warningAlertTypes;
    public $errorAlertTypes;

    public $languagePriority = ['en', 'de', 'es', 'fr', 'it', 'pt'];

    protected $listeners = [
        'updateOption',
        'cancelSettings'
    ];

    protected function getListeners()
    {
        return ['toggleVisibility' => 'onToggleVisibility'];
    }

    public function mount()
    {
        $this->prepareDeviceFormData(true);
        $this->selectedDeviceType = null;
        $this->deviceFormFieldSettings = [];

        $accountId = session('account.id');

        $cacheKeyFields = __CLASS__.__METHOD__.'_deviceFields_'.$accountId;
        $this->deviceFieldsTranslations = GroupCache::rememberForever('profile_data', $cacheKeyFields, function() {
            return $this->getTranslations(['form' => 'device_field']);
        });

        $cacheKeySettings = __CLASS__.__METHOD__.'_deviceSettings_'.$accountId;
        $this->deviceSettingsTranslations = GroupCache::rememberForever('profile_data', $cacheKeySettings, function() {
            return $this->getTranslations(['settings' => '']);
        });

        $this->option = 0;

        // Alert types
        $this->locale = session('locale', 'default');
        $this->languages = Language::where('language_enabled', '=', true)
            ->get()
            ->pluck('language_code')
            ->all();
        $this->prepareAlertTranslationData();
    }

    public function updateFieldTranslations()
    {
        if (empty($this->deviceFieldsTranslations)) {
            return;
        }

        $this->deviceFieldsTranslations = $this->trimStringsInArrayRecursively($this->deviceFieldsTranslations);
        $profileData = $this->getProfileData();

        foreach ($this->deviceFieldsTranslations as $field => $translations) {
            // Remove the prefix if present
            $fieldKey = Str::after($field, 'device_field_');

            $defaultLang = $this->determineDefaultLanguage($translations);
            if ($defaultLang) {
                $profileData['translations']['default']['device']['field'][$fieldKey] = $translations[$defaultLang];
            }
            foreach ($translations as $lang => $value) {
                if ($lang !== 'default') {
                    $profileData['translations'][$lang]['device']['field'][$fieldKey] = $value;
                }
            }
        }

        $this->saveProfileData($profileData);
        GroupCache::forgetGroup('profile_data');
        GroupCache::forgetGroup('translations');

        $this->notify('success', __('Values for device form fields updated'));
    }

    public function updateSettingTranslations()
    {
        if (empty($this->deviceSettingsTranslations)) {
            return;
        }

        $this->deviceSettingsTranslations = $this->trimStringsInArrayRecursively($this->deviceSettingsTranslations);
        $profileData = $this->getProfileData();

        foreach ($this->deviceSettingsTranslations as $field => $translations) {
            $translationPath = explode('_', $field);

            $defaultLang = $this->determineDefaultLanguage($translations);
            if ($defaultLang) {
                $this->setNestedValue($profileData['translations']['default']['device']['setting'], $translationPath, $translations[$defaultLang]);
            }
            foreach ($translations as $langCode => $value) {
                if ($langCode !== 'default') {
                    $this->setNestedValue($profileData['translations'][$langCode]['device']['setting'], $translationPath, $value);
                }
            }
        }

        $this->saveProfileData($profileData);
        GroupCache::forgetGroup('profile_data');
        GroupCache::forgetGroup('translations');

        $this->notify('success', __('Values for device settings updated'));
    }

    private function setNestedValue(&$array, $keys, $value)
    {
        $currentArray = &$array;
        foreach ($keys as $key) {
            if (!isset($currentArray[$key]) || !is_array($currentArray[$key])) {
                $currentArray[$key] = [];
            }
            $currentArray = &$currentArray[$key];
        }
        $currentArray = $value;
    }

    public function render()
    {
        return view('livewire.settings.field-translations');
    }

    ////////////////////////// ALERT TYPES //////////////////////////
    public function prepareAlertTranslationData()
    {
        $accountId = session('account.id');

        $cacheKeyAlert = __CLASS__ . __METHOD__ . '_alert_' . $accountId . '_' . $this->locale;
        $this->alertTranslations = GroupCache::rememberForever('profile_data', $cacheKeyAlert, function() {
            return $this->getTranslations(['alert' => '']);
        });

        $cacheKeyAlertLabels = __CLASS__ . __METHOD__ . '_alertLabels_' . $accountId . '_' . $this->locale;
        $this->alertLabelsTranslations = GroupCache::rememberForever('profile_data', $cacheKeyAlertLabels, function() {
            return $this->getAlertTranslations($this->locale);
        });

        $this->visibility  = $this->getAlertTypeDisplayStates();
        $this->criticality = $this->getAlertCriticalityStates();
        $this->alarmality  = $this->getAlertAlarmalityStates();
        $this->warningAlertTypes = AlertType::warnings()->get()->pluck('at_type')->toArray();
        $this->errorAlertTypes = AlertType::errors()->get()->pluck('at_type')->toArray();
    }

    public function updateAlertTypes()
    {
        $this->alertTranslations = $this->trimStringsInArrayRecursively($this->alertTranslations);
        $profileData = $this->getProfileData();

        foreach ($this->alertTranslations as $alertType => $translations) {
            $defaultLang = $this->determineDefaultLanguage($translations);
            if ($defaultLang) {
                $profileData['translations']['default']['alert']['type'][$alertType] = $translations[$defaultLang];
            }
            foreach ($translations as $langCode => $value) {
                if ($langCode !== 'default') {
                    $profileData['translations'][$langCode]['alert']['type'][$alertType] = $value;
                }
            }
        }

        foreach ($this->visibility as $alertType => $stateBool) {
            $profileData['config']['alert']['display'][$alertType] = $stateBool;
        }
        foreach ($this->criticality as $alertType => $stateBool) {
            $profileData['config']['alert']['critical'][$alertType] = $stateBool;
        }
        foreach ($this->alarmality as $alertType => $stateBool) {
            $profileData['config']['alert']['alarm'][$alertType] = $stateBool;
        }

        $this->saveProfileData($profileData);
        GroupCache::forgetGroup('profile_data');
        GroupCache::forgetGroup('translations');

        $this->notify('success', __('Alert Types updated'));
        $this->prepareAlertTranslationData();
    }

    public function toggleVisibility($alertType)
    {
        if (empty($this->visibility[$alertType])) {
            $this->visibility[$alertType] = true;
        } else {
            $this->visibility[$alertType] = false;
            $this->criticality[$alertType] = false;
            $this->alarmality[$alertType] = false;
        }
        $this->updateAlertTypes();
    }

    public function toggleCriticality($alertType)
    {
        if (empty($this->criticality[$alertType])) {
            $this->criticality[$alertType] = true;
            $this->visibility[$alertType] = true;
        } else {
            $this->criticality[$alertType] = false;
            $this->alarmality[$alertType] = false;
        }
        $this->updateAlertTypes();
    }

    public function toggleAlarmality($alertType)
    {
        if (empty($this->alarmality[$alertType])) {
            $this->alarmality[$alertType] = true;
            $this->criticality[$alertType] = true;
            $this->visibility[$alertType] = true;
        } else {
            $this->alarmality[$alertType] = false;
        }
        $this->updateAlertTypes();
    }

    public function cancelSettings()
    {
        $this->mount();
    }

    private function determineDefaultLanguage($translations)
    {
        $availableLanguages = array_keys($translations);
        foreach ($this->languagePriority as $lang) {
            if (in_array($lang, $availableLanguages) && !empty($translations[$lang])) {
                return $lang;
            }
        }
        foreach ($availableLanguages as $lang) {
            if ($lang !== 'default' && !empty($translations[$lang])) {
                return $lang;
            }
        }
        return null;
    }
}
