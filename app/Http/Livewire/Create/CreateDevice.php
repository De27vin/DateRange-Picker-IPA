<?php
namespace App\Http\Livewire\Create;

use App\Enum\ModuleFlags;
use App\Helpers\GroupCache;
use App\Models\Address;
use App\Models\Device;
use App\Models\DeviceGateway;
use App\Models\DeviceLabelOld;
use App\Models\DeviceSite;
use App\Models\Location;
use App\Models\Module;
use App\Models\ModuleType;
use App\Services\DeviceFormFieldsService;
use App\Services\DevicesService;
use App\Traits\AccountsTrait;
use App\Traits\DeviceFormTrait;
use App\Traits\TranslationsTrait;
use App\Traits\ValidationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CreateDevice extends Component
{
    use AccountsTrait;
    use ValidationTrait;
    use DeviceFormTrait;
    use TranslationsTrait;


    public $deviceSite;

    public $deviceFields = [];
//    public $addressFields;
    public $fieldTranslations;
    public $requiredFields = [];
    public $countries;

    public $rules;
    public $messages;

    ///////////// fields /////////////
    public $moduleType;
    public $moduleTypeOptions;

    public $module;
    public $moduleOptions;

    public $gateway;
    public $gatewayOptions;

    public $labels = [];
    public $labelOptions;


    private DeviceFormFieldsService $formFieldsService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->formFieldsService = new DeviceFormFieldsService();
    }

    public function mount(DeviceSite $deviceSite)
    {
        $this->deviceSite = $deviceSite;
        $this->locale = session('locale', 'default');
        $this->account = $this->getCurrentAccount();
//        $this->addressFields = $this->getAddressFields();
        $this->fieldTranslations = $this->getFieldTranslations($this->locale);
//        $this->labelOptions = $this->getLabelOptions(); // HIDE LABELS
        $this->countries = $this->getCountryList();
        // todo: can be checking first if site have already gateway and if so then removing it from options
        $this->moduleTypeOptions = ModuleType::deviceTypes()->get()->pluck('mt_type', 'mt_id');
    }

    public function render()
    {
        return view('livewire.create.create-device');
    }

    public function cancel()
    {
        redirect('/device-site/'.$this->deviceSite->ds_id);
    }

//    private function getAddressFields()
//    {
//        return [
//            'address_value'       => null,
//            'location_postcode'   => null,
//            'location_value'      => null,
//            'location_country_id' => null,
//        ];
//    }

    private function getDeviceFields()
    {
        return [
            'equipment'   => null,
            'identity'    => null,
            'module'      => null,
            'pin'         => null,
        ];
    }

    public function hydrate()
    {
        $this->resetValidation();
    }

    public function updatedModuleType($moduleTypeId)
    {
        // remove fields
        $this->removeFields();
        // remove selected module and dropdown
        $this->moduleOptions = null;
        $this->module = null;
        // remove selected gateway and dropdown
        $this->gatewayOptions = null;
        $this->gateway = null;

        if (!$this->validateModuleType()) {
            return;
        }

        $moduleOptions = $this->getModuleOptionsWithValidation();
        if (!$moduleOptions) {
            return;
        }

        $this->moduleOptions = $moduleOptions;
    }


    public function updatedModule($moduleId)
    {
        // remove fields
        $this->removeFields();
        // remove selected gateway and dropdown
        $this->gatewayOptions = null;
        $this->gateway = null;

        if (empty($moduleId)) {
            return;
        }

        $module = Module::findOrFail($moduleId);
        $moduleType = ModuleType::findOrFail($this->moduleType);

        // THIS SHOULD NEVER HAPPEN
        if ($module->module_type->mt_type !== $moduleType->mt_type) {
            $this->addError('general', __('Error - chosen module type does not match selected device type'));
        }

        $isModuleTypeGateway = str_contains(strtolower($moduleType->mt_type), 'gateway'); // this constraint might be removed
        $doesModuleSupportSip = boolval($module->module_flags & ModuleFlags::MODULE_FLAG_SIP_SUPPORT->value);

        if ($isModuleTypeGateway && $doesModuleSupportSip) {
            //todo: filtering based on chosen module
            $this->gatewayOptions = DeviceGateway::doesntHave('device')->get()->mapWithKeys(function (DeviceGateway $gateway) {
                $value = $gateway->dg_mac ?? $gateway->dg_imei;
                return [$gateway->dg_id => $value];
            })->toArray();
        }

        $this->setFieldsForModule($module);
    }

    private function removeFields()
    {
        $this->deviceFields = [];
        $this->requiredFields = [];
    }

    private function setFieldsForModule(Module $deviceModule)
    {
        $deviceFields = $this->getDeviceFields();

        $deviceFieldsSettings = $this->getProfileData()['config']['modules'][$deviceModule->module_name]['device']['field'];
        $protocolFieldsSettings = $this->getProfileData()['config']['modules'][$this->deviceSite->module->module_name]['device']['field'];

        $this->requiredFields = $this->formFieldsService->getRequiredFields($deviceModule);
        $this->requiredFields = array_unique(array_merge($this->requiredFields, $this->formFieldsService->getRequiredFields($this->deviceSite->module)));

        // this functionality here might be moved to DeviceFormFieldsService
        foreach ($deviceFields as $field => $value) {
            if (empty($deviceFieldsSettings[$field]['display']) && empty($protocolFieldsSettings[$field]['display']) && !in_array($field, $this->requiredFields)) {
                unset($deviceFields[$field]);
            }
        }

        $this->deviceFields = $deviceFields;
    }

    public function create()
    {
        if (!$this->validateModuleType() || !$this->getModuleOptionsWithValidation()) {
            return;
        }
        $this->validate();
        if (!$this->integrityValidation()) {
            return;
        }

        $accountId = session('account.id');
        DB::beginTransaction();
        try {

            // todo: might be performed validation that selected module and module_type are corresponding
            // MODULE
            $module = Module::findOrFail($this->module);

            $requiresModule = boolval($module->module_flags & ModuleFlags::MODULE_FLAG_MODULE_REQUIRED->value) ||
                              boolval($this->deviceSite->module->module_flags & ModuleFlags::MODULE_FLAG_MODULE_REQUIRED->value);

            $requiresIdentity = boolval($module->module_flags & ModuleFlags::MODULE_FLAG_IDENTITY_REQUIRED->value) ||
                                boolval($this->deviceSite->module->module_flags & ModuleFlags::MODULE_FLAG_IDENTITY_REQUIRED->value);

            $device = Device::create([
                'device_ds_id' => $this->deviceSite->ds_id,
                'device_account_id' => $accountId,
                'device_module_id' => $module->module_id,
                'device_equipment' => !empty($this->deviceFields['equipment']) ? trim($this->deviceFields['equipment']) : null,
                'device_identity' => !empty($this->deviceFields['identity']) ? trim($this->deviceFields['identity']) : null,
                'device_setidentity' => $requiresIdentity ? (!empty($this->deviceFields['identity']) ? trim($this->deviceFields['identity']) : null) : null,
                'device_module' => $this->deviceFields['module'] ?? 0, /* todo: number string is going to be trimmed anyway when converting into integer field, however other checks or validation might be performed */
                'device_setmodule' => $requiresModule ? ($this->deviceFields['module'] ?? 0) : null,
                'device_pin' => !empty($this->deviceFields['pin']) ? trim($this->deviceFields['pin']) : null,
                'device_enabled' => 1,
            ]);

            foreach ($this->labels as $id => $label) {
                $device->device_labels()->attach($id);
            }

            // GATEWAY
            if ($this->gateway) {
                $gateway = DeviceGateway::findOrFail($this->gateway);
                $gateway->dg_device_id = $device->device_id;
                $gateway->save();
            }

            DB::commit();

            GroupCache::forgetGroup('sites');
            GroupCache::forgetGroup('devices');
            GroupCache::forgetGroup('settings');
            GroupCache::forgetGroup('gateways');

            $this->notify('success', __('New device created'));
            return Redirect::to('/device-site/'.$this->deviceSite->ds_id);

        } catch (\Throwable $e) {
            \Log::error($e, ['Caught']);
            DB::rollback();
            $this->notify('error', __('Saving failed. Fill in required fields'));
        }
    }

    public function rules()
    {
        // MODULE rules
        $typeRules = [
            'module' => 'required|exists:modules,module_id',
            'moduleType' => 'required|exists:module_types,mt_id',
        ];

        // GATEWAY rules
        $gatewayRules = ['gateway' => 'nullable|exists:device_gateways,dg_id'];

        // DEVICE rules
        $deviceRules = [];
        foreach ($this->deviceFields as $field => $value) {
            if (in_array($field, $this->requiredFields)) {
                $deviceRules['deviceFields.'.$field] = 'required';
            }
        }

//        // ADDRESS rules
//        $addressRules = [];
//        foreach ($this->getAddressFields() as $field => $value) {
//            if (in_array('address', $this->requiredFields)) {
//                $addressRules['addressFields.'.$field] = 'required';
//            }
//            else {
//                $otherFields = array_diff(array_keys($this->getAddressFields()), [$field]);
//                $otherRequired = implode(',', array_map(fn($item) => 'addressFields.'.$item, $otherFields));
//                $addressRules['addressFields.'.$field] = 'required_with:'.$otherRequired;
//            }
//        }

        return array_merge($typeRules, $gatewayRules, $deviceRules);
    }

    public function messages()
    {
        // todo: change hardcoded string messages into keyed maessages from trans validation.php
        return [
            'module.required' => __('Module is required'),
            'module.exists' => __('Selected module is invalid'),
            'moduleType.required' => __('Device type is required'),
            'moduleType.exists' => __('Selected device type is invalid'),
            'gateway.exists' => __('Selected gateway is invalid'),
            'deviceFields.equipment.required' => __('Equipment ID is required'),
            'deviceFields.identity.required' => __('Identity is required'),
            'deviceFields.module.required' => __('Module is required'),
            'deviceFields.pin.required' => __('Pin is required'),
            'deviceFields.labels.required' => __('Labels are required'),

            'gateway.exists' => trans('Selected gateway is invalid'),

            'deviceFields.equipment.required' => 'Equipment ID is required',
            'deviceFields.identity.required' => 'Identity is required',
            'deviceFields.module.required' => 'Module is required',
            'deviceFields.pin.required' => 'Pin is required',
//            'deviceFields.tech.required' => 'Custom 1 is required',
//            'deviceFields.custom.required' => 'Custom 2 is required',
//            'deviceFields.custom3.required' => 'Custom 3 is required',
//            'deviceFields.custom4.required' => 'Custom 4 is required',
//            'deviceFields.link.required' => 'Link is required',
            'deviceFields.labels.required' => 'Labels are required',

//            'addressFields.address_value.required' => 'Address is required',
//            'addressFields.address_value.required_with' => 'Address is required',
//            'addressFields.location_postcode.required' => 'Postcode is required',
//            'addressFields.location_postcode.required_with' => 'Postcode is required',
//            'addressFields.location_value.required' => 'Location is required',
//            'addressFields.location_value.required_with' => 'Location is required',
//            'addressFields.location_country_id.required' => 'Country is required',
//            'addressFields.location_country_id.required_with' => 'Country is required',
        ];
    }

    private function getLabelOptions()
    {
//        $labels = DeviceLabel::query()
//            ->with('device_label_settings')
//            ->where('dl_account_id', '=', session('account.id'))
//            ->withDepth()
//            ->defaultOrder()
//            ->get()
//            ->toTree()
//            ->toArray();

//        return $this->mapLabels($labels); // HIDE LABELS
    }

    private function mapLabels($label)
    {
        $node = [];
        foreach ($label as $labelItem) {
            $node[] = [
                'dl_id' => $labelItem['dl_id'],
                'dl_name' => $labelItem['dl_name'],
                'children' => $this->mapLabels($labelItem['children'])
            ];
        }

        return $node;
    }

    public function attachGroup($id)
    {
        $selectedLabel = DeviceLabelOld::find($id);
        $this->labels[$id] = $selectedLabel;
        $this->deviceFields['labels'][$id] = $selectedLabel;
    }

    public function detachGroup($id)
    {
        unset($this->labels[$id]);
        unset($this->deviceFields['labels'][$id]);
    }

    // CUSTOM VALIDATIONS

    private function validateModuleType(): bool
    {
       if (empty($this->moduleType)) {
           return false;
       }

       $moduleType = ModuleType::findOrFail($this->moduleType);
       $existingDeviceTypes = $this->deviceSite->devices->pluck('module.module_type.mt_type');

       $protocolSupportsMultiple = boolval($this->deviceSite->module->module_flags & ModuleFlags::MODULE_FLAG_MULTI_SUPPORT->value);
       foreach (['TELEALARM', 'INTERCOM', 'GATEWAY'] as $deviceModuleType) {
           if ($moduleType->mt_type !== $deviceModuleType) continue;
           if (($deviceModuleType === 'GATEWAY' || !$protocolSupportsMultiple) && $existingDeviceTypes->contains($deviceModuleType)) {
               $deviceModuleType = ucfirst(strtolower($deviceModuleType));
               $this->addError('general', __('Site protocol module does not support multiple :type devices - delete existing one before adding another', [
                   'type' => $deviceModuleType
               ]));
               return false;
           }
       }

       $alreadyHaveDeviceType = $existingDeviceTypes->contains($moduleType->mt_type);
       if ($alreadyHaveDeviceType) {
           $deviceTypeModule = $this->deviceSite->devices->where('module.module_type.mt_type', $moduleType->mt_type)->first()->module;
           $moduleSupportsMultiple = boolval($deviceTypeModule->module_flags & ModuleFlags::MODULE_FLAG_MULTI_SUPPORT->value);
           if (!$moduleSupportsMultiple) {
               $deviceTypeModule = ucfirst(strtolower($deviceTypeModule->module_desc ?: $deviceTypeModule->module_name));
               $this->addError('general', __('Attached :type device module :module does not support multiple devices - delete existing one before adding another', [
                   'type' => $moduleType->mt_type,
                   'module' => $deviceTypeModule
               ]));
               return false;
           }
       }

       return true;
    }

    private function getModuleOptionsWithValidation(): false|array
    {
        if (empty($this->moduleType)) {
            return false;
        }

        $moduleOptions = $this->deviceSite->module->supported_modules
            ->where('module_mt_id', $this->moduleType)
            ->keyBy('module_id')
            ->map(fn ($module) => $module->module_desc ?: $module->module_name)
            ->toArray();

        // this is repeated - can be optimized
        $moduleType = ModuleType::findOrFail($this->moduleType);
        $existingDeviceTypes = $this->deviceSite->devices->pluck('module.module_type.mt_type');
        $alreadyHaveDeviceType = $existingDeviceTypes->contains($moduleType->mt_type);
        if ($alreadyHaveDeviceType) {
            $deviceTypeModule = $this->deviceSite->devices->where('module.module_type.mt_type', $moduleType->mt_type)->first()->module;
            $moduleOptions = array_intersect_key($moduleOptions, [$deviceTypeModule->module_id => ($deviceTypeModule->module_desc ?: $deviceTypeModule->module_name)]);
        }

        if (empty($moduleOptions)) {
            $this->addError('general', __('There are no applicable modules for the site protocol module and chosen device type'));
            return false;
        }

        return $moduleOptions;
    }

    private function integrityValidation(): bool
    {
        $errors = [];
//        if ($this->addressFields['address_value']) {
//            $location = Location::addData(
//                location: $this->addressFields['location_value'],
//                postcode: $this->addressFields['location_postcode'],
//                countryId: $this->addressFields['location_country_id'],
//                save: false,
//            );
//            $address = Address::addData(
//                address: $this->addressFields['address_value'],
//                locationId: $location->location_id,
//                save: false,
//            );
//            $errors = array_merge($errors, $this->validateAddress($this->deviceSite->module, $address, $location));
//        }
        $errors = array_merge($errors, $this->validateEquipment($this->deviceFields['equipment'], null));
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addError('general', $error);
            }
            return false;
        }

        return true;
    }
}