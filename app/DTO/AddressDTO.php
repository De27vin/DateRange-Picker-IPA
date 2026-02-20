<?php
namespace App\DTO;

class AddressDTO
{
    public function __construct(
        public readonly ?string $street = null,
        public readonly ?string $city = null,
        public readonly ?string $zip = null,
        public readonly ?int $countryId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? null,
            city: $data['city'] ?? null,
            zip: $data['zip'] ?? null,
            countryId: isset($data['countryId']) ? (int)$data['countryId'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'street' => $this->street,
            'city' => $this->city,
            'zip' => $this->zip,
            'country_id' => $this->countryId,
        ]);
    }

    public function isEmpty(): bool
    {
        return empty($this->toArray());
    }

    public function isComplete(): bool
    {
        return !empty($this->street) && !empty($this->city) && !empty($this->zip) && !empty($this->countryId);
    }

}
