<?php

namespace App\Services\Import;

use App\Models\Account;
use App\Models\CustomFieldConfig;
use App\Models\Module;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ImportTemplateService
{
    private const SAMPLE_ROW_COUNT = 5;
    private const MAX_SAMPLE_ROWS = 10;
    private const SUPPORTED_DEVICE_TYPES = ['GATEWAY', 'TELEALARM', 'INTERCOM', 'MONITOR'];

    private array $addressSamples = [
        ['street' => 'Bahnhofstrasse 10', 'zip' => '8001', 'city' => 'Zurich', 'country' => 'CH'],
        ['street' => 'Freie Strasse 22', 'zip' => '4051', 'city' => 'Basel', 'country' => 'CH'],
        ['street' => 'Bundesplatz 3', 'zip' => '3011', 'city' => 'Bern', 'country' => 'CH'],
        ['street' => 'Rue du Rhone 12', 'zip' => '1204', 'city' => 'Geneva', 'country' => 'CH'],
        ['street' => 'Seestrasse 45', 'zip' => '8002', 'city' => 'Zurich', 'country' => 'CH'],
        ['street' => 'Pilatusstrasse 18', 'zip' => '6003', 'city' => 'Lucerne', 'country' => 'CH'],
    ];

    public function generateTemplate(int $accountId, string $format = 'xlsx'): string
    {
        // Get custom fields for this account
        $customFields = CustomFieldConfig::where('cfc_account_id', $accountId)->get();

        // Build columns (standard + custom fields)
        $columns = $this->buildColumns($customFields);

        // Build account-specific examples (fallback to static file only if needed)
        $examples = $this->buildExamples($accountId);
        if (empty($examples)) {
            $examples = $this->loadExamples(storage_path('app/import_examples.csv'));
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers
        $col = 1;
        foreach ($columns as $column) {
            $sheet->setCellValueByColumnAndRow($col, 1, $column['name']);
            $col++;
        }

        // Set requirement row
        $col = 1;
        foreach ($columns as $column) {
            $sheet->setCellValueByColumnAndRow($col, 2, $column['required'] ? 'mandatory' : 'notmandatory');
            $col++;
        }

        // Add example rows
        $this->addExampleRows($sheet, $columns, $examples);

        // Style the template (only for XLSX)
        if ($format === 'xlsx') {
            $this->styleTemplate($sheet, count($columns), count($examples));
        }

        // Auto-size columns
        foreach (range(1, count($columns)) as $col) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'import_template_');
        $tempFile .= '.' . $format;

        if ($format === 'csv') {
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setUseBOM(true); // Excel needs BOM for UTF-8 detection
        } else {
            $writer = new Xlsx($spreadsheet);
        }

        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * Build account-specific example rows
     */
    private function buildExamples(int $accountId): array
    {
        $account = Account::with(['modules.module_type'])->find($accountId);

        $protocolIds = collect();
        if ($account && $account->modules) {
            $protocolIds = $account->modules
                ->filter(fn($module) => $module->module_type && $module->module_type->mt_type === 'PROTOCOL')
                ->pluck('module_id')
                ->unique()
                ->values();
        }

        $protocolQuery = Module::with(['module_type', 'supported_modules.module_type'])
            ->whereHas('module_type', fn($query) => $query->where('mt_type', 'PROTOCOL'));

        if ($protocolIds->isNotEmpty()) {
            $protocolQuery->whereIn('module_id', $protocolIds);
        }

        $protocols = $protocolQuery->get();

        if ($protocols->isEmpty()) {
            return [];
        }

        $examples = [];
        $protocolCount = $protocols->count();
        $desiredRows = min(max($protocolCount, self::SAMPLE_ROW_COUNT), self::MAX_SAMPLE_ROWS);

        for ($index = 0; $index < $desiredRows; $index++) {
            $protocol = $protocols[$index % $protocolCount];
            $deviceModule = $this->pickCompatibleDeviceModule($protocol, $index);

            if (!$deviceModule) {
                continue;
            }

            $examples[] = $this->makeExampleRow($protocol, $deviceModule, $index);
        }

        return $examples;
    }

    private function pickCompatibleDeviceModule(Module $protocol, int $index): ?Module
    {
        $modules = $protocol->supported_modules
            ->filter(function ($module) {
                $type = $module->module_type?->mt_type;
                return $type && in_array($type, self::SUPPORTED_DEVICE_TYPES);
            })
            ->values();

        if ($modules->isEmpty()) {
            return null;
        }

        return $modules[$index % $modules->count()];
    }

    private function makeExampleRow(Module $protocol, Module $deviceModule, int $index): array
    {
        $address = $this->addressSamples[$index % count($this->addressSamples)];
        $numbers = $this->generatePhoneNumbers($index);
        $deviceType = $this->mapDeviceType($deviceModule);

        $row = [
            'SiteName' => sprintf('Sample Site %02d', $index + 1),
            'SiteModule' => $this->formatModuleLabel($protocol),
            'Street' => $address['street'],
            'ZIP' => $address['zip'],
            'City' => $address['city'],
            'Country' => $address['country'],
            'PSTNNumber' => $numbers['pstn'],
            'SIMNumber' => $numbers['sim'],
            'PBXNumber' => $numbers['pbx'],
            'SIPNumber' => $numbers['sip'],
            'ExternalLink' => sprintf('https://example.com/import-site-%d', $index + 1),
            'EquipmentID' => sprintf('EQ-%05d', $index + 1),
            'Identity' => (string) (100 + $index),
            'Module' => (string) (($index % 4) + 1),
            'Pin' => str_pad((string) (1000 + $index), 4, '0', STR_PAD_LEFT),
            'DeviceType' => $deviceType,
            'DeviceModule' => $this->formatModuleLabel($deviceModule),
            'MACAddress' => '',
            'IMEI' => '',
            'FirmwareVersion' => '1.' . ($index + 1),
            'DeviceStatus' => 'Enabled',
        ];

        if ($deviceModule->module_type && $deviceModule->module_type->mt_type === 'GATEWAY') {
            $row['MACAddress'] = $this->generateMacAddress($index);
            $row['IMEI'] = $this->generateImei($index);
        }

        return $row;
    }

    private function formatModuleLabel(Module $module): string
    {
        return $module->module_desc ?: $module->module_name;
    }

    private function generatePhoneNumbers(int $index): array
    {
        return [
            'pstn' => $this->formatE164Number(10000000 + $index),
            'sim'  => $this->formatE164Number(20000000 + $index),
            'pbx'  => $this->formatE164Number(30000000 + $index),
            'sip'  => $this->formatE164Number(40000000 + $index),
        ];
    }

    private function formatE164Number(int $localPart): string
    {
        return '+41' . str_pad((string) $localPart, 8, '0', STR_PAD_LEFT);
    }

    private function generateMacAddress(int $index): string
    {
        $value = 0xA1B2C3D40000 + $index;
        return strtolower(str_pad(dechex($value), 12, '0', STR_PAD_LEFT));
    }

    private function generateImei(int $index): string
    {
        $base = str_pad((string) (35963219509500 + $index), 14, '0', STR_PAD_LEFT);
        $check = $this->calculateLuhnCheckDigit($base);
        return $base . $check;
    }

    private function calculateLuhnCheckDigit(string $number): int
    {
        $sum = 0;
        $length = strlen($number);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$i];
            if (($i % 2) === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        $mod = $sum % 10;
        return ($mod === 0) ? 0 : 10 - $mod;
    }

    private function mapDeviceType(Module $module): string
    {
        $type = strtolower($module->module_type?->mt_type ?? '');
        return match ($type) {
            'gateway' => 'gateway',
            'intercom' => 'intercom',
            'monitor' => 'monitor',
            default => 'telealarm',
        };
    }

    /**
     * Load fallback examples from CSV file
     */
    private function loadExamples(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $examples = [];
        $handle = fopen($path, 'r');

        // Read header
        $header = fgetcsv($handle);

        // Read all example rows
        while (($row = fgetcsv($handle)) !== false) {
            $example = [];
            foreach ($header as $index => $columnName) {
                $example[$columnName] = $row[$index] ?? '';
            }
            $examples[] = $example;
        }

        fclose($handle);

        return $examples;
    }

    private function buildColumns($customFields): array
    {
        $columns = [
            // Site fields
            ['name' => 'SiteName', 'required' => false],
            ['name' => 'SiteModule', 'required' => true],
            ['name' => 'Street', 'required' => false],
            ['name' => 'ZIP', 'required' => false],
            ['name' => 'City', 'required' => false],
            ['name' => 'Country', 'required' => false],
            ['name' => 'PSTNNumber', 'required' => false],
            ['name' => 'SIMNumber', 'required' => false],
            ['name' => 'PBXNumber', 'required' => false],
            ['name' => 'SIPNumber', 'required' => false],
            ['name' => 'ExternalLink', 'required' => false],
        ];

        // Add site custom fields
        foreach ($customFields as $field) {
            if (!$field->cfc_is_device) {
                $columns[] = [
                    'name' => $field->cfc_name . ' (sitecustomfield)',
                    'required' => false
                ];
            }
        }

        // Device fields
        $columns = array_merge($columns, [
            ['name' => 'EquipmentID', 'required' => true],
            ['name' => 'Identity', 'required' => false], // Depends on module
            ['name' => 'Module', 'required' => false], // Module number - depends on module
            ['name' => 'Pin', 'required' => false], // Depends on module
            ['name' => 'DeviceType', 'required' => true],
            ['name' => 'DeviceModule', 'required' => true],
            ['name' => 'MACAddress', 'required' => false],
            ['name' => 'IMEI', 'required' => false],
            ['name' => 'FirmwareVersion', 'required' => false],
            ['name' => 'DeviceStatus', 'required' => true],
        ]);

        // Add device custom fields
        foreach ($customFields as $field) {
            if ($field->cfc_is_device) {
                $columns[] = [
                    'name' => $field->cfc_name . ' (devicecustomfield)',
                    'required' => false
                ];
            }
        }

        return $columns;
    }

    private function addExampleRows($sheet, array $columns, array $examples): void
    {
        // Write example rows
        for ($i = 0; $i < count($examples); $i++) {
            $rowNum = 3 + $i; // Start after header and requirement rows
            $example = $examples[$i];

            $col = 1;
            foreach ($columns as $column) {
                $columnName = $column['name'];

                // For custom fields, leave empty (they're account-specific)
                if (str_contains($columnName, '(sitecustomfield)') || str_contains($columnName, '(devicecustomfield)')) {
                    $value = '';
                } else {
                    // Get value from example data
                    $value = $example[$columnName] ?? '';
                }

                $sheet->setCellValueByColumnAndRow($col, $rowNum, $value);
                $col++;
            }
        }
    }

    /**
     * Style template (for XLSX)
     */
    private function styleTemplate($sheet, int $columnCount, int $exampleCount): void
    {
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);

        // Style header row
        $headerRange = 'A1:' . $lastColumn . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style requirement row
        $reqRange = 'A2:' . $lastColumn . '2';
        $sheet->getStyle($reqRange)->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 9,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style example rows
        $lastExampleRow = 2 + $exampleCount;
        $exampleRange = 'A3:' . $lastColumn . $lastExampleRow;
        $sheet->getStyle($exampleRange)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
        ]);

        // Freeze first two rows
        $sheet->freezePane('A3');

        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(18);
    }
}
