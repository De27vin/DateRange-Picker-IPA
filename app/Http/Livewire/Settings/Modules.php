<?php

namespace App\Http\Livewire\Settings;

use App\Enum\ModuleFlags;
use App\Helpers\GroupCache;
use App\Models\AccountModuleSetting;
use App\Models\Module;
use App\Services\RolesService;
use App\Services\SettingsService;
use App\Traits\AccountsTrait;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Models\Language;
use App\Traits\FreeswitchApiTrait;
use App\Traits\TranslationsTrait;
use App\Traits\DeviceFormTrait;
use App\Http\Livewire\DataTable\WithCachedRows;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class Modules extends Component
{
    use WithCachedRows;
    use FreeswitchApiTrait;
    use TranslationsTrait;
    use DeviceFormTrait;
    use AccountsTrait;

    public $showDeleteModal    = false;
    public $breadcrumb         = ['UCP', 'Settings', 'Device Types'];
//    public $customFieldSettings;
    public $selectedDeviceType = null;
    public $accModProgrammableSettings = null;
    public $accModAdvancedSettings = null;
    public $option             = 0;
    public $moduleOption       = 0; // todo: rather remove
    public $canWriteSettings   = false;
    public $showCreate         = false;
    public $deviceTypes;
    public $locale;
    public $editing;
    public $translations;
    public $fieldTranslations;
    public $languages;
    public $selectedModule = null;
    public $moduleFieldOptions = [];
    public $showFormFields;
    public $moduleOptions     = [];

    protected $listeners = [
        'updateOption',
        'cancelSettings',
        'create'
    ];

    protected $rules = [
        'selectedDeviceType.dt_name' => 'required',
        'editing.dt_name' => 'required|unique:device_types',
        'editing.dt_module_id' => 'required',
        'translations' => '',
    ];

    private SettingsService $settingsService;
    private RolesService $rolesService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->settingsService = new SettingsService();
        $this->rolesService = new RolesService();
    }

    public function mount()
    {
        $this->initData();
    }

    public function initData()
    {
        $this->locale       = session('locale', 'en');
        $this->languages    = Language::where('language_enabled', '=', true)->get()->pluck('language_code')->all();
        $this->translations = null;
        $this->fieldTranslations = $this->getFieldTranslations($this->locale);
        $this->option       = -1;
        $this->deviceTypes  = null;
        $this->showFormFields = false;

        if( Auth::user()->is_admin ){
            $this->canWriteSettings = true;
        }
//        $this->customFieldSettings = session('customFieldSettings');

//        $this->moduleOptions = $this->account->modules // THIS WILL BE BACK - BELOW IS STUB
        $this->moduleOptions = Module::all()
            ->filter(fn($m) => $m->module_type->mt_type === 'PROTOCOL')
            ->keyBy('module_id')
            ->map(fn ($module) => $module->module_desc ?: $module->module_name)
            ->toArray();

//        $this->moduleOptions = Module::all()
//            ->keyBy('module_id')
//            ->map(fn ($module) => $module->module_desc ?: $module->module_name)
//            ->toArray();

        $this->selectedModule = null;
        $this->accModProgrammableSettings = null;
    }

    public function render()
    {
        return view('livewire.settings.modules');
    }

    public function updateProgrammableSettings()
    {
        if (!$this->selectedModule || !$this->account) {
            return;
        }

        $success = $this->settingsService->updateAccountModuleSettings(
            $this->selectedModule,
            $this->account->account_id,
            collect($this->accModProgrammableSettings),
            true,
        );

        if($success){
            $this->notify('success', __('Values for module settings updated'));
            $this->makeFsReload();
            GroupCache::forgetGroup('settings');
        } else {
            $this->notify('error', __('Values for module could not be updated'));
        }

        $accModSettings = $this->settingsService->getAccountModuleSettings($this->selectedModule->module_id, session('account.id'));
        $this->accModProgrammableSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACC_MOD, $accModSettings, $this->selectedModule->modules_settables);
        $this->accModAdvancedSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACC_MOD, $accModSettings);
    }

    public function updateAdvancedSettings()
    {
        if (!$this->selectedModule || !$this->account) {
            return;
        }

        $success = $this->settingsService->updateAccountModuleSettings(
            $this->selectedModule,
            $this->account->account_id,
            collect($this->accModAdvancedSettings),
            null,
        );

        if($success){
            $this->notify('success', __('Values for module settings updated'));
            $this->makeFsReload();
            GroupCache::forgetGroup('settings');
        } else {
            $this->notify('error', __('Values for module could not be updated'));
        }

        $accModSettings = $this->settingsService->getAccountModuleSettings($this->selectedModule->module_id, session('account.id'));
        $this->accModProgrammableSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACC_MOD, $accModSettings, $this->selectedModule->modules_settables);
        $this->accModAdvancedSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACC_MOD, $accModSettings);
    }

    public function changeBoolSetting($key, $value)
    {
        if (!empty($this->accModProgrammableSettings[$key]['bool']) && !empty($this->accModProgrammableSettings[$key]['is_writeable'])) {
            $this->accModProgrammableSettings[$key]['bool'] = [
                'on' => 'on' === $value && !$this->accModProgrammableSettings[$key]['bool']['on'],
                'off' => 'off' === $value && !$this->accModProgrammableSettings[$key]['bool']['off'],
                'na' => 'na' === $value && !$this->accModProgrammableSettings[$key]['bool']['na'],
            ];
            if ($this->accModProgrammableSettings[$key]['bool']['on']) {
                $this->accModProgrammableSettings[$key]['value'] = '1';
                $this->accModProgrammableSettings[$key]['not_applicable'] = false;
            }
            elseif ($this->accModProgrammableSettings[$key]['bool']['off']) {
                $this->accModProgrammableSettings[$key]['value'] = '0';
                $this->accModProgrammableSettings[$key]['not_applicable'] = false;
            }
            elseif ($this->accModProgrammableSettings[$key]['bool']['na']) {
                $this->accModProgrammableSettings[$key]['value'] = '';
                $this->accModProgrammableSettings[$key]['not_applicable'] = true;
            }
            else {
                $this->accModProgrammableSettings[$key]['value'] = '';
                $this->accModProgrammableSettings[$key]['not_applicable'] = false;
            }
        }

        if (!empty($this->accModAdvancedSettings[$key]['bool']) && !empty($this->accModAdvancedSettings[$key]['is_writeable'])) {
            $this->accModAdvancedSettings[$key]['bool'] = [
                'on' => 'on' === $value && !$this->accModAdvancedSettings[$key]['bool']['on'],
                'off' => 'off' === $value && !$this->accModAdvancedSettings[$key]['bool']['off'],
                'na' => 'na' === $value && !$this->accModAdvancedSettings[$key]['bool']['na'],
            ];
            if ($this->accModAdvancedSettings[$key]['bool']['on']) {
                $this->accModAdvancedSettings[$key]['value'] = '1';
                $this->accModAdvancedSettings[$key]['not_applicable'] = false;
            }
            elseif ($this->accModAdvancedSettings[$key]['bool']['off']) {
                $this->accModAdvancedSettings[$key]['value'] = '0';
                $this->accModAdvancedSettings[$key]['not_applicable'] = false;
            }
            elseif ($this->accModAdvancedSettings[$key]['bool']['na']) {
                $this->accModAdvancedSettings[$key]['value'] = '';
                $this->accModAdvancedSettings[$key]['not_applicable'] = true;
            }
            else {
                $this->accModAdvancedSettings[$key]['value'] = '';
                $this->accModAdvancedSettings[$key]['not_applicable'] = false;
            }
        }
    }

    public function changeSettingNa($settingId, bool $state)
    {
        if (!empty($this->accModProgrammableSettings[$settingId])) {
            $this->accModProgrammableSettings[$settingId]['not_applicable'] = $state;
        }

        if (!empty($this->accModAdvancedSettings[$settingId])) {
            $this->accModAdvancedSettings[$settingId]['not_applicable'] = $state;
        }
    }

    public function updateFieldOptions()
    {
        $profileData = $this->getProfileData();
        $moduleFieldSettings =& $profileData['config']['modules'][$this->selectedModule->module_name]['device']['field'];

        $updated = false;
        foreach ($this->moduleFieldOptions as $field => $options) {

            if ($this->isFieldLocked($field, $this->selectedModule)) {
                continue;
            }

            $field = Str::after($field, 'device_field_');

            if ($moduleFieldSettings[$field]['required'] != $options['required']['value']) {
                $moduleFieldSettings[$field]['required'] = $options['required']['value'];
                $updated = true;
            }
            if ($moduleFieldSettings[$field]['display'] != $options['display']['value']) {
                $moduleFieldSettings[$field]['display'] = $options['display']['value'];
                $updated = true;
            }
        }

        if ($updated) {
            $this->saveProfileData($profileData);
            GroupCache::forgetGroup('profile_data');
//        $this->updateCustomFieldSettings();
            $this->notify('success', __('Settings for fields visibility updated'));
        }
    }

    public function toggleDisplay($field)
    {
        if (!$this->moduleFieldOptions[$field]['display']['value']) {
            $this->moduleFieldOptions[$field]['display']['value'] = true;
        } else {
            $this->moduleFieldOptions[$field]['display']['value'] = false;
            $this->moduleFieldOptions[$field]['required']['value'] = false;
        }
    }

    public function toggleRequired($field)
    {
        if (!$this->moduleFieldOptions[$field]['required']['value']) {
            $this->moduleFieldOptions[$field]['required']['value'] = true;
            $this->moduleFieldOptions[$field]['display']['value'] = true;
        } else {
            $this->moduleFieldOptions[$field]['required']['value'] = false;
        }
    }

    public function updatedOption($index)
    {
        try{
            if($index > 0){
                $this->selectedModule = Module::where('module_id', '=', $index)->first();
                $this->translations = $this->getTranslations(['settings' => 'device']);

                $accModSettings = $this->settingsService->getAccountModuleSettings($this->selectedModule->module_id, session('account.id'));
                $this->accModProgrammableSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACC_MOD, $accModSettings, $this->selectedModule->modules_settables);
                $this->accModAdvancedSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACC_MOD, $accModSettings);

                $this->moduleFieldOptions = $this->getModuleFieldsOptions($this->selectedModule);
                $this->showFormFields = true;
            } elseif($index == 0) {
                $this->initData();
            }
        } catch(\Throwable $e){
            \Log::error($e, ['Caught']);
            $this->notify('error', __('Error on modules synchronization - please re-login to the system'));
        }
        $this->showCreate = false;
    }

    public function cancelSettings($type)
    {
        if ($type === 'programmableSettings') {
            $accModSettings = $this->settingsService->getAccountModuleSettings($this->selectedModule->module_id, session('account.id'));
            $this->accModProgrammableSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACC_MOD, $accModSettings, $this->selectedModule->modules_settables);
        }
        if ($type === 'advancedSettings') {
            $accModSettings = $this->settingsService->getAccountModuleSettings($this->selectedModule->module_id, session('account.id'));
            $this->accModAdvancedSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACC_MOD, $accModSettings);
        }
        if ($type === 'fieldsVisibility') {
            $this->moduleFieldOptions = $this->getModuleFieldsOptions($this->selectedModule);
        }
    }

    public function cancelCreate()
    {
        $this->showCreate = false;
    }

    // TODO: TO DELETE AFTER ADJUSTING TEMPLATE
    public function toggleSettings($path, $type)
    {
        $this->moduleFieldOptions[$path][$type]['value'] = !$this->moduleFieldOptions[$path][$type]['value'];
    }

    public function makeFsReload()
    {
        if($result = $this->fsMake('ucp del accmod ' . $this->account->account_id . ' ' . $this->selectedModule->module_id, false, true)) {
            $this->notify('success', __('ucp reload account module command processed'));
        } else {
            $this->notify('error', __('Due to connection problems, it is possible that the changed values will only take effect after a slight delay.'));
        }
    }
}
