<?php

namespace App\Validators;

use App\DTO\PhoneNumbersDTO;
use App\Services\ProfileAccessService;
use App\Models\{DeviceSite, Number};

class PhoneNumbersValidator
{
    public function __construct(
        private readonly ProfileAccessService $profileAccess,
    ) {}

    public function validateDTO(DeviceSite $deviceSite, PhoneNumbersDTO $phoneNumbersDTO): array
    {
        $numbers = $phoneNumbersDTO->toArray();

        return array_merge(
            $this->validateRequired($deviceSite, $numbers),
            $this->validateUnique($numbers),
            $this->validateExisting($numbers, $deviceSite->ds_id)
        );
    }

    private function validateRequired(DeviceSite $deviceSite, array $numbers): array
    {
        return empty($numbers) && $this->profileAccess->isFieldRequired($deviceSite->module, 'numbers')
            ? [trans('validation.device.numbers.required')]
            : [];
    }

    private function validateUnique(array $numbers): array
    {
        return count($numbers) !== count(array_unique($numbers))
            ? [trans('validation.device.numbers.not_unique')]
            : [];
    }

    private function validateExisting(array $numbers, int $siteId): array
    {
        return Number::whereIn('number_value', $numbers)
            ->where('number_ds_id', '!=', $siteId)
            ->whereNotNull('number_ds_id')
            ->exists()
            ? [trans('validation.device.numbers.already_exists')]
            : [];
    }
}