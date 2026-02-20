<?php
namespace App\Services;

use App\DTO\SiteDTO;
use App\DTO\DeviceDTO;
use App\Models\Device;
use App\Models\DeviceSite;
use App\Validators\AddressValidator;
use App\Validators\DeviceGatewayValidator;
use App\Validators\LabelsValidator;
use App\Validators\PhoneNumbersValidator;
use App\Validators\DeviceValidator;

class SiteValidationService
{
    public function __construct(
        private readonly NotificationsService $notifications,
        private readonly AddressValidator $addressValidator,
        private readonly DeviceValidator $deviceValidator,
        private readonly PhoneNumbersValidator $phoneNumberValidator,
        private readonly DeviceGatewayValidator $deviceGatewayValidator,
        private readonly ProfileAccessService $profileAccess,
        private readonly LabelsValidator $labelsValidator,
    ) {}

    protected function performValidationNotification(string ...$validationErrors): array
    {
        foreach ($validationErrors as $error) {
            // $this->notify('error', $error);
        }
        return $validationErrors;
    }

    public function validateSiteDTO(SiteDTO $siteDTO, DeviceSite $siteModel): array
    {
        $errors = array_merge(
            $this->validateSiteLinkDTO($siteDTO, $siteModel),
            $this->addressValidator->validateDTO($siteModel->module, $siteDTO->addressDTO),
            $this->phoneNumberValidator->validateDTO($siteModel, $siteDTO->phoneNumbersDTO),
            $this->labelsValidator->validate($siteDTO->labels, $siteModel),
        );

        $this->notifications->addWithContext('error', $errors, $this->getSiteIdentifier($siteModel));

        // devices
        $errors = array_merge($errors, $this->validateDevices($siteDTO->devices, $siteModel));

        return $errors;
    }

    /** @noinspection PhpParamsInspection */
    protected function validateDevices(array $devices, DeviceSite $siteModel): array
    {
        $errors = [];

        foreach ($devices as $deviceDTO) {
            $deviceModel = $siteModel->devices()->findOrFail($deviceDTO->deviceId);
            $deviceErrors = $this->deviceValidator->validateDTO($deviceDTO, $deviceModel, $devices);
            $deviceErrors = array_merge($deviceErrors, $this->deviceGatewayValidator->validateDTO($deviceDTO, $deviceModel));
            $this->notifications->addWithContext('error', $deviceErrors, $this->getDeviceIdentifier($deviceDTO, $deviceModel));
            $errors = array_merge($errors, $deviceErrors);
        }

        return $errors;
    }

    public function validateSiteLinkDTO(SiteDTO $siteDTO, DeviceSite $site): array
    {
        if ($this->profileAccess->isFieldRequired($site->module, 'link') && empty($siteDTO->dsLink)) {
            return [trans('validation.device_site.link.required')];
        }

        return [];
    }

    private function getSiteIdentifier(DeviceSite $siteModel): string
    {
        return __('Site ') . ($siteModel->ds_name ?: '');
    }

    private function getDeviceIdentifier(DeviceDTO $deviceDTO, Device $deviceModel): string
    {
        return $deviceModel->device_equipment ?? $deviceDTO->deviceEquipment ?? '#' . ($deviceDTO->deviceId ?? '');
    }

}