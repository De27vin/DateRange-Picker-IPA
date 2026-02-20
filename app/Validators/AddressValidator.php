<?php

namespace App\Validators;

use App\DTO\AddressDTO;
use App\Services\ProfileAccessService;
use App\Models\{Module, Location};

class AddressValidator
{
    public function __construct(
        private readonly ProfileAccessService $profileAccess,
    ) {}

    public function validateDTO(Module $module, AddressDTO $addressDTO): array
    {
        return $this->validateRequirements($addressDTO, $module) ?:
               $this->validateWithRegex($addressDTO) ?:
               $this->validateLocation($addressDTO) ?:
               [];
//        return array_merge(
//            $this->validateRequirements($addressDTO, $module),
//            $this->validateWithRegex($addressDTO),
//            $this->validateLocation($addressDTO)
//        );
    }

    private function validateRequirements(AddressDTO $addressDTO, Module $module): array
    {
        $isAddressRequired = $this->profileAccess->isFieldRequired($module, 'address');

        if ($isAddressRequired && $addressDTO->isEmpty()) {
           return [trans('Address is required.')];
        }

        if (!$addressDTO->isEmpty() && !$addressDTO->isComplete()) {
            return [trans('Full address is required.')];
        }

        return [];
    }

    private function validateWithRegex(AddressDTO $addressDTO): array
    {
        $errors = [];
        $fieldPattern = "/^[\p{L}0-9 ',.\-()\/]{1,35}$/u";

        foreach (['street', 'city'] as $field) {
            if (!preg_match($fieldPattern, $addressDTO->$field)) {
//                $errors[] = trans("$field field contains invalid characters");
            }
        }

        // zip
        if (!preg_match("/^[[:alnum:]\-_]+$/", $addressDTO->zip)) {
//            $errors[] = trans('validation.device.device_address.postcode_wrong_input');
        }

        return $errors;
    }


    private function validateLocation(AddressDTO $addressDTO): array
    {
        return Location::where('location_value', $addressDTO->city)
            ->where('location_postcode', $addressDTO->zip)
            ->where('location_country_id', '!=', $addressDTO->countryId)
            ->exists()
            ? [trans('validation.device.device_address.location_exists')]
            : [];
    }

}