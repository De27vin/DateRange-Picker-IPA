<?php
namespace App\Validators;

use App\DTO\DeviceDTO;
use App\Services\ProfileAccessService;
use App\Models\{Device, DeviceSite, Module};

class DeviceValidator
{
    public function __construct(
        private readonly ProfileAccessService $profileAccess,
    ) {}

    public function validateDTO(DeviceDTO $deviceDTO, Device $device, array $allSiteDeviceDTOs): array
    {
        return array_merge(
            $this->validateDeviceEquipmentDTO($deviceDTO),
            $this->validateDeviceIdentityDTO($deviceDTO, $device),
            $this->validateDeviceModuleDTO($deviceDTO, $device->device_site->module),
            $this->validateDevicePinDTO($deviceDTO, $device)
        );
    }

    private function validateDeviceEquipmentDTO(DeviceDTO $deviceDTO): array
    {
        if (empty($deviceDTO->deviceEquipment)) {
            return [trans('validation.device.device_equipment.required')];
        }

        // Note: device_equipment doesn't have a setfield variant
        return Device::where('device_equipment', $deviceDTO->deviceEquipment)
            ->where('device_id', '!=', $deviceDTO->deviceId)
            ->exists()
            ? [trans('validation.device.device_equipment.already_exists')]
            : [];
    }

    protected function validateDeviceIdentityDTO(DeviceDTO $deviceDTO, Device $device): array
    {
        $isIdentityRequired = $this->profileAccess->isFieldRequired($device->device_site->module, 'identity');

        if ($isIdentityRequired && empty($deviceDTO->deviceIdentity) && $deviceDTO->deviceIdentity !== '0') {
            return [trans('validation.device.device_identity.required')];
        }

        if ($deviceDTO->deviceIdentity === null) {
            return [];
        }

        // Check against both regular and set fields
        return Device::where('device_id', '!=', $deviceDTO->deviceId)
            ->where('device_module', $deviceDTO->deviceModule)
            ->where(function($query) use ($deviceDTO) {
                $query->where('device_identity', $deviceDTO->deviceIdentity)
                      ->orWhere('device_setidentity', $deviceDTO->deviceIdentity);
            })
            ->exists()
            ? [trans('validation.device.device_identity_or_set.already_exists')]
            : [];
    }

    private function validateDeviceModuleDTO(DeviceDTO $deviceDTO, Module $module): array
    {
        $isModuleRequired = $this->profileAccess->isFieldRequired($module, 'module');

        if ($isModuleRequired && empty($deviceDTO->deviceModule) && $deviceDTO->deviceModule !== 0) {
            return [trans('validation.device.device_module.required')];
        }

        // For module, we need to check if changing it would conflict with any identities. This includes both regular identity and setidentity fields
//        if ($deviceDTO->deviceModule !== null) {
//            $conflictExists = Device::where('device_id', '!=', $deviceDTO->deviceId)
//                ->where(function($query) use ($deviceDTO) {
//                    $query->where('device_identity', $deviceDTO->deviceIdentity)
//                          ->orWhere('device_setidentity', $deviceDTO->deviceIdentity);
//                })
//                ->where(function($query) use ($deviceDTO) {
//                    $query->where('device_module', $deviceDTO->deviceModule)
//                          ->orWhere('device_setmodule', $deviceDTO->deviceModule);
//                })
//                ->exists();
//
//            if ($conflictExists) {
//                return [trans('validation.device.device_module.uniqueness_violation')];
//            }
//        }

        return [];
    }

    private function validateDevicePinDTO(DeviceDTO $deviceDTO, Device $device): array
    {
        $isPinRequired = $this->profileAccess->isFieldRequired($device->device_site->module, 'pin');

        if (empty($deviceDTO->devicePin) && $isPinRequired) {
            return [trans('validation.device.device_pin.required')];
        }

        if ($deviceDTO->devicePin === null) {
            return [];
        }

        // Check against both regular and set fields
        return Device::where('device_id', '!=', $deviceDTO->deviceId)
            ->where('device_ds_id', $device->device_ds_id)
            ->where('device_module_id', $device->device_module_id)
            ->where(function($query) use ($deviceDTO) {
                $query->where('device_pin', $deviceDTO->devicePin)
                      ->orWhere('device_setpin', $deviceDTO->devicePin);
            })
            ->exists()
            ? [trans('validation.device.device_pin.already_exists')]
            : [];
    }


    // todo: this might be needing some extra look/test and consideration
//    private function validateSetFieldConflicts(DeviceDTO $deviceDTO, Device $device): array
//    {
//        // Only check if we're setting either identity or module
//        if ($deviceDTO->deviceIdentity === null && $deviceDTO->deviceModule === null) {
//            return [];
//        }
//
//        $conflictExists = Device::where('device_id', '!=', $deviceDTO->deviceId)
//            ->where(function($query) use ($deviceDTO, $device) {
//
//                $finalIdentity = $deviceDTO->deviceIdentity ?? $device->device_identity;
//                $finalModule = $deviceDTO->deviceModule ?? $device->device_module;
//                $query->where(function($q) use ($finalIdentity, $finalModule) {
//                    $q->where(function($sub) use ($finalIdentity) {
//                        $sub->where('device_identity', $finalIdentity)
//                            ->orWhere('device_setidentity', $finalIdentity);
//                    })->where(function($sub) use ($finalModule) {
//                        $sub->where('device_module', $finalModule)
//                            ->orWhere('device_setmodule', $finalModule);
//                    });
//                });
//            })
//            ->exists();
//
//        return $conflictExists
//            ? [trans('validation.device.setfield.potential_conflict')]
//            : [];
//    }
}