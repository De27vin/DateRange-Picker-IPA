<?php
namespace App\DTO;

class PhoneNumbersDTO
{
    public function __construct(
        public readonly ?string $pstn = null,
        public readonly ?string $sim = null,
        public readonly ?string $sip = null,
        public readonly ?string $pbx = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            pstn: $data['pstn'] ?? null,
            sim: $data['sim'] ?? null,
            sip: $data['sip'] ?? null,
            pbx: $data['pbx'] ?? null
        );
    }

    public function isEmpty(): bool
    {
        return empty($this->toArray());
    }

    public function toArray(): array
    {
        return array_filter([
            'pstn' => $this->pstn,
            'sim' => $this->sim,
            'sip' => $this->sip,
            'pbx' => $this->pbx,
        ]);
    }
}
