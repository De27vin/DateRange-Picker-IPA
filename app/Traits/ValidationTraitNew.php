<?php
namespace App\Traits;

use App\DTO\SiteDTO;
use App\DTO\DeviceDTO;
use App\DTO\AddressDTO;
use App\DTO\PhoneNumbersDTO;
use App\Models\Device;
use App\Models\Address;
use App\Models\DeviceGateway;
use App\Models\DeviceSite;
use App\Models\Location;
use App\Models\Module;
use App\Models\Number;
use Illuminate\Support\Str;

trait ValidationTraitNew
{
    use TranslationsTrait;
    use DeviceFormTrait;

    protected function performValidationNotification(string ...$validationErrors): array
    {
        foreach ($validationErrors as $error) {
//            $this->notify('error', $error);
        }
        return $validationErrors;
    }

    protected function validateSiteDTO(SiteDTO $siteDTO, DeviceSite $siteModel): array
    {
        $siteIdentifier = $siteModel->ds_name ?: $siteDTO->dsName;
        $errors = array_merge(
            $this->validateAddressDTO($siteModel->module, $siteDTO->addressDTO),
            $this->validatePhoneNumbersDTO($siteModel, $siteDTO->phoneNumbersDTO),
            $this->validateDevices($siteDTO->devices, $siteModel)
        );

        $this->notifications->addWithContext('error', $errors, __('Site :name', ['name' => $siteIdentifier]));
        return $errors;
    }

    protected function validateDevices(array $devices, DeviceSite $siteModel): array
    {
        return array_reduce($devices, function ($errors, $deviceDTO) use ($siteModel) {
            $deviceErrors = $this->validateDeviceDTO($deviceDTO, $siteModel);
            $deviceIdentifier = $this->getDeviceIdentifier($deviceDTO, $siteModel);
            $this->notifications->addWithContext('error', $deviceErrors, __('Device :name', ['name' => $deviceIdentifier]));
            return array_merge($errors, $deviceErrors);
        }, []);
    }

    private function getDeviceIdentifier(DeviceDTO $deviceDTO, DeviceSite $siteModel): string
    {
        $deviceModel = $siteModel->devices()->find($deviceDTO->deviceId);
        return $deviceModel->device_equipment ?? $deviceDTO->deviceEquipment ?? '#' . ($deviceDTO->deviceId ?? '');
    }

    protected function validateDeviceDTO(DeviceDTO $deviceDTO, DeviceSite $deviceSite): array
    {
        return array_merge(
            $this->validateEquipment($deviceDTO->deviceEquipment, $deviceDTO->deviceId),
            $this->validateIdentityFromDTO($deviceDTO, $deviceSite),
            $this->validateDevicePin($deviceDTO, $deviceSite->module),
            $this->validateDeviceModule($deviceDTO, $deviceSite->module)
        );
    }

    protected function validateDevicePin(DeviceDTO $deviceDTO, Module $module): array
    {
        if (!$this->isFieldRequired($module, 'pin')) {
            return [];
        }

        return empty($deviceDTO->devicePin)
            ? [trans('validation.device.device_pin.required')]
            : [];
    }

    protected function validateDeviceModule(DeviceDTO $deviceDTO, Module $module): array
    {
        if (!$this->isFieldRequired($module, 'module')) {
            return [];
        }

        return !in_array($deviceDTO->deviceModule, [0, '0'], true)
            ? [trans('validation.device.device_module.required')]
            : [];
    }

    protected function validateIdentityFromDTO(DeviceDTO $deviceDTO, DeviceSite $deviceSite): array
    {
        $errors = [];
        $isIdentityRequired = $this->isFieldRequired($deviceSite->module, 'identity');

        if (empty($deviceDTO->deviceIdentity) && $isIdentityRequired) {
            $errors[] = trans('validation.device.device_identity.required');
        }

        if (!empty($deviceDTO->deviceIdentity) && !empty($deviceDTO->deviceModule)) {
            $existingDevice = Device::where('device_identity', $deviceDTO->deviceIdentity)
                ->where('device_module', $deviceDTO->deviceModule)
                ->where('device_id', '!=', $deviceDTO->deviceId)
                ->exists();

            if ($existingDevice) {
                $errors[] = trans('validation.device.device_identity.already_exists');
            }
        }

        return $errors;
    }

    private function isFieldRequiredForModule(Module $module, string $field): bool
    {
        return $this->getProfileData()['config']['modules'][$module->module_name]['device']['field'][$field]['required'] ?? false;
    }

    protected function validateEquipment(string $equipment, int $deviceId): array
    {
        $errors = [];

        if (empty($equipment)) {
            $errors[] = trans('validation.device.device_equipment.required');
        } else {
            $existingDevice = Device::where(['device_equipment' => $equipment])->first();

            if ($existingDevice?->device_id !== $deviceId) {
                $errors[]  = trans('validation.device.device_equipment.already_exists');
            }
        }

        return $errors;
    }

    protected function validateIdentity(Device $device): array
    {
        $errors = [];
        $module = $device->module ?? $device->device_site->module;
        $isIdentityRequired = $this->getProfileData()['config']['modules'][$module->module_name]['device']['field']['identity']['required'];

        if (empty($device->device_identity) && ($isIdentityRequired)) {
            $errors[] = trans('validation.device.device_identity.required');
        }

        if (!empty($device->device_identity) && !empty($device->device_module)) {
            $existingDevice = Device::where([
                'device_identity' => $device->device_identity,
                'device_module' => $device->device_module,
            ])->first();

            if ($existingDevice && $existingDevice->device_id != $device->device_id) {
                $errors[]  = trans('validation.device.device_identity.already_exists');
            }
        }

        return $errors;
    }

    protected function validatePhoneNumbersDTO(DeviceSite $deviceSite, PhoneNumbersDTO $phoneNumbersDTO): array
    {
        $errors = [];
        $presentNumbers = $phoneNumbersDTO->toArray();
        $areNumbersRequired = $this->isFieldRequired($deviceSite->module, 'numbers');

        if (empty($presentNumbers) && $areNumbersRequired) {
            return [trans('validation.device.numbers.required')];
        }

        if (count($presentNumbers) !== count(array_unique($presentNumbers))) {
            $errors[] = trans('validation.device.numbers.not_unique');
        }

        if ($this->hasExistingNumbers($presentNumbers, $deviceSite->ds_id)) {
            $errors[] = trans('validation.device.numbers.already_exists');
        }

        return $errors;
    }

    private function hasExistingNumbers(array $numbers, int $siteId): bool
    {
        return Number::whereIn('number_value', $numbers)
            ->where('number_ds_id', '!=', $siteId)
            ->whereNotNull('number_ds_id')
            ->exists();
    }


    protected function validateAddressDTO($module, AddressDTO $addressDTO): array
    {
        if ($addressDTO->isEmpty()) {
            return [];
        }

        $errors = [];
        $isAddressRequired = $this->isFieldRequiredForModule($module, 'address');

        if ($isAddressRequired && !$addressDTO->isComplete()) {
            $errors[] = trans('Full address is required.');
        } elseif (!$addressDTO->isComplete()) {
            if (empty($addressDTO->countryId)) {
                $errors[] = trans('Address is incomplete. Country is required');
            }
            if (empty($addressDTO->city) && empty($addressDTO->zip)) {
                $errors[] = trans('Address is incomplete. City or Zip is required');
            }
        }

        $errors = array_merge($errors, $this->validateAddressFields($addressDTO));

        return $errors;
    }

    private function validateAddressFields(AddressDTO $addressDTO): array
    {
        $errors = [];
        $fieldPattern = "/^[\p{L}0-9 ',.\-()\/]{1,35}$/u";

        foreach (['street', 'city'] as $field) {
            if (!preg_match($fieldPattern, $addressDTO->$field)) {
                $errors[] = __('The field :field contains invalid characters', ['field' => $field]);
            }
        }

        if (!$this->alpha_num($addressDTO->zip)) {
            $errors[] = trans('validation.device.device_address.postcode_wrong_input');
        }

        if ($this->isExistingLocation($addressDTO)) {
            $errors[] = trans('validation.device.device_address.location_exists');
        }

        return $errors;
    }

    private function isExistingLocation(AddressDTO $addressDTO): bool
    {
        return Location::where('location_value', $addressDTO->city)
            ->where('location_postcode', $addressDTO->zip)
            ->where('location_country_id', '!=', $addressDTO->countryId)
            ->exists();
    }

    protected function validateMacAddress(?string $macAddress, bool $shouldExist = false)
    {
        $errors = [];

        if (empty($macAddress)) {
            return $errors;
        }

        // can be potentially checked if exists on another account but trashed
        if (!$shouldExist && $gateway = DeviceGateway::query()->withoutGlobalScopes()->where('dg_mac', $macAddress)->first()) {
            if ($gateway->dg_account_id != session('account.id') || !$gateway->trashed()) {
                $errors[] = trans('validation.mac_address.exists', ['mac_address' => $macAddress]);
            }
        }
        if ($shouldExist && ! DeviceGateway::where('dg_mac', $macAddress)->first()) {
            $errors[] = trans('validation.mac_address.exists', ['mac_address' => $macAddress]);
        }

        $match = [];
        preg_match('/^([0-9a-f]{2}){6}$/', $macAddress, $match);
        if (!count($match)) {
            $errors[] = trans('validation.mac_address.invalid');
        }

        return $errors;
    }

    protected function validateImei(?string $imei, bool $shouldExist = false)
    {
        $errors = [];

        if (empty($imei)) {
            return $errors;
        }

        // can be potentially checked if exists on another account but trashed
        if (!$shouldExist && $gateway = DeviceGateway::query()->withoutGlobalScopes()->where('dg_imei', $imei)->first()) {
            if ($gateway->dg_account_id != session('account.id') || !$gateway->trashed()) {
                $errors[] = trans('validation.imei.exists', ['imei' => $imei]);
            }
        }
        if ($shouldExist && ! DeviceGateway::where('dg_imei', $imei)->first()) {
            $errors[] = trans('validation.imei.exists', ['imei' => $imei]);
        }

        if (!$this->is_imei($imei)) {
            $errors[] = trans('validation.imei.invalid');
        }

        return $errors;
    }

    protected function isFieldPhoneNumber(string $fieldName): bool
    {
        return Str::contains($fieldName, ['pstn', 'sim', 'sip', 'pbx']);
    }

    private function isFieldRequired($module, string $field): bool
    {
        return $this->getProfileData()['config']['modules'][$module->module_name]['device']['field'][$field]['required'];
    }

    protected function alpha_space($value)
    {
        return preg_match('/^[a-z0-9 .\-]+$/i', $value);
    }

    protected function is_luhn($n) {
        $str = '';
        foreach (str_split(strrev((string) $n)) as $i => $d) {
            $str .= $i %2 !== 0 ? $d * 2 : $d;
        }
        return array_sum(str_split($str)) % 10 === 0;
    }

    protected function is_imei($n){
        return $this->is_luhn($n) && strlen($n) == 15;
    }

    protected function alpha_num($value): bool
    {
        return preg_match("/^[[:alnum:]\-_]+$/", $value);
    }

}