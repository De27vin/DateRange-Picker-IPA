<?php

namespace App\Validators;

use App\DTO\DeviceDTO;
use App\Models\Device;
use App\Models\DeviceGateway;

class DeviceGatewayValidator
{
    public function validateDTO(DeviceDTO $deviceDTO, Device $deviceModel): array
    {
        $errors = [];

        if (!$deviceModel->can_assign_gateway && !empty($gatewayId)) {
            return [trans('validation.device.gateway.not_supported')];
        }
        if (empty($gatewayId)) {
            return [];
        }

        try {
            $gateway = DeviceGateway::withTrashed()->findOrFail($gatewayId);
            if ($gateway->trashed()) {
                $errors[] = trans('validation.device.gateway.deleted');
            }
            if ($gateway->dg_device_id && $gateway->dg_device_id !== $deviceModel->device_id) {
                $errors[] = trans('validation.device.gateway.already_assigned');
            }
        } catch (\Throwable $e) {
            $errors[] = trans('validation.device.gateway.not_found');
        }

        return $errors;
    }
}