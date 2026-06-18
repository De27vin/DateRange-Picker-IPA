<?php

namespace App\DTO\Import;

class ImportValidationResult
{
    /** @var ImportError[] */
    private array $errors = [];

    /** @var ImportError[] */
    private array $warnings = [];

    private ?ImportSummary $summary = null;

    /**
     * Add error
     */
    public function addError(ImportError $error): void
    {
        if ($error->isError()) {
            $this->errors[] = $error;
        } else {
            $this->warnings[] = $error;
        }
    }

    /**
     * Add multiple errors
     */
    public function addErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * Check if validation passed (no errors)
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if has errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if has warnings
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get all warnings
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get errors grouped by row
     */
    public function getErrorsByRow(): array
    {
        $grouped = [];
        foreach ($this->errors as $error) {
            $grouped[$error->row][] = $error;
        }
        return $grouped;
    }

    /**
     * Get warnings grouped by row
     */
    public function getWarningsByRow(): array
    {
        $grouped = [];
        foreach ($this->warnings as $warning) {
            $grouped[$warning->row][] = $warning;
        }
        return $grouped;
    }

    /**
     * Set summary
     */
    public function setSummary(ImportSummary $summary): void
    {
        $this->summary = $summary;
    }

    /**
     * Get summary
     */
    public function getSummary(): ?ImportSummary
    {
        return $this->summary;
    }

    /**
     * Convert to array for API response
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'errors' => array_map(fn($e) => $e->toArray(), $this->errors),
            'warnings' => array_map(fn($w) => $w->toArray(), $this->warnings),
            'errorsByRow' => array_map(
                fn($errors) => array_map(fn($e) => $e->toArray(), $errors),
                $this->getErrorsByRow()
            ),
            'warningsByRow' => array_map(
                fn($warnings) => array_map(fn($w) => $w->toArray(), $warnings),
                $this->getWarningsByRow()
            ),
            'summary' => $this->summary?->toArray(),
        ];
    }
}
