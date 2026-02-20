<?php
namespace App\DTO;

class GatewayDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $mac = null,
        public readonly ?string $imei = null,
        public readonly ?int $deviceId = null  // for tracking assignment
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['clonedGatewayId'] ?? null,
            mac: $data['mac'] ?? null,
            imei: $data['imei'] ?? null,
            deviceId: $data['device_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'dg_id' => $this->id,
            'dg_mac' => $this->mac,
            'dg_imei' => $this->imei,
            'dg_device_id' => $this->deviceId
        ];
    }
}
