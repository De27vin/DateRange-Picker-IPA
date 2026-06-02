<?php
namespace App\Services;

use App\Models\Device;
use App\Models\DeviceGateway;
use App\Traits\PasswordPolicyTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class DeviceGatewayPersistenceService
{
    use PasswordPolicyTrait;
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

    /**
     * Find or restore a gateway for the given account.
     */
    public function findOrRestoreGateway(
        ?string $mac,
        ?string $imei,
        int $accountId,
        ?int $deviceId = null
    ): DeviceGateway {
        if (!$mac && !$imei) {
            throw new Exception('At least one of MAC or IMEI must be provided');
        }

        $gatewayByMac = null;
        $gatewayByImei = null;

        if ($mac) {
            $gatewayByMac = DeviceGateway::withoutGlobalScopes()
                ->withTrashed()
                ->where('dg_mac', $mac)
                ->first();
        }

        if ($imei) {
            $gatewayByImei = DeviceGateway::withoutGlobalScopes()
                ->withTrashed()
                ->where('dg_imei', $imei)
                ->first();
        }

        $existingGateway = null;

        if ($gatewayByMac && $gatewayByImei && $gatewayByMac->dg_id === $gatewayByImei->dg_id) {
            $existingGateway = $gatewayByMac;
        } elseif ($gatewayByMac && $gatewayByImei && $gatewayByMac->dg_id !== $gatewayByImei->dg_id) {
            Log::error('Gateway conflict: MAC and IMEI belong to different gateways', [
                'mac' => $mac,
                'imei' => $imei,
                'gateway_by_mac_id' => $gatewayByMac->dg_id,
                'gateway_by_imei_id' => $gatewayByImei->dg_id,
                'account_id' => $accountId,
            ]);
            throw new Exception(
                "Cannot import: MAC address belongs to gateway #{$gatewayByMac->dg_id}, " .
                "but IMEI belongs to different gateway #{$gatewayByImei->dg_id}. " .
                "Manual resolution required before import."
            );
        } elseif ($gatewayByMac) {
            $existingGateway = $gatewayByMac;
        } elseif ($gatewayByImei) {
            $existingGateway = $gatewayByImei;
        }

        if ($existingGateway) {
            if ($existingGateway->dg_account_id != $accountId) {
                Log::warning('Attempt to use gateway from different account', [
                    'gateway_id' => $existingGateway->dg_id,
                    'gateway_account' => $existingGateway->dg_account_id,
                    'requested_account' => $accountId,
                    'mac' => $mac,
                    'imei' => $imei,
                ]);
                throw new Exception('Gateway belongs to a different account');
            }

            if (!$existingGateway->trashed() && $existingGateway->dg_device_id) {
                Log::error('Attempt to use gateway that is already assigned', [
                    'gateway_id' => $existingGateway->dg_id,
                    'assigned_to_device' => $existingGateway->dg_device_id,
                    'account_id' => $accountId,
                    'mac' => $mac,
                    'imei' => $imei,
                ]);
                throw new Exception('Gateway is already assigned to another device');
            }

            $wasTrashed = $existingGateway->trashed();
            $wasUnassigned = !$existingGateway->dg_device_id;

            if ($wasTrashed) {
                $existingGateway->restore();
            }

            if ($mac !== null) {
                $existingGateway->dg_mac = $mac;
            }
            if ($imei !== null) {
                $existingGateway->dg_imei = $imei;
            }
            $existingGateway->dg_device_id = $deviceId;
            $existingGateway->dg_modified = Carbon::now();

            $realm = 'serv24.com';
            $password = $this->generatePassword($accountId);
            $hashPart = $mac ?? $imei ?? '';
            $existingGateway->dg_siphash = md5($hashPart . ':' . $realm . ':' . $password);
            $existingGateway->dg_sippwd = encrypt($password, false);

            $existingGateway->save();

            if ($wasTrashed) {
                Log::info('Gateway restored from soft delete and assigned', [
                    'gateway_id' => $existingGateway->dg_id,
                    'account_id' => $accountId,
                    'device_id' => $deviceId,
                    'mac' => $mac,
                    'imei' => $imei,
                ]);
            } elseif ($wasUnassigned) {
                Log::info('Unassigned gateway reused and assigned', [
                    'gateway_id' => $existingGateway->dg_id,
                    'account_id' => $accountId,
                    'device_id' => $deviceId,
                    'mac' => $mac,
                    'imei' => $imei,
                ]);
            } else {
                Log::info('Gateway updated', [
                    'gateway_id' => $existingGateway->dg_id,
                    'account_id' => $accountId,
                    'device_id' => $deviceId,
                    'mac' => $mac,
                    'imei' => $imei,
                ]);
            }

            return $existingGateway;
        }

        // Gateway doesn't exist - create new one
        $realm = 'serv24.com';
        $password = $this->generatePassword($accountId);
        $hashPart = $mac ?? $imei ?? '';
        $passwordHash = md5($hashPart . ':' . $realm . ':' . $password);
        $encryptedPassword = encrypt($password, false);

        $gateway = DeviceGateway::create([
            'dg_account_id' => $accountId,
            'dg_device_id' => $deviceId,
            'dg_mac' => $mac,
            'dg_imei' => $imei,
            'dg_siphash' => $passwordHash,
            'dg_sippwd' => $encryptedPassword,
        ]);

        Log::info('New gateway created', [
            'gateway_id' => $gateway->dg_id,
            'account_id' => $accountId,
            'mac' => $mac,
            'imei' => $imei,
        ]);

        return $gateway;
    }
}