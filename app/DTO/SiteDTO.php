<?php
namespace App\DTO;

class SiteDTO
{
    public function __construct(
        public readonly int $dsId,
        public readonly ?string $dsName,
        public readonly ?string $dsLink,
        public readonly AddressDTO $addressDTO,
        public readonly PhoneNumbersDTO $phoneNumbersDTO,

        /** @var $devices DeviceDTO[] */
        public readonly array $devices,

        public readonly array $customFields, // array of CustomFieldDTO
        public readonly ?string $alarmNumber = null,
        public readonly ?string $periodicalNumber = null,

        public readonly array $labels = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            dsId: $data['ds_id'],
            dsName: $data['cloned']['ds_name'] ?? null,
            dsLink: $data['cloned']['ds_link'] ?? null,
            addressDTO: AddressDTO::fromArray($data['cloned']['address']),
            phoneNumbersDTO: PhoneNumbersDTO::fromArray($data['cloned']),
            devices: array_map(fn($device) => DeviceDTO::fromArray($device), $data['devices']),
            customFields: array_map(fn($field) => CustomFieldDTO::fromArray($field), $data['clonedCustomFields'] ?? []),
            alarmNumber: $data['cloned']['alarmNumber'] ?? null,
            periodicalNumber: $data['cloned']['periodicalNumber'] ?? null,
            labels: $data['cloned']['labels'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'ds_id' => $this->dsId,
            'ds_name' => $this->dsName,
            'ds_link' => $this->dsLink,
            'address' => $this->addressDTO->toArray(),
            'phone_numbers' => $this->phoneNumbersDTO->toArray(),
            'devices' => array_map(fn($device) => $device->toArray(), $this->devices),
            'custom_fields' => $this->customFields,
            'alarm_number' => $this->alarmNumber,
            'periodical_number' => $this->periodicalNumber,
            'labels' => $this->labels,
        ];
    }
}
