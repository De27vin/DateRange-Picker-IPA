<?php

namespace App\DTO\Import;

class ImportRowDTO
{
    // Site fields
    public ?string $siteName = null;
    public ?string $siteModule = null; // protocol name
    public ?string $street = null;
    public ?string $zip = null;
    public ?string $city = null;
    public ?string $country = null;
    public ?string $pstnNumber = null;
    public ?string $simNumber = null;
    public ?string $pbxNumber = null;
    public ?string $sipNumber = null;
    public ?string $externalLink = null;

    // Device fields
    public ?string $equipmentId = null;
    public ?string $identity = null;
    public ?string $module = null; // module number
    public ?string $pin = null;
    public ?string $deviceType = null; // gateway/telealarm/intercom/monitor
    public ?string $deviceModule = null; // module name (e.g., "TA-2N-LIFT1")
    public ?string $macAddress = null;
    public ?string $imeiNumber = null;
    public ?string $firmwareVersion = null;
    public ?string $deviceStatus = null; // Enabled/Disabled

    // Custom fields
    public array $siteCustomFields = []; // ['field_name' => 'value']
    public array $deviceCustomFields = []; // ['field_name' => 'value']

    // Metadata
    public int $rowNumber;
    public array $rawData = [];

    public function __construct(int $rowNumber, array $data = [])
    {
        $this->rowNumber = $rowNumber;
        $this->rawData = $data;
    }

    /**
     * Get all phone numbers as array
     */
    public function getPhoneNumbers(): array
    {
        return array_filter([
            'PSTN' => $this->pstnNumber,
            'SIM' => $this->simNumber,
            'PBX' => $this->pbxNumber,
            'SIP' => $this->sipNumber,
        ]);
    }

    /**
     * Check if any phone number is present
     */
    public function hasAnyPhoneNumber(): bool
    {
        return !empty($this->getPhoneNumbers());
    }

    /**
     * Get address fields as array
     */
    public function getAddressFields(): array
    {
        return [
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }

    /**
     * Check if any address field is filled
     */
    public function hasAnyAddressField(): bool
    {
        return !empty(array_filter($this->getAddressFields()));
    }

    /**
     * Check if all address fields are filled
     */
    public function hasCompleteAddress(): bool
    {
        $fields = $this->getAddressFields();
        return !empty($fields['street'])
            && !empty($fields['zip'])
            && !empty($fields['city'])
            && !empty($fields['country']);
    }

    /**
     * Generate unique key for site identification
     */
    public function generateSiteKey(): string
    {
        $numbers = $this->getPhoneNumbers();
        if (empty($numbers)) {
            // Fallback to row number if no numbers
            return 'row_' . $this->rowNumber;
        }

        // Sort for consistent keys
        ksort($numbers);

        $parts = [];
        foreach ($numbers as $type => $number) {
            $parts[] = $type . ':' . $this->normalizePhoneNumber($number);
        }

        return implode('|', $parts);
    }

    /**
     * Normalize phone number (ensure + prefix)
     */
    private function normalizePhoneNumber(?string $number): ?string
    {
        if (empty($number)) {
            return null;
        }

        $number = preg_replace('/\s+/', '', $number);
        return preg_match('/^\+/', $number) ? $number : '+' . $number;
    }

    /**
     * Get normalized phone numbers
     */
    public function getNormalizedPhoneNumbers(): array
    {
        $numbers = [];
        foreach ($this->getPhoneNumbers() as $type => $number) {
            $normalized = $this->normalizePhoneNumber($number);
            if ($normalized) {
                $numbers[$type] = $normalized;
            }
        }
        return $numbers;
    }
}
