<?php
namespace App\DTO;

class DeviceDTO
{
    public function __construct(
        public readonly int $deviceId,
        public readonly string $deviceEquipment,
        public readonly ?string $deviceIdentity,
        public readonly int $deviceModule,
        public readonly ?string $devicePin,
        public readonly ?string $deviceSetIdentity,
        public readonly ?int $deviceSetModule,
        public readonly ?string $deviceSetPin,
        public readonly array $customFields,
        public readonly ?GatewayDTO $gateway = null,
        public readonly ?string $alarmNumber = null,
        public readonly ?string $periodicalNumber = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deviceId: $data['device_id'],
            deviceEquipment: $data['cloned']['device_equipment'] ?? '',
            deviceIdentity: $data['cloned']['device_identity'] ?? null,
            deviceModule: $data['cloned']['device_module'] ?? null,
            devicePin: $data['cloned']['device_pin'] ?? null,
            deviceSetIdentity: $data['cloned']['device_setidentity'] ?? null,
            deviceSetModule: $data['cloned']['device_setmodule'] ?? null,
            deviceSetPin: $data['cloned']['device_setpin'] ?? null,
            customFields: array_map(
                fn($field) => CustomFieldDTO::fromArray($field),
                $data['clonedCustomFields'] ?? []
            ),
            gateway: !empty($data['clonedGatewayId']) ? GatewayDTO::fromArray($data) : null,
            alarmNumber: $data['cloned']['alarmNumber'] ?? null,
            periodicalNumber: $data['cloned']['periodicalNumber'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'device_id' => $this->deviceId,
            'device_equipment' => $this->deviceEquipment,
            'device_identity' => $this->deviceIdentity,
            'device_module' => $this->deviceModule,
            'device_pin' => $this->devicePin,
            'device_setidentity' => $this->deviceSetIdentity,
            'device_setmodule' => $this->deviceSetModule,
            'device_setpin' => $this->deviceSetPin,
            'custom_fields' => $this->customFields,
            'gateway' => $this->gateway?->toArray(),
            'alarm_number' => $this->alarmNumber,
            'periodical_number' => $this->periodicalNumber,
        ];
    }
}