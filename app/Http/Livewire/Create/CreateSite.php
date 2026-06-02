<?php
namespace App\Http\Livewire\Create;

use App\Enum\ModuleFlags;
use App\Helpers\GroupCache;
use App\Models\Address;
use App\Models\DeviceSite;
use App\Models\Location;
use App\Models\Module;
use App\Models\Number;
use App\Models\NumberType;
use App\Services\DeviceFormFieldsService;
use App\Services\PhoneNumbersService;
use App\Services\SettingsService;
use App\Traits\AccountsTrait;
use App\Traits\DeviceFormTrait;
use App\Traits\TranslationsTrait;
use App\Traits\ValidationTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;

class CreateSite extends Component
{
    use AccountsTrait;
    use ValidationTrait;
    use DeviceFormTrait;
    use TranslationsTrait;

    public $name;
    public $module;
    public $pstn;
    public $sim;
    public $pbx;
    public $sip;
    public $link;
    public $copyNumberToCli = true;
    public $moduleOptions;
    public $numberTypes;
    public $numbersRequired = false;
    public $addressFields;
    public $addressRequired = false;
    public $requiredFields = [];
    public $customFields;
    public $fieldSettings = [];
    public $sipSupported = false;
    public $fieldTranslations;
    public $locale;
    public $countries;

    public $sipOptions;


    private PhoneNumbersService $numbersService;
    private DeviceFormFieldsService $formFieldsService;
    private SettingsService $settingsService;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->numbersService = new PhoneNumbersService();
        $this->formFieldsService = new DeviceFormFieldsService();
        $this->settingsService = new SettingsService();
    }

    public function mount()
    {
        $this->locale = session('locale', 'default');
        $this->account = $this->getCurrentAccount();
//        $this->moduleOptions = $this->account->modules // THIS WILL BE BACK - BELOW IS STUB
        $this->moduleOptions = Module::all()
            ->filter(fn($m) => $m->module_type->mt_type === 'PROTOCOL')
            ->keyBy('module_id')
            ->map(fn ($module) => $module->module_desc ?: $module->module_name)
            ->toArray();
        $this->fieldTranslations = $this->getFieldTranslations($this->locale);
        $this->numberTypes = ['sip', 'sim', 'pbx', 'pstn'];
        $this->addressFields = $this->getAddressFields();
        $this->customFields = $this->getCustomFields();
        $this->countries = $this->getCountryList();

        $this->sipOptions = Number::query()
            ->whereHas('number_type', function($query) {
                $query->where('nt_type', '=', 'SIP');
            })
            ->where('number_account_id', session('account.id'))
            ->whereNull('number_ds_id')
            ->get()
            ->mapWithKeys(function (Number $number) {
                return [$number->number_id => $number->number_value];
            })
            ->toArray();
    }

    public function render()
    {
//        $this->validate($this->rules(),$this->messages());
        return view('livewire.create.create-site');
    }

    public function cancel()
    {
        redirect('/equipment');
    }

    private function getAddressFields()
    {
        return [
            'address_value'       => null,
            'location_postcode'   => null,
            'location_value'      => null,
            'location_country_id' => null,
        ];
    }

    private function getCustomFields()
    {
        return [];
    }

    public function create()
    {
        $this->validate($this->rules(),$this->messages());

        if ($this->performValidationNotification(...$this->addressValidation())) {
            return;
        }

        $numbersNotAvailable = [];
        if (!empty($this->pstn)) {
             // this preg_replace is most likely to remove as it is inside checkAvailabilityAndReturn
            $pstn = preg_replace('/\s+/', '', $this->pstn);
            $pstn = $this->numbersService->checkAvailabilityAndReturn($pstn);
            if ($pstn === false) {
                $numbersNotAvailable[] = 'pstn';
            }
        }
        if (!empty($this->sim)) {
            $sim = preg_replace('/\s+/', '', $this->sim);
            $sim = $this->numbersService->checkAvailabilityAndReturn($sim);
            if ($sim === false) {
                $numbersNotAvailable[] = 'sim';
            }
        }
        if (!empty($this->pbx)) {
            $pbx = preg_replace('/\s+/', '', $this->pbx);
            $pbx = $this->numbersService->checkAvailabilityAndReturn($pbx);
            if ($pbx === false) {
                $numbersNotAvailable[] = 'pbx';
            }
        }
        if (!empty($this->sip)) {
            $sip = preg_replace('/\s+/', '', $this->sip);
            $sip = $this->numbersService->checkAvailabilityAndReturn($sip);
            if ($sip === false) {
                $numbersNotAvailable[] = 'sip';
            }
        }

        if (!empty($numbersNotAvailable)) {
            foreach ($numbersNotAvailable as $type) {
                $this->addError($type, __('Provided number is already in use'));
            }
            return;
        }


        $accountId = session('account.id');
        DB::beginTransaction();
        try {
            // ADDRESS
            if ($this->addressFields['address_value']) {
                // todo: do address validation
                $location = Location::addData(
                    location: $this->addressFields['location_value'],
                    postcode: $this->addressFields['location_postcode'],
                    countryId: $this->addressFields['location_country_id'],
                );
                $address = Address::addData(
                    address: $this->addressFields['address_value'],
                    locationId: $location->location_id,
                );
            }

            $deviceSite = DeviceSite::create([
                'ds_name' => trim($this->name),
                'ds_protocol_id' => $this->module,
                'ds_account_id' => $accountId,
                'ds_address_id' => $address->address_id ?? null,
                'ds_link' => trim($this->link) ?: null,
            ]);
            $deviceSite->save();

            if (!empty($pstn)) {
                if (is_string($pstn)) {
                    Number::create([
                        'number_ds_id' => $deviceSite->ds_id,
                        'number_account_id' => $accountId,
                        'number_nt_id' => NumberType::where('nt_type', 'PSTN')->first()->nt_id,
                        'number_value' => trim($pstn),
                    ]);
                } elseif ($pstn instanceof Number) {
                    $pstn->number_ds_id = $deviceSite->ds_id;
                    $pstn->number_account_id = $accountId;
                    $pstn->number_nt_id = NumberType::where('nt_type', 'PSTN')->first()->nt_id;
                    $pstn->save();
                }
            }
            if (!empty($sim)) {
                if (is_string($sim)) {
                    Number::create([
                        'number_ds_id' => $deviceSite->ds_id,
                        'number_account_id' => $accountId,
                        'number_nt_id' => NumberType::where('nt_type', 'SIM')->first()->nt_id,
                        'number_value' => trim($sim),
                    ]);
                } elseif ($sim instanceof Number) {
                    $sim->number_ds_id = $deviceSite->ds_id;
                    $sim->number_account_id = $accountId;
                    $sim->number_nt_id = NumberType::where('nt_type', 'SIM')->first()->nt_id;
                    $sim->save();
                }
            }
            if (!empty($pbx)) {
                if (is_string($pbx)) {
                    Number::create([
                        'number_ds_id' => $deviceSite->ds_id,
                        'number_account_id' => $accountId,
                        'number_nt_id' => NumberType::where('nt_type', 'PBX')->first()->nt_id,
                        'number_value' => trim($pbx),
                    ]);
                } elseif ($pbx instanceof Number) {
                    $pbx->number_ds_id = $deviceSite->ds_id;
                    $pbx->number_account_id = $accountId;
                    $pbx->number_nt_id = NumberType::where('nt_type', 'PBX')->first()->nt_id;
                    $pbx->save();
                }
            }
            if (!empty($sip)) {
                if (is_string($sip)) {
                    Number::create([
                        'number_ds_id' => $deviceSite->ds_id,
                        'number_account_id' => $accountId,
                        'number_nt_id' => NumberType::where('nt_type', 'SIP')->first()->nt_id,
                        'number_value' => trim($sip),
                    ]);
                } elseif ($sip instanceof Number) {
                    $sip->number_ds_id = $deviceSite->ds_id;
                    $sip->number_account_id = $accountId;
                    $sip->number_nt_id = NumberType::where('nt_type', 'SIP')->first()->nt_id;
                    $sip->save();
                }
            }

            if ($this->copyNumberToCli) {
                foreach (['sip', 'sim', 'pbx', 'pstn'] as $numType) {
                    if (!empty($$numType)) {
                        $numberValue = is_string($$numType) ? $$numType : $$numType->number_value;
                        $currentPlainSettings = $this->settingsService->getPlainSiteSettings($deviceSite);

                        $updateSettings = [];

                        $route1Setting = $currentPlainSettings['call.alarm.route1.cli.number'];
                        $route1Setting['value'] = $numberValue;
                        $updateSettings[] = $route1Setting;

                        $outboundSetting = $currentPlainSettings['call.outbound.trunk.cli.number'];
                        $outboundSetting['value'] = $numberValue;
                        $updateSettings[] = $outboundSetting;

                        $this->settingsService->updateDeviceSiteSettings($deviceSite, collect($updateSettings));
                        break;
                    }
                }
            }


            DB::commit();

            GroupCache::forgetGroup('sites');
            GroupCache::forgetGroup('devices');
            GroupCache::forgetGroup('settings');
            GroupCache::forgetGroup('numbers');


            $this->notify('success', __('New site created'));
            return Redirect::to('/device-site/'.$deviceSite->ds_id);

        } catch (\Throwable $e) {
            \Log::error($e, ['Caught']);
            DB::rollback();
            $this->notify('error', __('Saving failed. Fill in required fields'));
        }
    }

    public function updatedModule()
    {
        if (empty($this->module)) {
            return;
        }
        $this->resetValidation();
        $this->selectedModule = Module::findOrFail($this->module);
        $this->requiredFields = $this->formFieldsService->getRequiredFields($this->selectedModule);
        $this->numbersRequired = $this->formFieldsService->isFieldRequired($this->selectedModule, 'numbers');
        $this->addressRequired = $this->formFieldsService->isFieldRequired($this->selectedModule, 'address');
        $this->fieldSettings = $this->formFieldsService->getFieldsSettings($this->selectedModule);
        $this->sipSupported = boolval($this->selectedModule->module_flags & ModuleFlags::MODULE_FLAG_SIP_SUPPORT->value);
    }

    protected function rules()
    {
        $rules = [];
        $rules = Arr::add($rules, 'module', 'required');
        $rules = Arr::add($rules, 'name', 'nullable');

        if($this->numbersRequired){
            $rules = Arr::add($rules, 'pstn', 'sometimes|nullable|required_without_all:sim,pbx,sip|different:sim,pbx,sip');
            $rules = Arr::add($rules, 'sim', 'sometimes|nullable|required_without_all:pstn,pbx,sip|different:pstn,pbx,sip');
            $rules = Arr::add($rules, 'pbx', 'sometimes|nullable|required_without_all:pstn,sim,sip|different:pstn,sim,sip');
            $rules = Arr::add($rules, 'sip', 'sometimes|nullable|required_without_all:pstn,sim,pbx|different:pstn,sim,pbx');
        } else {
            $rules = Arr::add($rules, 'pstn', 'nullable|different:sim,pbx,sip');
            $rules = Arr::add($rules, 'sim', 'nullable|different:pstn,pbx,sip');
            $rules = Arr::add($rules, 'pbx', 'nullable|different:pstn,sim,sip');
            $rules = Arr::add($rules, 'sip', 'nullable|different:pstn,sim,pbx');
        }

        // ADDRESS rules
        foreach ($this->getAddressFields() as $field => $value) {
            if ($this->addressRequired) {
                $rules['addressFields.'.$field] = 'required';
            }
            else {
                $otherFields = array_diff(array_keys($this->getAddressFields()), [$field]);
                $otherRequired = implode(',', array_map(fn($item) => 'addressFields.'.$item, $otherFields));
                $rules['addressFields.'.$field] = 'required_with:'.$otherRequired;
            }
        }

        // CUSTOM RULES
        foreach ($this->getCustomFields() as $field => $value) {
            if (!empty($this->fieldSettings[$field]['required'])) {
                $rules['customFields.'.$field] = 'required';
            }
        }

        $rules['link'] = !empty($this->fieldSettings['link']['required']) ? 'required' : 'nullable';


        return Arr::dot($rules);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'module.required' => __('Module is required'),

            'pstn.required' => __('At least one number is required'),
            'pstn.required_without_all' => __('At least one number is required'),
            'pstn.unique' => __('Number already exists'),
            'pstn.distinct' => __('Number is not unique'),
            'pstn.different' => __('Number is not unique'),

            'sim.required' => __('At least one number is required'),
            'sim.required_without_all' => __('At least one number is required'),
            'sim.unique' => __('Number already exists'),
            'sim.distinct' => __('Number is not unique'),
            'sim.different' => __('Number is not unique'),

            'pbx.required' => __('At least one number is required'),
            'pbx.required_without_all' => __('At least one number is required'),
            'pbx.unique' => __('Number already exists'),
            'pbx.distinct' => __('Number is not unique'),
            'pbx.different' => __('Number is not unique'),

            'sip.required' => __('At least one number is required'),
            'sip.required_without_all' => __('At least one number is required'),
            'sip.unique' => __('Number already exists'),
            'sip.distinct' => __('Number is not unique'),
            'sip.different' => __('Number is not unique'),

            'addressFields.address_value.required' => __('Address is required'),
            'addressFields.address_value.required_with' => __('Address is required'),
            'addressFields.location_postcode.required' => __('Postcode is required'),
            'addressFields.location_postcode.required_with' => __('Postcode is required'),
            'addressFields.location_value.required' => __('Location is required'),
            'addressFields.location_value.required_with' => __('Location is required'),
            'addressFields.location_country_id.required' => __('Country is required'),
            'addressFields.location_country_id.required_with' => __('Country is required'),

            'link.required' => __('Link is required')
        ];
    }

    private function addressValidation(): array
    {
        $errors = [];
        if ($this->addressFields['address_value']) {
            $location = Location::addData(
                location: $this->addressFields['location_value'],
                postcode: $this->addressFields['location_postcode'],
                countryId: $this->addressFields['location_country_id'],
                save: false,
            );
            $address = Address::addData(
                address: $this->addressFields['address_value'],
                locationId: $location->location_id,
                save: false,
            );
            $errors = array_merge($errors, $this->validateAddress($this->selectedModule, $address, $location));
        }

        return $errors;
    }
}