<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CustomFieldConfig;
use App\Models\Module;
use App\Services\RolesService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportDevicesController extends Controller
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

    public function __construct(private RolesService $rolesService) {}

    public function downloadTemplate(Request $request)
    {
        if ($error = $this->checkImportPermission()) {
            return response()->json($error, 403);
        }

        try {
            $format = $request->query('format', 'xlsx');
            $accountId = session('account.id');

            if (!$accountId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Account not found in session'),
                ], 401);
            }

            if (!in_array($format, ['csv', 'xlsx'])) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Invalid format. Allowed: csv, xlsx'),
                ], 400);
            }

            $file = $this->generateTemplate($accountId, $format);
            $headers = $format === 'csv'
                ? ['Content-Type' => 'text/csv']
                : ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

            return response()->download($file, 'import_template.' . $format, $headers)->deleteFileAfterSend();
        } catch (Exception $e) {
            Log::error('Template download failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function downloadInstructions(Request $request)
    {
        if ($error = $this->checkImportPermission()) {
            return response()->json($error, 403);
        }

        try {
            $locale = session('locale', 'en');
            $instructionsPath = resource_path("import/import_instructions_{$locale}.txt");
            if (!file_exists($instructionsPath)) {
                $instructionsPath = resource_path('import/import_instructions_en.txt');
            }

            if (!file_exists($instructionsPath)) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Instructions file not found'),
                ], 404);
            }

            $content = str_replace(
                '{MODULES_SECTION}',
                $this->generateModulesSection(),
                file_get_contents($instructionsPath)
            );

            $tempFile = tempnam(sys_get_temp_dir(), 'import_instructions_');
            file_put_contents($tempFile, $content);

            return response()->download($tempFile, 'import_instructions.txt', [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ])->deleteFileAfterSend();
        } catch (Exception $e) {
            Log::error('Instructions download failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    private function checkImportPermission(): ?array
    {
        $user = Auth::user();

        if (!$user) {
            return ['success' => false, 'message' => trans('Authentication required')];
        }

        if (!$this->rolesService->canUserImport($user)) {
            return [
                'success' => false,
                'message' => trans('You do not have permission to access import functionality'),
            ];
        }

        return null;
    }

    private function generateTemplate(int $accountId, string $format = 'xlsx'): string
    {
        $columns = $this->buildTemplateColumns(
            CustomFieldConfig::where('cfc_account_id', $accountId)->get()
        );
        $examples = $this->buildTemplateExamples($accountId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($columns as $index => $column) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $column['name']);
            $sheet->setCellValueByColumnAndRow($index + 1, 2, $column['required'] ? 'mandatory' : 'notmandatory');
        }

        foreach ($examples as $rowIndex => $example) {
            foreach ($columns as $colIndex => $column) {
                $name = $column['name'];
                $value = str_contains($name, '(sitecustomfield)')
                    || str_contains($name, '(devicecustomfield)')
                    ? ''
                    : ($example[$name] ?? '');

                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 3, $value);
            }
        }

        if ($format === 'xlsx') {
            $this->styleTemplate($sheet, count($columns), count($examples));
        }

        foreach (range(1, count($columns)) as $col) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'import_template_') . '.' . $format;
        if ($format === 'csv') {
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setUseBOM(true);
        } else {
            $writer = new Xlsx($spreadsheet);
        }

        $writer->save($tempFile);

        return $tempFile;
    }

    private function buildTemplateColumns($customFields): array
    {
        $columns = [
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

        foreach ($customFields as $field) {
            if (!$field->cfc_is_device) {
                $columns[] = ['name' => $field->cfc_name . ' (sitecustomfield)', 'required' => false];
            }
        }

        $columns = array_merge($columns, [
            ['name' => 'EquipmentID', 'required' => true],
            ['name' => 'Identity', 'required' => false],
            ['name' => 'Module', 'required' => false],
            ['name' => 'Pin', 'required' => false],
            ['name' => 'DeviceType', 'required' => true],
            ['name' => 'DeviceModule', 'required' => true],
            ['name' => 'MACAddress', 'required' => false],
            ['name' => 'IMEI', 'required' => false],
            ['name' => 'FirmwareVersion', 'required' => false],
            ['name' => 'DeviceStatus', 'required' => true],
        ]);

        foreach ($customFields as $field) {
            if ($field->cfc_is_device) {
                $columns[] = ['name' => $field->cfc_name . ' (devicecustomfield)', 'required' => false];
            }
        }

        return $columns;
    }

    private function buildTemplateExamples(int $accountId): array
    {
        $account = Account::with(['modules.module_type'])->find($accountId);
        $protocolIds = $account?->modules
            ? $account->modules
                ->filter(fn ($module) => $module->module_type && $module->module_type->mt_type === 'PROTOCOL')
                ->pluck('module_id')
                ->unique()
                ->values()
            : collect();

        $protocolQuery = Module::with(['module_type', 'supported_modules.module_type'])
            ->whereHas('module_type', fn ($query) => $query->where('mt_type', 'PROTOCOL'));

        if ($protocolIds->isNotEmpty()) {
            $protocolQuery->whereIn('module_id', $protocolIds);
        }

        $protocols = $protocolQuery->get();
        if ($protocols->isEmpty()) {
            return [];
        }

        $examples = [];
        $desiredRows = min(max($protocols->count(), self::SAMPLE_ROW_COUNT), self::MAX_SAMPLE_ROWS);

        for ($index = 0; $index < $desiredRows; $index++) {
            $protocol = $protocols[$index % $protocols->count()];
            $deviceModule = $this->pickCompatibleDeviceModule($protocol, $index);

            if ($deviceModule) {
                $examples[] = $this->makeExampleRow($protocol, $deviceModule, $index);
            }
        }

        return $examples;
    }

    private function pickCompatibleDeviceModule(Module $protocol, int $index): ?Module
    {
        $modules = $protocol->supported_modules
            ->filter(fn ($module) => in_array($module->module_type?->mt_type, self::SUPPORTED_DEVICE_TYPES))
            ->values();

        return $modules->isEmpty() ? null : $modules[$index % $modules->count()];
    }

    private function makeExampleRow(Module $protocol, Module $deviceModule, int $index): array
    {
        $address = $this->addressSamples[$index % count($this->addressSamples)];
        $deviceType = $this->mapDeviceType($deviceModule);
        $row = [
            'SiteName' => sprintf('Sample Site %02d', $index + 1),
            'SiteModule' => $this->formatModuleLabel($protocol),
            'Street' => $address['street'],
            'ZIP' => $address['zip'],
            'City' => $address['city'],
            'Country' => $address['country'],
            'PSTNNumber' => $this->formatE164Number(10000000 + $index),
            'SIMNumber' => $this->formatE164Number(20000000 + $index),
            'PBXNumber' => $this->formatE164Number(30000000 + $index),
            'SIPNumber' => $this->formatE164Number(40000000 + $index),
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
            $row['MACAddress'] = strtolower(str_pad(dechex(0xA1B2C3D40000 + $index), 12, '0', STR_PAD_LEFT));
            $base = str_pad((string) (35963219509500 + $index), 14, '0', STR_PAD_LEFT);
            $row['IMEI'] = $base . $this->calculateLuhnCheckDigit($base);
        }

        return $row;
    }

    private function formatModuleLabel(Module $module): string
    {
        return $module->module_desc ?: $module->module_name;
    }

    private function formatE164Number(int $localPart): string
    {
        return '+41' . str_pad((string) $localPart, 8, '0', STR_PAD_LEFT);
    }

    private function calculateLuhnCheckDigit(string $number): int
    {
        $sum = 0;

        for ($i = 0, $length = strlen($number); $i < $length; $i++) {
            $digit = (int) $number[$i];
            if (($i % 2) === 1) {
                $digit *= 2;
                $digit = $digit > 9 ? $digit - 9 : $digit;
            }
            $sum += $digit;
        }

        return $sum % 10 === 0 ? 0 : 10 - ($sum % 10);
    }

    private function mapDeviceType(Module $module): string
    {
        return match (strtolower($module->module_type?->mt_type ?? '')) {
            'gateway' => 'gateway',
            'intercom' => 'intercom',
            'monitor' => 'monitor',
            default => 'telealarm',
        };
    }

    private function styleTemplate($sheet, int $columnCount, int $exampleCount): void
    {
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A3:' . $lastColumn . (2 + $exampleCount))->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ]);

        $sheet->freezePane('A3');
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(18);
    }

    private function generateModulesSection(): string
    {
        $modulesByType = Module::with('module_type')
            ->get()
            ->filter(fn ($module) => !in_array($module->module_type->mt_type ?? '', ['EVENT', 'SYSTEM']))
            ->groupBy('module_type.mt_type')
            ->sortKeys()
            ->sortBy(fn ($modules, $type) => $type === 'PROTOCOL' ? '0' : '1' . $type);

        $sections = [];
        $sectionNumber = 7;

        foreach ($modulesByType as $moduleType => $modules) {
            $section = "{$sectionNumber}. Available " . ucfirst(strtolower($moduleType)) . " Modules:\n";
            foreach ($modules->pluck('module_desc')->sort()->values() as $index => $moduleName) {
                $section .= "  {$sectionNumber}." . ($index + 1) . " {$moduleName}\n";
            }
            $sections[] = $section;
            $sectionNumber++;
        }

        return implode("\n", $sections);
    }
}
