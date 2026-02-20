<?php

namespace App\Http\Livewire\Ucp;

use App\Models\Address;
use App\Models\CustomFieldConfig;
use App\Models\CustomFieldValue;
use App\Models\DeviceAlert;
use App\Models\DeviceComment;
use App\Models\DeviceGateway;
use App\Models\DeviceSite;
use App\Models\Location;
use App\Models\Number;
use App\Models\NumberType;
use App\Models\Device;
use App\Services\AddressService;
use App\Services\CustomFieldsService;
use App\Services\DeviceFormFieldsService;
use App\Services\PhoneNumbersService;
use App\Services\RolesService;
use App\Services\SettingsService;
use App\Services\DevicesService;
use App\Traits\DeviceFormTrait;
use App\Traits\FreeswitchApiTrait;
use App\Traits\TranslationsTrait;
use App\Traits\ValidationTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @deprecated
 */
class DeviceSiteDetails extends Component
{
    use ValidationTrait;
    use DeviceFormTrait;
    use TranslationsTrait;
    use FreeswitchApiTrait;

    public DeviceSite $deviceSite;
    public $devicesAlerts;
    public $devicesStates;
    public $latestComments;
    public $fieldTranslations;
    public $alertTranslations;
    public $deviceSiteFields;
    public $deviceSiteSettingsProgrammable;
    public $deviceSiteSettingsNonProgrammable;
//    public $showEditSection; // deprecated edit
    public $showCustomFieldsSection;
    public $showAddDevice;
    public $showDeleteSite = false;
    public $showDeleteAddress = false;
    public $deviceSiteId;
    // gateway input
//    public $editedGatewayAssigned = [];
//    public $assignableGateways = [];

    public $tasks;

    public $deviceSiteCustomFields = [];

    private AddressService $addressService;
    private SettingsService $settingsService;
    private PhoneNumbersService $numbersService;
    private DeviceFormFieldsService $formFieldsService;
    private RolesService $rolesService;
    private CustomFieldsService $customService;

    protected $listeners = [
        'initDeviceSiteAlertsAndStates',
    ];

    public function __construct($id = null) {
        parent::__construct($id);
        $this->addressService = new AddressService();
        $this->settingsService = new SettingsService();
        $this->numbersService = new PhoneNumbersService();
        $this->formFieldsService = new DeviceFormFieldsService();
        $this->rolesService = new RolesService();
        $this->customService = new CustomFieldsService();
    }

    public function mount()
    {
        session(['currentPage' => 'device-site-details']);
        $this->prepareDeviceFormData();
        $this->showCustomFieldsSection = false;
        $this->showAddDevice = false;
        $this->showDeleteSite = false;
        $this->deviceSiteId = $this->getDeviceSiteIdFromUrl();
        $this->initDeviceSiteData();
        $this->initDeviceSiteAlertsAndStates();
        $this->initDeviceSiteFields();
        $this->alertTranslations = $this->getAlertTranslations(session('locale', 'default'));
        $this->fieldTranslations = $this->getFieldTranslations(session('locale', 'default'));
//        $this->deviceSiteSettingsProgrammable = $this->prepareSettingsForView(programmable: true);
//        $this->deviceSiteSettingsNonProgrammable = $this->prepareSettingsForView(programmable: false);
//        $this->setGatewaySelectOptions();

        $this->tasks = [
            'carcall' => false,
            'set'     => false,
            'revival' => false,
            'export'  => false
        ];

        $this->deviceSiteCustomFields = $this->customService->getCustomFields(session('account.id'), $this->deviceSiteId, false);
    }

    public function render()
    {
        return view('livewire.ucp.device-site-details', [
            'accountCustomFields' => $this->customService->getAccountCustomFieldsValues(session('account.id')),
        ]);
    }

    private function getDeviceSiteIdFromUrl()
    {
        return last(explode('/',url()->current()));
    }

    private function getDeviceSiteById($deviceSiteId): DeviceSite
    {
        $accountId = session('account.id');
//        $deviceSite = DeviceSite::with('module.funktions', 'devices', 'address', 'address.location', 'devices.device_labels')
        $deviceSite = DeviceSite::with('module.funktions', 'devices', 'address', 'address.location')
            ->where('ds_id', $deviceSiteId)
            ->where('ds_account_id', $accountId)
            ->first();

        if (!$deviceSite) {
            abort(404);
        }

        return $deviceSite;
    }

    public function deleteSite()
    {
        if (count($this->deviceSite->devices)) {
            throw new \Exception('Attempt to delete site with connected devices');
        }
        if (!empty($this->deviceSite->device_gateway)) {
            throw new \Exception('Attempt to delete site with connected gateway');
        }

        $success = false;
        DB::beginTransaction();
        try {

            $this->numbersService->detachNumbersFromSite($this->deviceSite);
            $this->deviceSite->device_site_settings()->delete();
            $this->deviceSite->delete();

            DB::commit();
            $success = true;
        } catch (\Throwable $e) {
            \Log::error($e, ['Caught']);
            DB::rollback();
        }

        if ($success) {
            $this->notify('success', __('Device Site successfully deleted'));
//            $this->makeFsDeleteSite($this->deviceSite->ds_id); -- this blocks screen in an awful way - needs to be fixed
            return redirect()->route('equipment');
        }

        $this->notify('error', __('Error occurred while deleting site'));
    }

    public function initDeviceSiteData(bool $reset = true)
    {
        $this->deviceSite = $this->getDeviceSiteById($this->deviceSiteId);
        $this->deviceSite->sortDevicesByType();

        if ($reset) {
            $this->showAddDevice = false;
        }
    }
    public function initDeviceSiteAlertsAndStates()
    {
        $this->setDevicesAlerts();
        $this->setDevicesStates();
        $this->deviceSite->sortDevicesByType();
    }

    private function initDeviceSiteFields()
    {
        $this->deviceSiteFields = $this->getDeviceSiteFields();
        if($this->deviceSite->address == null){
            $this->makeEmptyAddress();
        } else {
            $this->address = $this->deviceSite->address;
            $this->location = $this->deviceSite->address->location;
        }
    }

//    public function deleteDevice($device_id)
//    {
//        $success = false;
//        DB::beginTransaction();
//        try {
//            if ($gateway = DeviceGateway::where('dg_device_id', $device_id)->first()) {
//                $gateway->dg_device_id = null;
//                $gateway->save();
//            }
//            $device = Device::find($device_id);
//            $device->delete();
//            $this->showAddDevice = false;
//            $this->initDeviceSiteData();
//
//            DB::commit();
//            $success = true;
//        } catch (\Throwable $e) {
//            // log to db
//            $this->notify('error', trans('Error occurred while deleting device'));
//            DB::rollBack();
//        }
//
//        if ($success) {
//            $this->notify('success', __('Device successfully deleted'));
////            $this->makeFsDeleteDevice($device_id); -- this blocks screen in an awful way - needs to be fixed
//        }
//    }

//    private function prepareSettingsForView(bool $programmable = true): array
//    {
//        if (empty($this->deviceSite->module)) {
//            return [];
//        }
//
//        $translations = $this->getSettingTranslations(session('locale', 'default'), 'device');
//        $deviceSiteSettings = $this->settingsService->getDeviceSiteSettings($this->deviceSite);
//        $deviceSiteSettings = $this->settingsService->removePrefixedSettings($deviceSiteSettings, 'password');
//
//        $moduleSettables = $this->deviceSite->module?->modules_settables ?? [];
//        if ($programmable) {
//            $deviceSiteSettings = $this->settingsService->applySettables($deviceSiteSettings, $moduleSettables);
//        } else {
//            $deviceSiteSettings = $this->settingsService->removeSettables($deviceSiteSettings, $moduleSettables);
//        }
//
//        $viewSettings = [];
//        foreach ($deviceSiteSettings as $setting) {
//
//            if (!$this->rolesService->doesUserHaveHigherOrEqualRole(Auth::user(), $setting->setting_read_role_id, session('account.id'))) {
//                continue;
//            }
//
//            $translationKey = Str::replace('.', '_', $setting->setting_key);
//            $viewSettings[$setting->setting_id]['setting_id'] = $setting->setting_id;
//            $viewSettings[$setting->setting_id]['translation'] = $translations[$translationKey] ?? $this->getSettingDefaultTranslation($translationKey);
//            $viewSettings[$setting->setting_id]['type'] = $setting->setting_type->st_type;
//            $viewSettings[$setting->setting_id]['value'] = $setting->device_site_value ?? '';
//            $viewSettings[$setting->setting_id]['is_writeable'] = $this->rolesService->doesUserHaveHigherOrEqualRole(Auth::user(), $setting->setting_write_role_id, session('account.id'));
//            $viewSettings[$setting->setting_id]['not_applicable'] = isset($setting->device_site_value) && $setting->device_site_value === '';
//
//            if ($setting->setting_type->st_type === 'bool') {
//                $viewSettings[$setting->setting_id]['bool'] = [
//                    'on' => isset($setting->device_site_value) && $setting->device_site_value === '1',
//                    'off' => isset($setting->device_site_value) && $setting->device_site_value === '0',
//                    'na' => isset($setting->device_site_value) && $setting->device_site_value === '',
//                ];
//            }
//
//            foreach ($this->settingsService->getSettingsOrder('device_site') as $fallback) {
//                if (isset($setting->{$fallback.'_value'})) {
//                    $viewSettings[$setting->setting_id]['fallback'] = [
//                        'label' => 'Fallback: '.Str::replace('_', ' ', $fallback),
//                        'value' => $setting->{$fallback.'_value'},
//                    ];
//                    break;
//                }
//            }
//        }
//
//        return $viewSettings;
//    }

//    public function updateProgrammableSettings()
//    {
//        $updated = $this->settingsService->updateDeviceSiteSettings(
//            $this->deviceSite,
//            collect($this->deviceSiteSettingsProgrammable),
//            true,
//        );
//        if ($updated) {
//            $this->notify('success', 'Settings for device site updated');
//            $this->makeFsReloadSite($this->deviceSite->ds_id);
////            $this->emit('updateDeviceStats');
//        } else {
//            $this->deviceSiteSettingsProgrammable = $this->prepareSettingsForView(programmable: true);
//            $this->notify('error', 'Settings for device site were not updated');
//        }
//    }
//
//    public function updateNonProgrammableSettings()
//    {
//        $updated = $this->settingsService->updateDeviceSiteSettings(
//            $this->deviceSite,
//            collect($this->deviceSiteSettingsNonProgrammable),
//            false,
//        );
//        if ($updated) {
//            $this->notify('success', 'Settings for device site updated');
//            $this->makeFsReloadSite($this->deviceSite->ds_id);
////            $this->emit('updateDeviceStats');
//        } else {
//            $this->deviceSiteSettingsNonProgrammable = $this->prepareSettingsForView(programmable: false);
//            $this->notify('error', 'Settings for device site were not updated');
//        }
//    }
//
//    public function changeBoolSetting($key, $value)
//    {
//        if (!empty($this->deviceSiteSettingsProgrammable[$key]['bool']) && !empty($this->deviceSiteSettingsProgrammable[$key]['is_writeable'])) {
//            $this->deviceSiteSettingsProgrammable[$key]['bool'] = [
//                'on' => 'on' === $value && !$this->deviceSiteSettingsProgrammable[$key]['bool']['on'],
//                'off' => 'off' === $value && !$this->deviceSiteSettingsProgrammable[$key]['bool']['off'],
//                'na' => 'na' === $value && !$this->deviceSiteSettingsProgrammable[$key]['bool']['na'],
//            ];
//            if ($this->deviceSiteSettingsProgrammable[$key]['bool']['on']) {
//                $this->deviceSiteSettingsProgrammable[$key]['value'] = '1';
//                $this->deviceSiteSettingsProgrammable[$key]['not_applicable'] = false;
//            }
//            elseif ($this->deviceSiteSettingsProgrammable[$key]['bool']['off']) {
//                $this->deviceSiteSettingsProgrammable[$key]['value'] = '0';
//                $this->deviceSiteSettingsProgrammable[$key]['not_applicable'] = false;
//            }
//            elseif ($this->deviceSiteSettingsProgrammable[$key]['bool']['na']) {
//                $this->deviceSiteSettingsProgrammable[$key]['value'] = '';
//                $this->deviceSiteSettingsProgrammable[$key]['not_applicable'] = true;
//            }
//            else {
//                $this->deviceSiteSettingsProgrammable[$key]['value'] = '';
//                $this->deviceSiteSettingsProgrammable[$key]['not_applicable'] = false;
//            }
//        }
//
//        if (!empty($this->deviceSiteSettingsNonProgrammable[$key]['bool']) && !empty($this->deviceSiteSettingsNonProgrammable[$key]['is_writeable'])) {
//            $this->deviceSiteSettingsNonProgrammable[$key]['bool'] = [
//                'on' => 'on' === $value && !$this->deviceSiteSettingsNonProgrammable[$key]['bool']['on'],
//                'off' => 'off' === $value && !$this->deviceSiteSettingsNonProgrammable[$key]['bool']['off'],
//                'na' => 'na' === $value && !$this->deviceSiteSettingsNonProgrammable[$key]['bool']['na'],
//            ];
//            if ($this->deviceSiteSettingsNonProgrammable[$key]['bool']['on']) {
//                $this->deviceSiteSettingsNonProgrammable[$key]['value'] = '1';
//                $this->deviceSiteSettingsNonProgrammable[$key]['not_applicable'] = false;
//            }
//            elseif ($this->deviceSiteSettingsNonProgrammable[$key]['bool']['off']) {
//                $this->deviceSiteSettingsNonProgrammable[$key]['value'] = '0';
//                $this->deviceSiteSettingsNonProgrammable[$key]['not_applicable'] = false;
//            }
//            elseif ($this->deviceSiteSettingsNonProgrammable[$key]['bool']['na']) {
//                $this->deviceSiteSettingsNonProgrammable[$key]['value'] = '';
//                $this->deviceSiteSettingsNonProgrammable[$key]['not_applicable'] = true;
//            }
//            else {
//                $this->deviceSiteSettingsNonProgrammable[$key]['value'] = '';
//                $this->deviceSiteSettingsNonProgrammable[$key]['not_applicable'] = false;
//            }
//        }
//    }
//
//    public function changeSettingNa($settingId, bool $state)
//    {
//        if (!empty($this->deviceSiteSettingsProgrammable[$settingId])) {
//            $this->deviceSiteSettingsProgrammable[$settingId]['not_applicable'] = $state;
//        }
//
//        if (!empty($this->deviceSiteSettingsNonProgrammable[$settingId])) {
//            $this->deviceSiteSettingsNonProgrammable[$settingId]['not_applicable'] = $state;
//        }
//    }

    private function getSetAvailability()
    {
        $deviceFunctions = $this->deviceSite->module->funktions;
        foreach ($deviceFunctions as $item) {
            if($item->function_call == '_set'){
                return true;
            }
        }
        return false;
    }

    public function cancelEditDevice()
    {
        $this->showCustomFieldsSection = false;
        $this->showAddDevice = false;
    }

    private function getCarcallAvailability()
    {
        return Auth::user()->isAgent && Auth::user()->user_ext != null;
    }

    private function getRevivalAvailability()
    {
        $deviceFunctions = $this->deviceSite->module->funktions;
        foreach ($deviceFunctions as $item) {
            if($item->function_call == '_revival'){
                return true;
            }
        }
        return false;
    }

    private function setDevicesAlerts()
    {
        $this->devicesAlerts = DeviceAlert::with('alert_type')
            ->get()
            ->mapToGroups(function($item, $key){
                return [$item->da_device_id => $item];
            });
    }

    private function setDevicesStates()
    {
        foreach ($this->deviceSite->devices as $device)
        {
            $this->devicesStates[$device->device_id] = json_decode(json_encode($device->states), true);
        }
    }

    private function setLatestComments()
    {
        $deviceIds = $this->deviceSite->devices->map->device_id;
        $latestComments = DeviceComment::query()
            ->whereIn('dc_device_id', $deviceIds)
            ->orderBy('dc_device_id')
            ->orderByDesc('dc_created')
            ->get()
            ->groupBy('dc_device_id')
            ->map(fn($comments) => $comments->first()->dc_text)
            ->toArray();

        foreach ($deviceIds as $deviceId) {
            if (empty($latestComments[$deviceId])) {
                $latestComments[$deviceId] = '';
            }
        }

        $this->latestComments = $latestComments;
    }

    private function getDeviceSiteFields(): array
    {
        if (empty($this->deviceSite->module)) {
            return [];
        }

        $defaultFieldItem = [
            'display' => true,
            'required' => false,
            'locked' => false,
        ];

        $siteFields = [];
        $siteFields['name'] = $defaultFieldItem + ['value' => $this->deviceSite->ds_name];
        $siteFields['link'] = $defaultFieldItem + ['value' => $this->deviceSite->ds_link];
//        $siteFields['tech'] = $defaultFieldItem + ['value' => $this->deviceSite->ds_tech];
//        $siteFields['custom'] = $defaultFieldItem + ['value' => $this->deviceSite->ds_custom];
//        $siteFields['custom3'] = $defaultFieldItem + ['value' => $this->deviceSite->ds_custom3];
//        $siteFields['custom4'] = $defaultFieldItem + ['value' => $this->deviceSite->ds_custom4];
        foreach (NumberType::all()->pluck('nt_type', 'nt_id')->sortKeys()->toArray() as $numberType) {
            $numberType = strtolower($numberType);
            if (!in_array($numberType, ['pstn', 'sim', 'sip', 'pbx'])) continue;

            $siteFields[$numberType] = $defaultFieldItem + ['value' => $this->deviceSite->{$numberType}?->number_value ?? null];
        }
        $siteFields['address'] = $defaultFieldItem + ['value' => $this->deviceSite->address?->address_value ?? null];

        $profileData = $this->getProfileData();
        $moduleName = $this->deviceSite->module->module_name;
        $moduleFieldSettings = $profileData['config']['modules'][$moduleName]['device']['field'];

        foreach ($moduleFieldSettings as $moduleField => $moduleFieldSetting) {
            foreach ($siteFields as $siteField => $siteFieldItem) {
                if (str_contains($moduleField, $siteField) ||
                    (in_array($siteField, ['pstn', 'sim', 'sip', 'pbx']) && str_contains($moduleField, 'numbers'))
                ) {
                    $siteFields[$siteField] = $moduleFieldSetting + $siteFieldItem;
                }
            }
        }

        return $siteFields;
    }

    public function updateDeviceSiteCustomFields()
    {
        DB::beginTransaction();
        try {
            $this->customService->saveCustomFields($this->deviceSiteId, $this->deviceSiteCustomFields, false);
            DB::commit();
            $this->notify('success', trans('Site custom fields updated'));
        } catch (\Throwable $e) {
            \Log::error($e, ['Caught']);
            DB::rollback();
            $this->notify('error', trans('Error on site custom fields update'));
        }
    }

    // DEPRECATED - but useful
    public function updateDeviceSiteData()
    {
        $this->validate();

        // phone numbers
        $phoneNumbers = collect($this->deviceSiteFields)
            ->filter(fn($i, $k) => $this->isFieldPhoneNumber($k))
            ->map(fn($i, $k) => $i['value'])
            ->toArray();

        // todo: can be addres same validation rules as in createSite
        if ($this->address->address_value) {
            $location = Location::addData(
                location: $this->location->location_value,
                postcode: $this->location->location_postcode,
                countryId: $this->location->location_country_id,
                save: false
            );
            $address = Address::addData(
                address: $this->address->address_value,
                locationId: $location->location_id,
                save: false
            );
        }

        $errors = $this->validatePhoneNumbers($this->deviceSite, $phoneNumbers);
        if (!empty($location) && !empty($address)) {
            $errors = array_merge($errors, $this->validateAddress($this->deviceSite->module, $address, $location));
        }

        if ($this->performValidationNotification(...$errors)) {
            return;
        }

        $success = false;
        DB::beginTransaction();
        try {
            $this->updatePhoneNumbers($phoneNumbers);
            $this->updateAddress();
            $this->deviceSite->ds_name = $this->deviceSiteFields['name']['value'] ?: null;
            $this->deviceSite->ds_link = $this->deviceSiteFields['link']['value'] ?: null;
            $this->deviceSite->save();

            $this->customService->saveCustomFields($this->deviceSiteId, $this->deviceSiteCustomFields, false);

//            $this->updateGatewayAssignment();
            DB::commit();
            $success = true;
            $this->notify('success', trans('Device site updated'));
        } catch (\Throwable $e) {
            \Log::error($e, ['Caught']);
            DB::rollback();
            $this->notify('error', trans('Error on site update'));
        }

        if ($success) {
            $this->makeFsReloadSite($this->deviceSite->ds_id);
        }

        $this->initDeviceSiteData(false);
    }

    private function updateAddress()
    {
        if ($this->address->address_value != null) {
            $address = $this->addressService->getOrCreateAddress(
                $this->address->address_value,
                $this->location->location_value,
                $this->location->location_postcode,
                $this->location->location_country_id,
            );
            if ($this->deviceSite->ds_address_id != $address->address_id) {
                $this->deviceSite->ds_address_id = $address->address_id;
            }
        }
    }

    public function deleteAddress()
    {
        $this->deviceSite->update(['ds_address_id' => null]);

        $this->notify('success', __('Address of device is deleted.'));
        redirect('/device-site/'.$this->deviceSite->ds_id);
    }

    public function removeAlert($deviceId, $alertType, $value = null)
    {
        $cmd = 'ucp clear device '.$deviceId.' '.strtoupper($alertType);
        if ($value) {
            $cmd = $cmd.' '.$value;
        }

        if($result = $this->fsMake($cmd, false, true)) {
            $this->notify('success', __('Chosen alert successfully deleted.'));
        } else {
            $this->notify('error', __('ucp clear device command failed'));
        }
        $this->initDeviceSiteAlertsAndStates();
    }

    private function updatePhoneNumbers(array $phoneNumbers)
    {
        $toReload =  $this->numbersService->updateSitePhoneNumbers($this->deviceSite, $phoneNumbers);
        foreach ($toReload as $numberId) {
            $this->makeFsReloadPhone($numberId);
        }
    }

    public function makeFsReloadPhone($id)
    {
        if($result = $this->fsMake('ucp del number ' . $id, false, true)) {
            $this->notify('success', __('ucp reload number command processed'));
        } else {
            $this->notify('error', __('ucp reload number command failed'));
        }
    }

    public function makeFsReloadSite($id)
    {
        if($result = $this->fsMake('ucp reload site ' . $id, false, true)) {
            $this->notify('success', __('ucp reload site command processed'));
        } else {
            $this->notify('error', __('ucp reload site command failed'));
        }
    }

    public function makeFsDeleteSite($id)
    {
        if($result = $this->fsMake('ucp del site ' . $id, false, true)) {
            $this->notify('success', __('ucp del site command processed'));
        } else {
            $this->notify('error', __('ucp del site command failed'));
        }
    }

    public function makeFsDeleteDevice($id)
    {
        if($result = $this->fsMake('ucp del device ' . $id, false, true)) {
            $this->notify('success', __('ucp del device command processed'));
        } else {
            $this->notify('error', __('ucp del device command failed'));
        }
    }

//    private function setGatewaySelectOptions(): void
//    {
//        $emptyGatewayOption = ['label' => __('No Gateway'), 'value' => ''];
//        $this->editedGatewayAssigned = $this->getAssignedGateway();
//        $this->assignableGateways = array_merge(
//            [$emptyGatewayOption],
//            [$this->editedGatewayAssigned],
//            $this->getUnassignedGateways()
//        );
//    }

//    private function getUnassignedGateways(): array
//    {
//        $module = null;
//        $moduleIsGSR = false;
//        if(!is_null($this->deviceSite)){
//            $module = $this->deviceSite->module;
//        }
//        if(!is_null($module)){
////            $moduleIsGSR = (Str::contains($module->module_name,'GSR'));
//            $moduleIsGSR = false;
//        }
//        if($moduleIsGSR){
////            return DeviceGateway::with('device_gateway_type')->doesntHave('device')->whereHas('device_gateway_type', function($query){
////                $query->where('device_gateway_types.dgt_type','=','GSR');
////            })->get()->pluck('dg_id', 'dg_mac')->map(function ($value, $label) {
////                return ['label' => (string) $label, 'value' => (string) $value];
////            })->values()->toArray();
//        } else {
//            return DeviceGateway::forAccount()->doesntHave('device')->get()->pluck('dg_id', 'dg_mac')->map(function ($value, $label) {
//                return ['label' => (string) $label, 'value' => (string) $value];
//            })->values()->toArray();
//        }
//    }

//    private function getAssignedGateway(): ?array
//    {
//        $macAddress = (string) $this->deviceSite->device_gateway?->dg_mac;
//        return [
//            'label' => $macAddress ? $macAddress .' ('.__('current').')' : '',
//            'value' => (string) $this->deviceSite->device_gateway?->dg_id,
//        ];
//    }

//    private function updateGatewayAssignment(): void
//    {
//        $gatewayId = $this->editedGatewayAssigned['value'];
//        if ($gatewayId != $this->deviceSite->device_gateway?->dg_id) {
//            if (empty($gatewayId)) {
//                //detach gateway
//                $this->deviceSite->ds_dg_id = null;
//                $this->deviceSite->save();
//            } elseif (empty($this->deviceSite->device_gateway?->dg_id)) {
//                // attach gateway
//                $gateway = DeviceGateway::findOrFail($gatewayId);
//                if ($gateway->device_site) {
//                    throw new \Exception('Attempt to assing already assigned gateway');
//                }
//                $this->deviceSite->ds_dg_id = $gateway->dg_id;
//                $this->deviceSite->save();
//            } else {
//                // change assignment
//                $gateway = DeviceGateway::findOrFail($gatewayId);
//                if ($gateway->device_site) {
//                    throw new \Exception('Attempt to assing already assigned gateway');
//                }
//                $this->deviceSite->ds_dg_id = $gateway->dg_id;
//                $this->deviceSite->save();
//            }
//        }
//    }

    public function rules()
    {
        $rules = [
//            'deviceSiteFields.mac.value' => '',
//            "deviceSiteFields.pstn.value" => '',
//            "deviceSiteFields.sim.value" => '',
//            "deviceSiteFields.sip.value" => '',
//            "deviceSiteFields.pbx.value" => '',
//            "deviceSiteFields.name.value" => '',
//            "deviceSiteFields.link.value" => $this->deviceSiteFields['link']['required'] ? 'required' : '',
//            "deviceSiteFields.link.value" => '',
//            "deviceSiteFields.tech.value" => $this->deviceSiteFields['tech']['required'] ? 'required' : '',
//            "deviceSiteFields.custom.value" => $this->deviceSiteFields['custom']['required'] ? 'required' : '',
//            "deviceSiteFields.custom3.value" => $this->deviceSiteFields['custom3']['required'] ? 'required' : '',
//            "deviceSiteFields.custom4.value" => $this->deviceSiteFields['custom4']['required'] ? 'required' : '',

//            "deviceSiteFields.address" => '',
//            'address.address_value' => '',
//            'location.location_value' => '',
//            'location.location_postcode' => '',
//            'location.location_country_id' => '',
        ];

        return $rules;
    }

    public function messages()
    {
        // todo: change hardcoded string messages into keyed maessages from trans validation.php
        return [
            'deviceSiteFields.link.value.required' => __('Link is required'),
        ];
    }
}
