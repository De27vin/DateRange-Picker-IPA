<?php

namespace App\Http\Livewire\Ucp;

use App\Helpers\GroupCache;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Models\Device;
use App\Models\Role;
use App\Services\CustomFieldsService;
use App\Services\RolesService;
use App\Services\SettingsService;
use App\Traits\ValidationTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\FreeswitchApiTrait;
use App\Traits\DeviceFormTrait;
use App\Traits\TranslationsTrait;

class DeviceSettingsAndCustomFields extends Component
{
    use FreeswitchApiTrait;
    use DeviceFormTrait;
    use TranslationsTrait;

    // settings
    public $device;
    public array $deviceSettingsProgrammable = [];
    public array $deviceSettingsNonProgrammable = [];

    // custom fields
    public $showEditSection = false;
    public $locale;
    public $accountId;
    public $fieldTranslations;
    public array $deviceCustomFields = [];

    protected $listeners = [
        'updateSettingsFormData',
        'toggleCustomFields',
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

    public function mount($deviceId = null)
    {
        if ($deviceId === 'none') {
            $this->instantiateEmpty();
            return;
        }

        if ($deviceId) {
            $this->device = Device::findOrFail($deviceId);
        }

        $this->instantiateAll();
    }

    public function setDeviceId($deviceId)
    {
        $this->device = Device::findOrFail($deviceId);
        $this->prepareDeviceFormData();
        $this->instantiateAll();
    }

    private function instantiateSettings()
    {
        $deviceSettings = $this->settingsService->getDeviceSettings($this->device);
        $moduleSettables = $this->device->device_site->module?->modules_settables ?? [];

        $this->deviceSettingsProgrammable = $this->settingsService->prepareSettingsForView(SettingsService::DEVICE, $deviceSettings, $moduleSettables);
        $this->deviceSettingsNonProgrammable = $this->settingsService->prepareSettingsForView(SettingsService::DEVICE, $deviceSettings);
    }

    private function instantiateCustomFields()
    {
        $this->fieldTranslations = $this->getFieldTranslations($this->locale, 'device_field');
        $this->deviceCustomFields = $this->customService->getCustomFields($this->accountId, $this->device->device_id, true);
    }

    private function instantiateAll()
    {
        $this->instantiateSettings();
        $this->instantiateCustomFields();
    }

    private function instantiateEmpty()
    {
        // settings
        $this->deviceSettingsProgrammable = [];
        $this->deviceSettingsNonProgrammable = [];

        // custom fields
        $this->deviceCustomFields = [];
    }

    public function render()
    {
        return view('livewire.ucp.device-settings-custom-fields');
    }

    public function updateProgrammableSettings()
    {
        $updated = $this->settingsService->updateDeviceSettings(
            $this->device,
            collect($this->deviceSettingsProgrammable),
            true,
        );
        if ($updated) {
            $this->notify('success', __('Settings for device updated'));
            $this->makeFsReload($this->device->device_id);
            GroupCache::forgetGroup('settings');
            $this->dispatchBrowserEvent('siteUpdated', ['siteId' => $this->device->device_site->ds_id]);
        } else {
            $this->notify('error', __('Error occurred on settings update'));
        }

        GroupCache::forgetGroup('settings');
        $this->instantiateSettings();
    }

    public function updateNonProgrammableSettings()
    {
        $updated = $this->settingsService->updateDeviceSettings(
            $this->device,
            collect($this->deviceSettingsNonProgrammable),
            null,
        );

        if ($updated) {
            $this->notify('success', __('Settings for device updated'));
            $this->makeFsReload($this->device->device_id);
            GroupCache::forgetGroup('settings');
            $this->dispatchBrowserEvent('siteUpdated', ['siteId' => $this->device->device_site->ds_id]);
        } else {
            $this->notify('error', __('Error occurred on settings update'));
        }

        GroupCache::forgetGroup('settings');
        $this->instantiateSettings();
    }

    public function changeBoolSetting($key, $value)
    {
        if (!empty($this->deviceSettingsProgrammable[$key]['bool']) && !empty($this->deviceSettingsProgrammable[$key]['is_writeable'])) {
            $this->deviceSettingsProgrammable[$key]['bool'] = [
                'on' => 'on' === $value && !$this->deviceSettingsProgrammable[$key]['bool']['on'],
                'off' => 'off' === $value && !$this->deviceSettingsProgrammable[$key]['bool']['off'],
                'na' => 'na' === $value && !$this->deviceSettingsProgrammable[$key]['bool']['na'],
            ];
            if ($this->deviceSettingsProgrammable[$key]['bool']['on']) {
                $this->deviceSettingsProgrammable[$key]['value'] = '1';
                $this->deviceSettingsProgrammable[$key]['not_applicable'] = false;
            }
            elseif ($this->deviceSettingsProgrammable[$key]['bool']['off']) {
                $this->deviceSettingsProgrammable[$key]['value'] = '0';
                $this->deviceSettingsProgrammable[$key]['not_applicable'] = false;
            }
            elseif ($this->deviceSettingsProgrammable[$key]['bool']['na']) {
                $this->deviceSettingsProgrammable[$key]['value'] = '';
                $this->deviceSettingsProgrammable[$key]['not_applicable'] = true;
            }
            else {
                $this->deviceSettingsProgrammable[$key]['value'] = '';
                $this->deviceSettingsProgrammable[$key]['not_applicable'] = false;
            }
        }

        if (!empty($this->deviceSettingsNonProgrammable[$key]['bool']) && !empty($this->deviceSettingsNonProgrammable[$key]['is_writeable'])) {
            $this->deviceSettingsNonProgrammable[$key]['bool'] = [
                'on' => 'on' === $value && !$this->deviceSettingsNonProgrammable[$key]['bool']['on'],
                'off' => 'off' === $value && !$this->deviceSettingsNonProgrammable[$key]['bool']['off'],
                'na' => 'na' === $value && !$this->deviceSettingsNonProgrammable[$key]['bool']['na'],
            ];
            if ($this->deviceSettingsNonProgrammable[$key]['bool']['on']) {
                $this->deviceSettingsNonProgrammable[$key]['value'] = '1';
                $this->deviceSettingsNonProgrammable[$key]['not_applicable'] = false;
            }
            elseif ($this->deviceSettingsNonProgrammable[$key]['bool']['off']) {
                $this->deviceSettingsNonProgrammable[$key]['value'] = '0';
                $this->deviceSettingsNonProgrammable[$key]['not_applicable'] = false;
            }
            elseif ($this->deviceSettingsNonProgrammable[$key]['bool']['na']) {
                $this->deviceSettingsNonProgrammable[$key]['value'] = '';
                $this->deviceSettingsNonProgrammable[$key]['not_applicable'] = true;
            }
            else {
                $this->deviceSettingsNonProgrammable[$key]['value'] = '';
                $this->deviceSettingsNonProgrammable[$key]['not_applicable'] = false;
            }
        }
    }

    public function changeSettingNa($settingId, bool $state)
    {
        if (!empty($this->deviceSettingsProgrammable[$settingId])) {
            $this->deviceSettingsProgrammable[$settingId]['not_applicable'] = $state;
        }

        if (!empty($this->deviceSettingsNonProgrammable[$settingId])) {
            $this->deviceSettingsNonProgrammable[$settingId]['not_applicable'] = $state;
        }
    }

    // todo: move to service
    public function makeFsReload($id)
    {
        if ($result = $this->fsMake('ucp del device ' . $id, false, true)) {
            $this->notify('success', __('ucp reload device processed'));
        } else {
            $this->notify('warning', __('ucp reload device failed'));
        }
    }

    // to chyba jest niepotrzebne
    public function toggleCustomFields()
    {
        $this->showEditSection = !$this->showEditSection;
    }


    public function updateDeviceCustomFields()
    {
        DB::beginTransaction();
        try {
            $this->customService->saveCustomFields($this->device->device_id, $this->deviceCustomFields, true);
            DB::commit();
            $this->notify('success', trans('Device custom fields updated'));
            $this->dispatchBrowserEvent('siteUpdated', ['siteId' => $this->device->device_site->ds_id]);
        } catch (\Throwable $e) {
            DB::rollback();
            \Log::error('Custom fields update failed', ['error' => $e->getMessage(), 'device_id' => $this->device->device_id]);
            
            if (str_contains($e->getMessage(), 'QR Code value must be unique')) {
                $this->notify('error', __('QR Code value must be unique within the account. This value already exists on another device.'));
            } else {
                $this->notify('error', trans('Error on custom fields update'));
            }
        }

        GroupCache::forgetGroup('sites');
        GroupCache::forgetGroup('devices');
        GroupCache::forgetGroup('cfg');
        $this->instantiateCustomFields();
    }
}
