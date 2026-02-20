<?php
namespace App\Traits;

use App\DTO\PhoneNumbersDTO;
use App\Models\Device;
use App\Models\Address;
use App\Models\DeviceGateway;
use App\Models\DeviceSite;
use App\Models\Location;
use App\Models\Module;
use App\Models\Number;
use Illuminate\Support\Str;

// todo: This should be service
trait ValidationTrait
{
    use TranslationsTrait;
    use DeviceFormTrait;

    protected function performValidationNotification(string ...$validationErrors): array
    {
        foreach ($validationErrors as $error) {
            $this->notify('error', $error);
        }
        return $validationErrors;
    }

    protected function validatePhoneNumbersDTO(DeviceSite $deviceSite, PhoneNumbersDTO $phoneNumbersDTO): array
    {
        $errors = [];
        $module = $deviceSite->module;

        $areNumbersRequired = $this->getProfileData()['config']['modules'][$module->module_name]['device']['field']['numbers']['required'];
        $presentNumbers = $phoneNumbersDTO->toArray();

        if (empty($presentNumbers) && $areNumbersRequired) {
            $errors[] = trans('validation.device.numbers.required');
        }

        $existingNumbers = Number::whereIn('number_value', $presentNumbers)->get();
        foreach ($existingNumbers as $number) {
            if (!empty($number->number_ds_id) && $number->number_ds_id != $deviceSite->ds_id) {
                $errors[]  = trans('validation.device.numbers.already_exists');
            }
        }

        if (count($presentNumbers) !== count(array_unique($presentNumbers))) {
            $errors[]  = trans('validation.device.numbers.not_unique');
        }

        return $errors;
    }

    protected function validatePhoneNumbers(DeviceSite $deviceSite, array $phoneNumbers): array
    {
        $errors = [];
        $module = $deviceSite->module;

        $areNumbersRequired = $this->getProfileData()['config']['modules'][$module->module_name]['device']['field']['numbers']['required'];

        $presentNumbers = array_filter(
            $phoneNumbers,
            fn($value, $field) => $this->isFieldPhoneNumber($field) && !empty($value),
            ARRAY_FILTER_USE_BOTH
        );

        if (empty($presentNumbers) && $areNumbersRequired) {
            $errors[] = trans('validation.device.numbers.required');
        }

        $existingNumbers = Number::whereIn('number_value', $presentNumbers)->get();
        foreach ($existingNumbers as $number) {
            if (!empty($number->number_ds_id) && $number->number_ds_id != $deviceSite->ds_id) {
                $errors[]  = trans('validation.device.numbers.already_exists');
            }
        }

        if (count($presentNumbers) !== count(array_unique($presentNumbers))) {
            $errors[]  = trans('validation.device.numbers.not_unique');
        }

        return $errors;
    }

    // todo: This rule might be reviewed as if the address requirement should come from protocol or device module
    protected function validateAddress(Device|Module|DeviceSite $deviceOrModuleOrSite, Address $address, Location $location): array
    {
        $errors = [];
        if ($deviceOrModuleOrSite instanceof Device) {
            $module = $deviceOrModuleOrSite->device_site->module;
        } elseif ($deviceOrModuleOrSite instanceof DeviceSite) {
            $module = $deviceOrModuleOrSite->module;
        } elseif ($deviceOrModuleOrSite instanceof Module) {
            $module = $deviceOrModuleOrSite;
        }

        $isAddressFullyMissing = empty($address->address_value)
            && empty($location->location_value)
            && empty($location->location_postcode)
            && empty($location->location_country_id);

        $isAddressFullyPresent = !empty($address->address_value)
            && !empty($location->location_value)
            && !empty($location->location_postcode)
            && !empty($location->location_country_id);

        $isAddressRequired = $this->getProfileData()['config']['modules'][$module->module_name]['device']['field']['address']['required'];

        // new approach
        if (($isAddressRequired && !$isAddressFullyPresent)) {
            $errors[] = trans('Full address is required.');

        } elseif ((!$isAddressFullyMissing && !$isAddressFullyPresent)) {
            if (empty($location->location_country_id)) {
                $errors[] = trans('Address is incomplete. Country is required');
            }
            if (empty($location->location_value) && empty($location->location_postcode)) {
                $errors[] = trans('Address is incomplete. City or Zip is required');
            }

        } else {
             $existingLocation = Location::where([
                ['location_value', $location->location_value],
                ['location_postcode', $location->location_postcode],
            ])->first();

            if ($existingLocation && $existingLocation->location_country_id != $location->location_country_id) {
                $errors[] = trans('validation.device.device_address.location_exists');
            }

            if (!preg_match("/^[\p{L}0-9 ',.\-()\/]{1,35}$/u", $address->address_value)) {
                $errors[] = trans('Street field contains invalid characters');
            }
            if (!preg_match("/^[\p{L}0-9 ',.\-()\/]{1,35}$/u", $location->location_value)) {
                $errors[] = trans('City field contains invalid characters');
            }
            if (!$this->alpha_num($location->location_postcode)) {
                $errors[] = trans('validation.device.device_address.postcode_wrong_input');
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

        if (empty($device->device_identity) || empty($device->device_module)) {
            return $errors;
        }

        $existingDevice = Device::where([
            'device_identity' => $device->device_identity,
            'device_module' => $device->device_module,
        ])->first();

        if ($existingDevice && $existingDevice->device_id != $device->device_id) {
            $errors[]  = trans('validation.device.device_identity.already_exists');
        }

        return $errors;
    }

    protected function validateEquipment(?string $equipment, int|string|null $deviceId): array
    {
        $errors = [];

        if (empty($equipment)) {
            $errors[] = trans('validation.device.device_equipment.required');
        }

        if (empty($equipment)) {
            return $errors;
        }

        $existingDevice = Device::where([
            'device_equipment' => $equipment,
        ])->first();

        if ($existingDevice && $existingDevice->device_id != $deviceId) {
            $errors[]  = trans('validation.device.device_equipment.already_exists');
        }

        return $errors;
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

    protected function alpha_num($value)
    {
        return preg_match("/^[[:alnum:]\-_]+$/", $value);
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
}