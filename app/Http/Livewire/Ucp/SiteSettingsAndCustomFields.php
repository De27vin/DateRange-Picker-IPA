<?php

namespace App\Http\Livewire\Ucp;

use App\Helpers\GroupCache;
use App\Models\DeviceSite;
use App\Models\Role;
use App\Services\CustomFieldsService;
use App\Services\RolesService;
use App\Services\SettingsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\FreeswitchApiTrait;
use App\Traits\DeviceFormTrait;
use App\Traits\TranslationsTrait;

class SiteSettingsAndCustomFields extends Component
{
    use FreeswitchApiTrait;
    use DeviceFormTrait;
    use TranslationsTrait;

    // settings
    public $deviceSite;
    public array $deviceSiteSettingsProgrammable = [];
    public array $deviceSiteSettingsNonProgrammable = [];

    // custom fields
    public $showCustomFieldsSection = false;
    public $locale;
    public $accountId;
    public $fieldTranslations;
    public array $deviceSiteCustomFields = [];

    protected $listeners = [
        'updateSettingsFormData',
    ];

    private SettingsService $settingsService;
    private RolesService $rolesService;
    private CustomFieldsService $customService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->settingsService = new SettingsService();
        $this->rolesService = new RolesService();
        $this->customService = new CustomFieldsService();
    }

    public function mount($deviceSiteId = null)
    {
        if ($deviceSiteId === 'none') {
            $this->instantiateEmpty();
            return;
        }

        if ($deviceSiteId) {
            $this->deviceSite = DeviceSite::findOrFail($deviceSiteId);
        }

        $this->instantiateAll();
    }

    public function setDeviceSiteId($deviceSiteId)
    {
        $this->deviceSite = DeviceSite::findOrFail($deviceSiteId);
        $this->prepareDeviceFormData();
        $this->instantiateAll();
    }

    private function instantiateSettings()
    {
        $deviceSiteSettings = $this->settingsService->getDeviceSiteSettings($this->deviceSite);
        $moduleSettables = $this->deviceSite->module?->modules_settables ?? [];

        $this->deviceSiteSettingsProgrammable = $this->settingsService->prepareSettingsForView(SettingsService::DEVICE_SITE, $deviceSiteSettings, $moduleSettables);
        $this->deviceSiteSettingsNonProgrammable = $this->settingsService->prepareSettingsForView(SettingsService::DEVICE_SITE, $deviceSiteSettings);
    }

    private function instantiateCustomFields()
    {
        $this->fieldTranslations = $this->getFieldTranslations($this->locale, 'device_field');
        $this->deviceSiteCustomFields = $this->customService->getCustomFields(session('account.id'), $this->deviceSite->ds_id, false);
    }

    private function instantiateAll()
    {
        $this->instantiateSettings();
        $this->instantiateCustomFields();
    }

    private function instantiateEmpty()
    {
        // settings
        $this->deviceSiteSettingsProgrammable = [];
        $this->deviceSiteSettingsNonProgrammable = [];

        // custom fields
        $this->deviceSiteCustomFields = [];
    }

    public function render()
    {
        return view('livewire.ucp.site-settings-custom-fields');
    }

    public function updateProgrammableSettings()
    {
        $updated = $this->settingsService->updateDeviceSiteSettings(
            $this->deviceSite,
            collect($this->deviceSiteSettingsProgrammable),
            true,
        );
        if ($updated) {
            $this->notify('success', __('Settings for device site updated'));
            $this->makeFsReloadSite($this->deviceSite);
            $this->dispatchBrowserEvent('siteUpdated', ['siteId' => $this->deviceSite->ds_id]);
        } else {
            $this->notify('error', __('Settings for device site were not updated'));
        }

        GroupCache::forgetGroup('settings');
        $this->instantiateSettings();
    }

    public function updateNonProgrammableSettings()
    {
        $updated = $this->settingsService->updateDeviceSiteSettings(
            $this->deviceSite,
            collect($this->deviceSiteSettingsNonProgrammable),
            null,
        );
        if ($updated) {
            $this->notify('success', __('Settings for device site updated'));
            $this->makeFsReloadSite($this->deviceSite);
            $this->dispatchBrowserEvent('siteUpdated', ['siteId' => $this->deviceSite->ds_id]);
        } else {
            $this->notify('error', __('Settings for device site were not updated'));
        }

        GroupCache::forgetGroup('settings');
        $this->instantiateSettings();
    }

    public function changeBoolSetting($key, $value)
    {
        if (!empty($this->deviceSiteSettingsProgrammable[$key]['bool']) && !empty($this->deviceSiteSettingsProgrammable[$key]['is_writeable'])) {
            $this->deviceSiteSettingsProgrammable[$key]['bool'] = [
                'on' => 'on' === $value && !$this->deviceSiteSettingsProgrammable[$key]['bool']['on'],
                'off' => 'off' === $value && !$this->deviceSiteSettingsProgrammable[$key]['bool']['off'],
                'na' => 'na' === $value && !$this->deviceSiteSettingsProgrammable[$key]['bool']['na'],
            ];
            if ($this->deviceSiteSettingsProgrammable[$key]['bool']['on']) {
                $this->deviceSiteSettingsProgrammable[$key]['value'] = '1';
                $this->deviceSiteSettingsProgrammable[$key]['not_applicable'] = false;
            }
            elseif ($this->deviceSiteSettingsProgrammable[$key]['bool']['off']) {
                $this->deviceSiteSettingsProgrammable[$key]['value'] = '0';
                $this->deviceSiteSettingsProgrammable[$key]['not_applicable'] = false;
            }
            elseif ($this->deviceSiteSettingsProgrammable[$key]['bool']['na']) {
                $this->deviceSiteSettingsProgrammable[$key]['value'] = '';
                $this->deviceSiteSettingsProgrammable[$key]['not_applicable'] = true;
            }
            else {
                $this->deviceSiteSettingsProgrammable[$key]['value'] = '';
                $this->deviceSiteSettingsProgrammable[$key]['not_applicable'] = false;
            }
        }

        if (!empty($this->deviceSiteSettingsNonProgrammable[$key]['bool']) && !empty($this->deviceSiteSettingsNonProgrammable[$key]['is_writeable'])) {
            $this->deviceSiteSettingsNonProgrammable[$key]['bool'] = [
                'on' => 'on' === $value && !$this->deviceSiteSettingsNonProgrammable[$key]['bool']['on'],
                'off' => 'off' === $value && !$this->deviceSiteSettingsNonProgrammable[$key]['bool']['off'],
                'na' => 'na' === $value && !$this->deviceSiteSettingsNonProgrammable[$key]['bool']['na'],
            ];
            if ($this->deviceSiteSettingsNonProgrammable[$key]['bool']['on']) {
                $this->deviceSiteSettingsNonProgrammable[$key]['value'] = '1';
                $this->deviceSiteSettingsNonProgrammable[$key]['not_applicable'] = false;
            }
            elseif ($this->deviceSiteSettingsNonProgrammable[$key]['bool']['off']) {
                $this->deviceSiteSettingsNonProgrammable[$key]['value'] = '0';
                $this->deviceSiteSettingsNonProgrammable[$key]['not_applicable'] = false;
            }
            elseif ($this->deviceSiteSettingsNonProgrammable[$key]['bool']['na']) {
                $this->deviceSiteSettingsNonProgrammable[$key]['value'] = '';
                $this->deviceSiteSettingsNonProgrammable[$key]['not_applicable'] = true;
            }
            else {
                $this->deviceSiteSettingsNonProgrammable[$key]['value'] = '';
                $this->deviceSiteSettingsNonProgrammable[$key]['not_applicable'] = false;
            }
        }
    }

    public function changeSettingNa($settingId, bool $state)
    {
        if (!empty($this->deviceSiteSettingsProgrammable[$settingId])) {
            $this->deviceSiteSettingsProgrammable[$settingId]['not_applicable'] = $state;
        }

        if (!empty($this->deviceSiteSettingsNonProgrammable[$settingId])) {
            $this->deviceSiteSettingsNonProgrammable[$settingId]['not_applicable'] = $state;
        }
    }

    // todo: move to service
    public function makeFsReloadSite(DeviceSite $site): void
    {
       $success = $this->fsMake("ucp del site $site->ds_id", false, true)
           && $site->devices->every(fn($d) => $this->fsMake("ucp del device $d->device_id", false, true));
       $type = $success ? 'success' : 'warning';
       $result = $success ? __('processed') : __('failed');
       $this->notify($type, __('ucp reload site command ').$result);
    }

    public function updateDeviceSiteCustomFields()
    {
        DB::beginTransaction();
        try {
            $this->customService->saveCustomFields($this->deviceSite->ds_id, $this->deviceSiteCustomFields, false);
            DB::commit();
            $this->notify('success', trans('Site custom fields updated'));
            $this->dispatchBrowserEvent('siteUpdated', ['siteId' => $this->deviceSite->ds_id]);
        } catch (\Throwable $e) {
            \Log::error($e, ['Caught']);
            DB::rollback();
            $this->notify('error', trans('Error on site custom fields update'));
        }

        GroupCache::forgetGroup('sites');
        GroupCache::forgetGroup('devices');
        $this->instantiateCustomFields();
    }
}
