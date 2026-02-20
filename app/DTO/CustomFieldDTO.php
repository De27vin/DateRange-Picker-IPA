<?php
namespace App\DTO;

class CustomFieldDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name,
        public readonly mixed $value = null,
        public readonly ?string $type = null,
        public readonly ?bool $isRequired = false,
        public readonly ?array $options = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            value: $data['value'] ?? null,
            type: $data['type'] ?? null,
            isRequired: $data['is_required'] ?? false,
            options: $data['options'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'type' => $this->type,
            'is_required' => $this->isRequired,
            'options' => $this->options,
        ], fn($value) => !is_null($value));
    }

    public function isEmpty(): bool
    {
        return is_null($this->value) && is_null($this->options);
    }
}