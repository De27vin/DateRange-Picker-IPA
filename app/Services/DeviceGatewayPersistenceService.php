<?php
namespace App\Services;

use App\Models\Device;
use App\Models\DeviceGateway;
use Exception;

class DeviceGatewayPersistenceService
{
    public function updateGatewayAssignments(array $devices): void
    {
        foreach ($devices as $deviceDTO) {
            $device = Device::findOrFail($deviceDTO->deviceId);
            if ($device->can_assign_gateway) {
                $this->updateGatewayAssignment($device, $deviceDTO->gatewayId);
            }
        }
    }

    public function updateGatewayAssignment(Device $device, ?int $gatewayId): void
    {
        // OLD METHOD:
        if ($gatewayId != $device->gateway?->dg_id) {
            if (empty($gatewayId)) {
                //detach gateway
                $gateway = $device->gateway;
                $gateway->dg_device_id = null;
                $gateway->save();
            } elseif (empty($device->gateway?->dg_id)) {
                // attach gateway
                $gateway = DeviceGateway::findOrFail($gatewayId);
                if ($gateway->dg_device_id) { throw new \Exception('Attempt to assign already assigned gateway'); }
                $gateway->dg_device_id = $device->device_id;
                $gateway->save();
            } else {
                // change assignment
                $newGateway = DeviceGateway::findOrFail($gatewayId);
                if ($newGateway->dg_device_id) { throw new \Exception('Attempt to assign already assigned gateway'); }

                $oldGateway = $device->gateway;
                $oldGateway->dg_device_id = null;
                $oldGateway->save();

                $newGateway->dg_device_id = $device->device_id;
                $newGateway->save();
            }
        }

        // NEW METHOD 1:
//        DeviceGateway::where('dg_device_id', $device->device_id)->update(['dg_device_id' => null]);
//
//        // If a new gateway is specified, attach it
//        if ($gatewayId) {
//            $gateway = DeviceGateway::findOrFail($gatewayId);
//            if ($gateway->dg_device_id) { throw new Exception('Attempt to assign already assigned gateway'); }
//            $gateway->dg_device_id = $device->device_id;
//            $gateway->save();
//        }

        // NEW METHOD 2:
//        if (!$device->can_assign_gateway) {
//            return;
//        }
//
//        // First detach current gateway if exists
//        if ($currentGateway = $device->gateway) {
//            $currentGateway->dg_device_id = null;
//            $currentGateway->save();
//        }
//
//        // Then attach new gateway if provided
//        if ($gatewayId) {
//            $gateway = DeviceGateway::withTrashed()->findOrFail($gatewayId);
//
//            if ($gateway->trashed()) {
//                throw new Exception('Cannot assign deleted gateway');
//            }
//
//            if ($gateway->dg_device_id && $gateway->dg_device_id !== $device->device_id) {
//                throw new Exception('Gateway is already assigned to another device');
//            }
//
//            $gateway->dg_device_id = $device->device_id;
//            $gateway->save();
//        }

    }
}