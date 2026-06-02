<?php
namespace App\Traits;

use App\Enum\ModuleFlags;
use App\Models\DeviceSite;
use App\Models\DeviceType;
use App\Models\Device;
use App\Models\DeviceLabelOld;
use App\Models\DeviceNumber;
use App\Models\DeviceNumberType;
use App\Models\Language;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\NumberType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/** @deprecated */
trait DeviceFormTrait
{
    public $selectedDeviceType;
    public $deviceFieldSettings;
    public $deviceFormFieldSettings;
    public $deviceTypes;
    public $deviceId;
    public $languages;
    public $locale;
    public $canWriteSettings   = false;
    public $countries;
    public $address;
    public $location;
    public $device;

    // PORTING TO TEST BELOW
    public $selectedDeviceSite;
    public $selectedModule;
    public $modules;

    public function prepareDeviceFormData($all = false)
    {
        // PORTING TO TEST - NEW VERSION
        $this->locale = session('locale');
        $this->languages = app(\App\Services\LanguageService::class)->getEnabledLanguages();
        $this->countries = $this->getCountryList();
        $this->modules = Module::all()->pluck('module_name', 'module_id')->toArray();
        if( Auth::user()->is_user || Auth::user()->is_admin || Auth::user()->is_site ){
            $this->canWriteSettings = true;
        }
        $this->locale = session('locale', 'en');
        $this->accountId = session('account.id');
    }

    public function makeEmptyAddress()
    {
        $country = \App\Models\Country::query()->where('country_iso','=','CH')->first();
        $location = (new \App\Models\Address)->location()->newModelInstance();
        $location->location_value = null;
        $location->location_postcode = null;
        $location->location_country_id = $country->country_id;
        $address = (new \App\Models\Address);
        $address->location = $location;
        $address->address_value = null;
        $this->address = $address;
        $this->location = $location;
    }

    public function getDeviceTypesAsArray()
    {
        return DeviceType::where('dt_enabled', '=', true)->pluck('dt_name', 'dt_id')->toArray();
    }

    public function getSelectedDeviceType($id)
    {
        return DeviceType::with('device_type_settings')->where('dt_id','=',$id)->first();
    }

    public function getSelectedDeviceSite($id)
    {
        return DeviceSite::with('device_site_settings')->where('ds_id','=',$id)->first();
    }

    public function getSelectedModule($id)
    {
        return Module::with('settings', 'settables', 'account_settings')->where('module_id','=', $id)->first();
    }

    public function getPhoneData()
    {
        // PORTING TO TEST - NEW VERSION
        $this->device->pstn = $this->device->device_site->pstn?->number_value;
        $this->device->sim = $this->device->device_site->sim?->number_value;
        $this->device->sip = $this->device->device_site->sip?->number_value;
        $this->device->pbx = $this->device->device_site->pbx?->number_value;
    }

    public function resetData()
    {
        $this->selectedDeviceType = null;
        $this->deviceFieldSettings = null;
        $this->deviceFormFieldSettings = [];

        // porting
        $this->selectedDeviceSite = null;
    }

    public function updateDeviceFields()
    {
        // PORTING TO TEST - NOT APPLICABLE
//        $this->customFieldSettings[$this->selectedDeviceType->dt_name]['fields']   = $this->getDeviceFormFields();

        // PORTING TO TEST - NEW VERSION
        $this->customFieldSettings[$this->selectedDeviceSite->ds_name]['fields']   = $this->getDeviceFormFields();
    }

    public function updateCustomFieldSessionData()
    {
        $customFieldSettings = $this->getCustomFieldSettings();
        Session::put('customFieldSettings', $customFieldSettings);
    }

    public function getCountryList()
    {
        $countries = \App\Models\Country::all();
        $locale = session('locale', 'en');
        foreach ($countries as $key => $country) {
            $countryList[$country->country_id] = locale_get_display_region('-'.$country->country_iso,$locale);
        }
        $countryList = $this->arraySortUTF($countryList);
        return $countryList;
    }

    // todo: this function must be checked for calling and eventually removed
    public function updateCustomFieldSettings()
    {
        // PORTING TO TEST - NEW VERSION
        $sessionDeviceTypes = ( Session::get('customFieldSettings',null) == null ? [] : Session::get('customFieldSettings',null) );
        $this->prepareDeviceFormData();
        $settingsForModule = [];
        foreach ($this->modules as $id => $name) {
            $this->selectedModule = $this->getSelectedModule($id);
            $settingsForModule[$name]['fields'] = $this->getDeviceFormFields($this->selectedModule);
        }
        // dd($settingsForDeviceType);
        Session::put('customFieldSettings', $settingsForModule);
    }

    // todo: this function must be checked for calling and eventually removed
    public function getCustomFieldSettings()
    {
        // PORTING TO TEST - NEW VERSION
        $sessionModules = ( Session::get('customFieldSettings',null) == null ? [] : Session::get('customFieldSettings',null) );
        $this->prepareDeviceFormData();
        if(count($sessionModules) != count($this->modules)){
            $settingsForModule = [];
            foreach ($this->modules as $id => $name) {
                $this->selectedModule = $this->getSelectedModule($id);
                $settingsForModule[$name]['fields'] = $this->getDeviceFormFields($this->selectedModule);
            }
            Session::put('customFieldSettings', $settingsForModule);
            return $settingsForModule;
        } else {
            return $sessionModules;
        }
    }

    public function getDeviceFromUrl()
    {
        $device_id = last(explode('/',url()->current()));
        $device = Device::find($device_id);
        if (!empty($device)) {
            return $device;
        } else {
            abort(404);
        }
    }

    public function getDeviceFromUrlByEQID()
    {
        $device_eq = last(explode('/',url()->current()));
        $device = Device::where('device_equipment', $device_eq)->first();
        if (!empty($device)) {
            return $device;
        } else {
            abort(404);
        }
    }

    public function getLabelSettings($settables, $currentDeviceLabels= [])
    {
        $labelList = DB::select(DB::raw("
            SELECT * FROM device_labels WHERE FIND_IN_SET(dl_id, (SELECT GROUP_CONCAT(dl_tree) FROM device_labels JOIN device_labels_devices ON (dld_dl_id = dl_id AND dld_device_id = '" . $this->device->device_id . "'))) ORDER BY dl_group_1 ASC, dl_group_2 ASC, dl_group_3 ASC, dl_group_4 ASC, dl_group_5 ASC, dl_group_6 ASC
        "));
        foreach ($labelList as $item) {
            $group = DeviceLabelOld::with('device_label_settings')->where('dl_id','=',$item->dl_id)->first();
            foreach ($group->device_label_settings as $groupSetting) {
                if(array_key_exists($groupSetting->dls_setting_id, $settables) && $settables[$groupSetting->dls_setting_id] == null){
                    $settables[$groupSetting->dls_setting_id] = $groupSetting->dls_value;
                }
            }
        }

        return $settables;
    }

    public function getDeviceFormFields(Module $module = null)
    {
       $module = $module ?? $this->selectedModule ?? $this->device->device_site->module;
       $moduleName = $module->module_name;
       $profileData = $this->getProfileData();
       $moduleFieldOptions = $profileData['config']['modules'][$moduleName]['device']['field'];

       $outputFieldOptions = [];
       foreach ($moduleFieldOptions as $field => $option) {
           $outputFieldOptions['device_field_'.$field]['required']['value'] = $option['required'] || $module->fieldIsRequired($field);
           $outputFieldOptions['device_field_'.$field]['display']['value'] = $option['display'] || $module->fieldIsRequired($field);
           $outputFieldOptions['device_field_'.$field]['locked'] = $this->isFieldLocked($field, $module);
       }

       return $outputFieldOptions;

    }

    public function getSettingValue($id, $key, $type='string')
    {
        // PORTING TO TEST - NOT APPLICABLE
//        $dts = DeviceTypeSetting::where('dts_dt_id','=',$this->selectedDeviceType->dt_id)->where('dts_setting_id','=',$id)->first();

        // PORTING TO TEST - NEW VERSION
        $dts = null;

        if($dts != null){
            if($type == 'boolean'){
                return ( $dts->dts_value == '1' || $dts->dts_value == 'true' ? true : false );
            } else {

                return $dts->dts_value;
            }
        } else {
            // PORTING TO TEST - NOT APPLICABLE
//            $ms = ModuleSetting::where('ms_module_id','=', $this->selectedDeviceType->dt_module_id)->where('ms_setting_id','=',$id)->first();

            // PORTING TO TEST - NEW VERSION
            $ms = ModuleSetting::where('ms_module_id','=', $this->device->device_site->ds_protocol_id)->where('ms_setting_id','=',$id)->first();


            if($ms != null){
                if($type == 'boolean'){
                    return ( $ms->ms_value == 'true' || $ms->ms_value == '1' ? true : false );
                } else {
                    return $ms->ms_value;
                }
            } else {
                if($type == 'boolean'){
                    return false;
                } else {
                    return null;
                }
            }
        }
    }

    public function savePhoneNumber($deviceId, $phoneNumber, $type)
    {
        try{
            $dnt              = DeviceNumberType::where('dnt_type','=',$type)->first();
            $dn               = new DeviceNumber();
            $dn->dn_dnt_id    = $dnt->dnt_id;
            $dn->dn_device_id = $deviceId;
            $dn->dn_value     = $phoneNumber;
            $dn->save();
            return true;
        }
        catch(\Exception $e){
            return false;
        }
    }


    public function getParentDevice($phoneNumber)
    {
        $existingNumber = DeviceNumber::with('device')->where('dn_value','=',$phoneNumber)->first();
        if($existingNumber == null){
            return null;
        }
        $existingDevice = Device::with('device_type', 'device_numbers', 'childs')->onlyNodes()->where('device_id','=',$existingNumber['dn_device_id'])->first();
        if($existingDevice == null){
            return null;
        }
        return $existingDevice;
    }

    public function validatePhoneNumber()
    {
        $rawPhoneNumber = $this->device['device_number_primary'];
        // $rawPhoneNumber = ltrim($rawPhoneNumber, '0');
        if(Str::startsWith($rawPhoneNumber, '+')){
            $rawPhoneNumber = ltrim($rawPhoneNumber, '+');
            $rawPhoneNumber = '+' . $rawPhoneNumber;
            $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();

            try {
                $phoneNumber = $phoneNumberUtil->parse($rawPhoneNumber, null, null, true);
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
                return false;
            }
            return $phoneNumberUtil->isValidNumber($phoneNumber);
        } else {
            return ctype_digit($rawPhoneNumber);
        }
    }


    public function getSettingsFromModule()
    {
        if($this->deviceFieldSettings == null){
            return [];
        }
        foreach ($this->deviceFieldSettings as $key => $value) {
            foreach ($value as $label => $setting) {
                $deviceFormFieldSettings[Str::replace('.', '_', $key)][$label] = [ 'setting_id' => $setting->setting_id, 'setting_value' => $setting->setting_value ];
            }
        }
        return $deviceFormFieldSettings;
    }

//    public function getLabelsOfCustomFields($customFieldSettings = null, $data = null)
//    {
//        $label = [
//            'tech' => '',
//            'custom' => '',
//            'custom3' => '',
//            'custom4' => ''
//        ];
//        // $data = ($data->first()->has('dt_name') ? $data->first() : $data);
//        // dd(Device::hydrate($data->first()->toArray()));
//        $dtName = null;
//        try{
//            $dtName = $data->device_type->dt_name;
//        } catch(\Exception $e){
//            $dtName = $data->first()->dt_name;
//        }
//
//        if($customFieldSettings[$dtName]['fields']['device_field_tech']['display']['value']){
//            $label['tech'] = $customFieldSettings[$dtName]['fields']['device_field_tech']['label']['value'];
//            if($customFieldSettings[$dtName]['fields']['device_field_tech'][$this->locale]['value'] != null){
//                $label['tech'] = $customFieldSettings[$dtName]['fields']['device_field_tech'][$this->locale]['value'];
//            }
//        }
//
//        if($customFieldSettings[$dtName]['fields']['device_field_custom']['display']['value']){
//            $label['custom'] = $customFieldSettings[$dtName]['fields']['device_field_custom']['label']['value'];
//            if($customFieldSettings[$dtName]['fields']['device_field_custom'][$this->locale]['value'] != null){
//                $label['custom'] = $customFieldSettings[$dtName]['fields']['device_field_custom'][$this->locale]['value'];
//            }
//        }
//        if($customFieldSettings[$dtName]['fields']['device_field_custom3']['display']['value']){
//            $label['custom3'] = $customFieldSettings[$dtName]['fields']['device_field_custom3']['label']['value'];
//            if($customFieldSettings[$dtName]['fields']['device_field_custom3'][$this->locale]['value'] != null){
//                $label['custom3'] = $customFieldSettings[$dtName]['fields']['device_field_custom3'][$this->locale]['value'];
//            }
//        }
//        if($customFieldSettings[$dtName]['fields']['device_field_custom4']['display']['value']){
//            $label['custom4'] = $customFieldSettings[$dtName]['fields']['device_field_custom4']['label']['value'];
//            if($customFieldSettings[$dtName]['fields']['device_field_custom4'][$this->locale]['value'] != null){
//                $label['custom4'] = $customFieldSettings[$dtName]['fields']['device_field_custom4'][$this->locale]['value'];
//            }
//        }
//        return $label;
//
//    }

    public function arraySortUTF($tArray) {
        $aOriginal = $tArray;
        if (count($aOriginal) == 0) { return $aOriginal; }
        $aModified = array();
        $aReturn   = array();
        $aSearch   = array("Ä","Å","ä","Ö","ö","Ü","ü","ß","-");
        $aReplace  = array("Ae","Aa","ae","Oe","oe","Ue","ue","ss"," ");
        foreach($aOriginal as $key => $val) {
            $aModified[$key] = str_replace($aSearch, $aReplace, $val);
        }
        natcasesort($aModified);
        foreach($aModified as $key => $val) {
            $aReturn[$key] = $aOriginal[$key];
        }
        return $aReturn;
    }

    public function getModuleFieldsOptions(?Module $module = null): array
    {
        $module = $module ?? $this->selectedModule ?? $this->device->device_site->module;
        $moduleName = $module->module_name;
        $profileData = $this->getProfileData();
        $moduleFieldOptions = $profileData['config']['modules'][$moduleName]['device']['field'];

        $outputFieldOptions = [];
        foreach ($moduleFieldOptions as $field => $option) {
            $outputFieldOptions['device_field_'.$field]['required']['value'] = $option['required'];
            $outputFieldOptions['device_field_'.$field]['display']['value'] = $option['display'];
            $outputFieldOptions['device_field_'.$field]['locked'] = $this->isFieldLocked($field, $module);
        }

        return $outputFieldOptions;
    }

    private function isFieldLocked(string $field, Module $module): bool
    {
        $internallyLocked = false;
        $profileData = $this->getProfileData();
        if (isset($profileData['config']['modules'][$module->module_name]['device']['field'][$field]['locked'])) {
            $internallyLocked = $profileData['config']['modules'][$module->module_name]['device']['field'][$field]['locked'];
        }

        $flagToCheck = match ($field) {
            'identity' => ModuleFlags::MODULE_FLAG_IDENTITY_REQUIRED,
            'module' => ModuleFlags::MODULE_FLAG_MODULE_REQUIRED,
//            'numbers' => ModuleFlags::MODULE_FLAG_NUMBER_REQUIRED,
            'pin' => ModuleFlags::MODULE_FLAG_PIN_REQUIRED,
            default => null,
        };
        $externallyLocked = boolval($flagToCheck?->value & $module->module_flags);

        return $internallyLocked || $externallyLocked;
    }
}
