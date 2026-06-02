<?php

namespace App\DTO\Import;

class ImportError
{
    public function __construct(
        public readonly int $row,
        public readonly ?string $column,
        public readonly ?string $value,
        public readonly string $message,
        public readonly string $severity = 'error' // 'error' | 'warning'
    ) {}

    public function toArray(): array
    {
        return [
            'row' => $this->row,
            'column' => $this->column,
            'value' => $this->value,
            'message' => $this->message,
            'severity' => $this->severity,
        ];
    }

    public function isError(): bool
    {
        return $this->severity === 'error';
    }

    public function isWarning(): bool
    {
        return $this->severity === 'warning';
    }
}
