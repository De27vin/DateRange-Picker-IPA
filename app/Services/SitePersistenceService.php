<?php
namespace App\Services;

use App\DTO\DeviceDTO;
use App\Models\Device;
use App\Models\DeviceSite;
use App\DTO\SiteDTO;
use App\Traits\FreeswitchApiTrait;

class SitePersistenceService
{
    use FreeswitchApiTrait;

    private const SET_FIELDS = ['identity', 'module', 'pin'];
    private const SETTINGS = ['device.alarm1.number' => 'alarmNumber', 'device.periodical1.number' => 'periodicalNumber'];

    public function __construct(
        private readonly AddressService $addressService,
        private readonly CustomFieldsService $customFieldsService,
        private readonly PhoneNumbersService $numbersService,
        private readonly SettingsService $settingsService,
        private readonly NotificationsService $notifications,
        private readonly DeviceGatewayPersistenceService $gatewayService,
    ) {}

    public function persistSite(DeviceSite $siteModel, SiteDTO $siteDTO, array $options): void
    {
        $this->savePhoneNumbers($siteModel, $siteDTO, $options['updateCli'] ?? false);
        $this->saveAddress($siteModel, $siteDTO);
        $this->saveSiteDetails($siteModel, $siteDTO);
        $this->saveCustomFields($siteModel->ds_id, $siteDTO->customFields, false);
//        $this->saveSettings($siteModel, $siteDTO, 'site');  // editing alarm + periodical is disabled
        $this->saveLabels($siteModel, $siteDTO);
        $this->saveDevices($siteModel, $siteDTO);
        $this->reloadSite($siteModel);
    }

    private function savePhoneNumbers(DeviceSite $siteModel, SiteDTO $siteDTO, bool $updateCli = false): void
    {
        $reloadedNumbers = $this->numbersService->updateSitePhoneNumbers(
            $siteModel, 
            $siteDTO->phoneNumbersDTO->toArray()
        );

        if ($updateCli) {
            foreach (['sip', 'sim', 'pbx', 'pstn'] as $numType) {
                if (!empty($siteDTO->phoneNumbersDTO->$numType)) {
                    $numberValue = $siteDTO->phoneNumbersDTO->$numType;
                    $currentPlainSettings = $this->settingsService->getPlainSiteSettings($siteModel);

                    $updateSettings = [];

                    $route1Setting = $currentPlainSettings['call.alarm.route1.cli.number'];
                    $route1Setting['value'] = $numberValue;
                    $updateSettings[] = $route1Setting;

                    $outboundSetting = $currentPlainSettings['call.outbound.trunk.cli.number'];
                    $outboundSetting['value'] = $numberValue;
                    $updateSettings[] = $outboundSetting;

                    $this->settingsService->updateDeviceSiteSettings($siteModel, collect($updateSettings));
                    break;
                }
            }
        }

        $reloaded = [];
        foreach ($reloadedNumbers as $numberId) {
            $reloaded[] = $this->fsMake("ucp del number $numberId", false, true);
        }

        if (count($reloaded) && (count($reloaded) === count(array_filter($reloaded)))) {
            $this->notifications->add('info', __('Ucp reload numbers command processed'));
        } elseif (count($reloaded) && (count($reloaded) !== count(array_filter($reloaded)))) {
            $this->notifications->add('warning', __('Ucp reload numbers command failed'));
        }
    }

    private function saveAddress(DeviceSite $siteModel, SiteDTO $siteDTO): void
    {
        $siteModel->ds_address_id = $siteDTO->addressDTO->isEmpty() ? null :
            $this->addressService->getOrCreateAddress(
                $siteDTO->addressDTO->street,
                $siteDTO->addressDTO->city,
                $siteDTO->addressDTO->zip,
                $siteDTO->addressDTO->countryId
            )->address_id;
    }

    private function saveSiteDetails(DeviceSite $siteModel, SiteDTO $siteDTO): void
    {
        $siteModel->fill([
            'ds_name' => $siteDTO->dsName,
            'ds_link' => $siteDTO->dsLink,
        ])->save();
    }

    private function saveDevices(DeviceSite $siteModel, SiteDTO $siteDTO): void
    {
        foreach ($siteDTO->devices as $deviceDTO) {
            /** @var Device $deviceModel */
            $deviceModel = $siteModel->devices()->findOrFail($deviceDTO->deviceId);

            $this->updateDeviceFields($deviceModel, $deviceDTO);
            $this->saveCustomFields($deviceModel->device_id, $deviceDTO->customFields, true);
//            $this->saveSettings($deviceModel, $deviceDTO, 'device'); // editing alarm + periodical is disabled

            if ($deviceModel->can_assign_gateway) {
                $this->gatewayService->updateGatewayAssignment($deviceModel, $deviceDTO->gateway?->id);
            }
        }
    }

    private function updateDeviceFields(Device $deviceModel, DeviceDTO $deviceDTO): void
    {
        foreach (self::SET_FIELDS as $field) {
            if ($deviceModel->{"device_$field"} !== $deviceDTO->{'device'.ucfirst($field)}) {
                $deviceModel->{"device_set$field"} = $deviceDTO->{'device'.ucfirst($field)};
            }
        }

        if ($deviceModel->device_equipment !== $deviceDTO->deviceEquipment) {
            $deviceModel->device_equipment = $deviceDTO->deviceEquipment;
        }

        $deviceModel->save();
    }

    private function saveCustomFields(int $entityId, array $newFields, bool $isDevice): void
    {
        $existingFields = $this->customFieldsService->getCustomFields(
            session('account.id'),
            $entityId,
            $isDevice
        );

        foreach ($existingFields as &$field) {
            foreach ($newFields as $newField) {
                if ($field['name'] === $newField->name) {
                    $field['value'] = $newField->value;
                }
            }
        }

        $this->customFieldsService->saveCustomFields(
            $entityId,
            $existingFields,
            $isDevice
        );
    }

    private function saveSettings($entity, $dto, string $type): void
    {
        $currentSettings = $type === 'device'
            ? $this->settingsService->getPlainDeviceSettings($entity)
            : $this->settingsService->getPlainSiteSettings($entity);

        $updateSettings = [];
        foreach (self::SETTINGS as $settingKey => $dtoKey) {
            if ($currentSettings[$settingKey]['value'] !== $dto->$dtoKey) {
                $currentSettings[$settingKey]['value'] = $dto->$dtoKey;
                $updateSettings[$settingKey] = $currentSettings[$settingKey];
            }
        }

        if (!empty($updateSettings)) {
            if ($type === 'device') {
                $this->settingsService->updateDeviceSettings($entity, collect($updateSettings));
            } else {
                $this->settingsService->updateDeviceSiteSettings($entity, collect($updateSettings));
            }
        }
    }

    private function saveLabels(DeviceSite $siteModel, SiteDTO $siteDTO): void
    {
        $labelIds = array_map(fn($label) => $label['dl_id'], $siteDTO->labels);
        $siteModel->labels()->sync($labelIds);
    }

    private function reloadSite(DeviceSite $site)
    {
        $siteReload = $this->fsMake("ucp del site $site->ds_id", false, true);
        $deviceReloads = $site->devices->map(fn($d) => (bool)$this->fsMake("ucp del device $d->device_id", false, true))->toArray();
        $success = $siteReload && count($deviceReloads) === count(array_filter($deviceReloads));

        $this->notifications->add($success ? 'success' : 'warning', $success ? trans('Ucp reload site command processed') : trans('Ucp reload site command failed'));

    }
}