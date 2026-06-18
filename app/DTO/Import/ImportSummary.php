<?php

namespace App\DTO\Import;

class ImportSummary
{
    public int $totalRows = 0;
    public int $validRows = 0;
    public int $invalidRows = 0;
    public int $newSites = 0;
    public int $existingSites = 0;
    public int $newDevices = 0;
    public int $totalErrors = 0;
    public int $totalWarnings = 0;

    /** @var array Site keys that will be created */
    public array $siteKeysToCreate = [];

    /** @var array Site keys that already exist */
    public array $existingSiteKeys = [];

    /** @var array Mapping of row numbers to site keys */
    public array $rowToSiteKeyMap = [];

    public function toArray(): array
    {
        return [
            'totalRows' => $this->totalRows,
            'validRows' => $this->validRows,
            'invalidRows' => $this->invalidRows,
            'newSites' => $this->newSites,
            'existingSites' => $this->existingSites,
            'newDevices' => $this->newDevices,
            'totalErrors' => $this->totalErrors,
            'totalWarnings' => $this->totalWarnings,
        ];
    }
}
