<?php

namespace App\Services\Import;

use App\DTO\Import\ImportError;
use App\DTO\Import\ImportRowDTO;
use App\DTO\Import\ImportSummary;
use App\DTO\Import\ImportValidationResult;
use App\Enum\ModuleFlags;
use App\Models\Country;
use App\Models\CustomFieldConfig;
use App\Models\Device;
use App\Models\DeviceGateway;
use App\Models\DeviceSite;
use App\Models\Location;
use App\Models\Module;
use App\Models\ModuleType;
use App\Models\Number;
use App\Services\ProfileAccessService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportValidationService
{

    private const MAX_ROWS = 500;

    // Required columns (normalized keys)
    private const REQUIRED_COLUMNS = [
        'sitemodule',    // Site protocol
        'equipmentid',   // Equipment ID
        'devicetype',    // Device type
        'devicemodule',  // Device module name
        'devicestatus',  // Device status
    ];

    // Column mapping from normalized Excel/CSV column names to DTO properties
    // Keys are normalized (lowercase, no spaces/special chars)
    private const COLUMN_MAPPING = [
        'sitename' => 'siteName',
        'sitemodule' => 'siteModule',
        'street' => 'street',
        'zip' => 'zip',
        'city' => 'city',
        'country' => 'country',
        'pstnnumber' => 'pstnNumber',
        'simnumber' => 'simNumber',
        'pbxnumber' => 'pbxNumber',
        'sipnumber' => 'sipNumber',
        'externallink' => 'externalLink',
        'equipmentid' => 'equipmentId',
        'identity' => 'identity',
        'module' => 'module',
        'pin' => 'pin',
        'devicetype' => 'deviceType',
        'devicemodule' => 'deviceModule',
        'macaddress' => 'macAddress',
        'imei' => 'imeiNumber',
        'firmwareversion' => 'firmwareVersion',
        'devicestatus' => 'deviceStatus',
    ];

    private ProfileAccessService $profileAccess;
    private int $accountId;

    // Cache for database lookups
    private Collection $modules;
    private Collection $moduleTypes;
    private Collection $countriesCache;
    private Collection $customFieldConfigs;
    private Collection $existingNumbers;
    private Collection $existingDevices;
    private Collection $existingGateways;
    private Collection $accountModules;
    private bool $enforceModuleAccess = true;
    private array $moduleCompatibilityMatrix = [];

    public function __construct(ProfileAccessService $profileAccess)
    {
        $this->profileAccess = $profileAccess;
    }

    /**
     * Main validation method
     */
    public function validate(array $rows, int $accountId): ImportValidationResult
    {
        $startTime = microtime(true);
        Log::info('[IMPORT] Starting validation', ['rows' => count($rows), 'accountId' => $accountId]);

        $this->accountId = $accountId;
        $result = new ImportValidationResult();

        // Step 1: Validate structure
        $stepStart = microtime(true);
        $structureErrors = $this->validateStructure($rows);
        Log::info('[IMPORT] Step 1: Structure validation', ['time' => round(microtime(true) - $stepStart, 2) . 's', 'errors' => count($structureErrors)]);

        if (!empty($structureErrors)) {
            $result->addErrors($structureErrors);
            return $result;
        }

        // Step 2: Preload all necessary data from database
        $stepStart = microtime(true);
        $this->preloadDatabaseData($rows);
        Log::info('[IMPORT] Step 2: Preload database data', ['time' => round(microtime(true) - $stepStart, 2) . 's']);

        // Step 3: Parse rows to DTOs
        $stepStart = microtime(true);
        $rowDTOs = $this->parseRowsToDTO($rows);
        Log::info('[IMPORT] Step 3: Parse rows to DTOs', ['time' => round(microtime(true) - $stepStart, 2) . 's', 'dtos' => count($rowDTOs)]);

        // Step 4: Validate each row
        $stepStart = microtime(true);
        foreach ($rowDTOs as $rowDTO) {
            $rowErrors = $this->validateRow($rowDTO, $rowDTOs);
            $result->addErrors($rowErrors);
        }
        Log::info('[IMPORT] Step 4: Validate each row', ['time' => round(microtime(true) - $stepStart, 2) . 's', 'rows' => count($rowDTOs)]);

        // Step 5: Cross-row validation
        $stepStart = microtime(true);
        $crossRowErrors = $this->validateCrossRows($rowDTOs);
        $result->addErrors($crossRowErrors);
        Log::info('[IMPORT] Step 5: Cross-row validation', ['time' => round(microtime(true) - $stepStart, 2) . 's', 'errors' => count($crossRowErrors)]);

        // Step 6: Generate summary
        $stepStart = microtime(true);
        $summary = $this->generateSummary($rowDTOs, $result);
        $result->setSummary($summary);
        Log::info('[IMPORT] Step 6: Generate summary', ['time' => round(microtime(true) - $stepStart, 2) . 's']);

        $totalTime = round(microtime(true) - $startTime, 2);
        Log::info('[IMPORT] Validation completed', ['totalTime' => $totalTime . 's', 'totalErrors' => count($result->getErrors())]);

        return $result;
    }

    /**
     * Normalize column name for case-insensitive matching
     * Converts to lowercase and removes spaces, parentheses, and special chars
     */
    private function normalizeColumnName(string $columnName): string
    {
        // Remove BOM if present and trim quotes/whitespace
        $columnName = preg_replace('/^\xEF\xBB\xBF/', '', $columnName);
        $columnName = trim($columnName, " \t\n\r\0\x0B\"'");

        // Convert to lowercase
        $normalized = strtolower($columnName);

        // Remove quotes/apostrophes that might remain from CSV exports
        $normalized = str_replace(['"', "'"], '', $normalized);

        // Remove content in parentheses (e.g., "Module (number)" -> "Module")
        $normalized = preg_replace('/\s*\([^)]*\)/', '', $normalized);

        // Remove spaces, dashes, underscores
        $normalized = preg_replace('/[\s\-_]+/', '', $normalized);

        return $normalized;
    }

    /**
     * Validate file structure
     */
    private function validateStructure(array $rows): array
    {
        $errors = [];

        // Check if empty
        if (empty($rows)) {
            $errors[] = new ImportError(
                row: 0,
                column: null,
                value: null,
                message: trans('Import file is empty')
            );
            return $errors;
        }

        // Check row limit
        $dataRows = count($rows) - 1; // Exclude header row
        if ($dataRows > self::MAX_ROWS) {
            $errors[] = new ImportError(
                row: 0,
                column: null,
                value: (string)$dataRows,
                message: trans('Import file exceeds maximum allowed rows (:max). Found :count rows.', [
                    'max' => self::MAX_ROWS,
                    'count' => $dataRows
                ])
            );
        }

        // Check required columns (case-insensitive)
        $headers = array_keys($rows[0]);
        $normalizedHeaders = array_map(fn($h) => $this->normalizeColumnName($h), $headers);
        $missingColumns = array_diff(self::REQUIRED_COLUMNS, $normalizedHeaders);

        // Check for unknown columns (must match template headers)
        $unknownColumns = [];
        foreach ($headers as $index => $header) {
            $normalized = $normalizedHeaders[$index];
            $isCustomField = stripos($header, 'sitecustomfield') !== false
                || stripos($header, 'devicecustomfield') !== false;

            if (!isset(self::COLUMN_MAPPING[$normalized]) && !$isCustomField) {
                $unknownColumns[] = $header;
            }
        }

        if (!empty($unknownColumns)) {
            $errors[] = new ImportError(
                row: 0,
                column: null,
                value: implode(', ', $unknownColumns),
                message: trans('Unknown columns detected: :columns. Please use the provided template headers.', [
                    'columns' => implode(', ', $unknownColumns)
                ])
            );
        }

        if (!empty($missingColumns)) {
            // Map back to readable names for error message
            $readableNames = [
                'sitemodule' => 'SiteModule',
                'equipmentid' => 'EquipmentID',
                'devicetype' => 'DeviceType',
                'devicemodule' => 'DeviceModule',
                'devicestatus' => 'DeviceStatus',
            ];
            $missingReadable = array_map(fn($col) => $readableNames[$col] ?? $col, $missingColumns);

            $errors[] = new ImportError(
                row: 0,
                column: null,
                value: implode(', ', $missingReadable),
                message: trans('Missing required columns: :columns', [
                    'columns' => implode(', ', $missingReadable)
                ])
            );
        }

        return $errors;
    }

    /**
     * Preload all database data for validation
     */
    private function preloadDatabaseData(array $rows): void
    {
        // Load modules
        $queryStart = microtime(true);
        $this->modules = Module::with('module_type', 'supported_modules')->get();
        Log::info('[IMPORT] Loaded modules', ['count' => $this->modules->count(), 'time' => round(microtime(true) - $queryStart, 2) . 's']);

        // Load module types
        $queryStart = microtime(true);
        $this->moduleTypes = ModuleType::all();
        Log::info('[IMPORT] Loaded module types', ['count' => $this->moduleTypes->count(), 'time' => round(microtime(true) - $queryStart, 2) . 's']);

        // Load countries
        $queryStart = microtime(true);
        $this->countriesCache = Country::all();
        Log::info('[IMPORT] Loaded countries', ['count' => $this->countriesCache->count(), 'time' => round(microtime(true) - $queryStart, 2) . 's']);

        // Load custom field configs for this account
        $queryStart = microtime(true);
        $this->customFieldConfigs = CustomFieldConfig::where('cfc_account_id', $this->accountId)->get();
        Log::info('[IMPORT] Loaded custom fields', ['count' => $this->customFieldConfigs->count(), 'time' => round(microtime(true) - $queryStart, 2) . 's']);

        // Load existing numbers
        $queryStart = microtime(true);
        $this->existingNumbers = Number::whereNotNull('number_ds_id')->get();
        Log::info('[IMPORT] Loaded existing numbers', ['count' => $this->existingNumbers->count(), 'time' => round(microtime(true) - $queryStart, 2) . 's']);

        // Load existing devices (equipment IDs)
        $queryStart = microtime(true);
        $this->existingDevices = Device::where('device_account_id', $this->accountId)
            ->select('device_id', 'device_equipment', 'device_identity', 'device_module',
                     'device_pin', 'device_ds_id', 'device_module_id', 'device_setidentity', 'device_setpin')
            ->get();
        Log::info('[IMPORT] Loaded existing devices', ['count' => $this->existingDevices->count(), 'time' => round(microtime(true) - $queryStart, 2) . 's']);

        // Load existing gateways (MAC/IMEI)
        $queryStart = microtime(true);
        $this->existingGateways = DeviceGateway::withoutGlobalScopes()
            ->select('dg_id', 'dg_mac', 'dg_imei', 'dg_account_id', 'dg_deleted')
            ->get();
        Log::info('[IMPORT] Loaded existing gateways', ['count' => $this->existingGateways->count(), 'time' => round(microtime(true) - $queryStart, 2) . 's']);

        // Load account modules to determine access
        $queryStart = microtime(true);
        $this->accountModules = DB::table('accounts_modules')
            ->where('am_account_id', $this->accountId)
            ->pluck('am_module_id');
        $this->enforceModuleAccess = $this->accountModules->isNotEmpty();
        Log::info('[IMPORT] Loaded account modules', [
            'count' => $this->accountModules->count(),
            'enforce' => $this->enforceModuleAccess,
            'time' => round(microtime(true) - $queryStart, 2) . 's'
        ]);

        // Build module compatibility matrix
        $queryStart = microtime(true);
        $this->buildModuleCompatibilityMatrix();
        Log::info('[IMPORT] Built compatibility matrix', ['time' => round(microtime(true) - $queryStart, 2) . 's']);
    }

    /**
     * Build module compatibility matrix from modules_matrix table
     */
    private function buildModuleCompatibilityMatrix(): void
    {
        $matrix = DB::table('modules_matrix')
            ->join('modules as protocol', 'modules_matrix.mm_protocol_id', '=', 'protocol.module_id')
            ->join('modules as device_module', 'modules_matrix.mm_module_id', '=', 'device_module.module_id')
            ->select('protocol.module_name as protocol_name', 'device_module.module_name as device_module_name')
            ->get();

        foreach ($matrix as $entry) {
            if (!isset($this->moduleCompatibilityMatrix[$entry->protocol_name])) {
                $this->moduleCompatibilityMatrix[$entry->protocol_name] = [];
            }
            $this->moduleCompatibilityMatrix[$entry->protocol_name][] = $entry->device_module_name;
        }
    }

    /**
     * Parse rows to DTO objects
     */
    private function parseRowsToDTO(array $rows): array
    {
        $dtos = [];

        // Skip header row (index 0) and optional requirement row (index 1 if it contains 'mandatory')
        $startIndex = 1;
        if (isset($rows[1]) && $this->isRequirementRow($rows[1])) {
            $startIndex = 2;
        }

        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNumber = $i + 1; // Excel row number (1-indexed)

            $dto = new ImportRowDTO($rowNumber, $row);

            // Map standard columns (case-insensitive)
            foreach ($row as $originalColumn => $value) {
                $normalizedColumn = $this->normalizeColumnName($originalColumn);

                // Check if this is a standard column
                if (isset(self::COLUMN_MAPPING[$normalizedColumn])) {
                    $property = self::COLUMN_MAPPING[$normalizedColumn];
                    $trimmedValue = trim($value);
                    $dto->$property = $trimmedValue === '' ? null : $trimmedValue;
                }
                // Check for custom fields (case-insensitive check for markers)
                elseif (stripos($originalColumn, 'sitecustomfield') !== false) {
                    // Extract field name (everything before the marker, trimmed)
                    $fieldName = preg_replace('/\s*\(?\s*sitecustomfield\s*\)?\s*/i', '', $originalColumn);
                    $dto->siteCustomFields[trim($fieldName)] = trim($value);
                }
                elseif (stripos($originalColumn, 'devicecustomfield') !== false) {
                    // Extract field name (everything before the marker, trimmed)
                    $fieldName = preg_replace('/\s*\(?\s*devicecustomfield\s*\)?\s*/i', '', $originalColumn);
                    $dto->deviceCustomFields[trim($fieldName)] = trim($value);
                }
            }

            $dtos[] = $dto;
        }

        return $dtos;
    }

    /**
     * Check if row is requirement row (contains 'mandatory' or 'notmandatory')
     */
    private function isRequirementRow(array $row): bool
    {
        foreach ($row as $value) {
            if (in_array(strtolower(trim($value)), ['mandatory', 'notmandatory'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate single row
     */
    private function validateRow(ImportRowDTO $row, array $allRows): array
    {
        $errors = [];

        // Validate site-level fields
        $errors = array_merge($errors, $this->validateSiteFields($row));

        // Validate device-level fields
        $errors = array_merge($errors, $this->validateDeviceFields($row));

        return $errors;
    }

    /**
     * Validate site-level fields
     */
    private function validateSiteFields(ImportRowDTO $row): array
    {
        $errors = [];

        // 1. Validate sitemodule (protocol)
        $protocolErrors = $this->validateProtocol($row);
        $errors = array_merge($errors, $protocolErrors);

        if (empty($protocolErrors)) {
            $protocol = $this->getProtocolModule($row->siteModule);

            // 2. Validate numbers
            $errors = array_merge($errors, $this->validateNumbers($row, $protocol));

            // 3. Validate address
            $errors = array_merge($errors, $this->validateAddress($row, $protocol));
        }

        // 4. Validate custom site fields
        $errors = array_merge($errors, $this->validateCustomFields($row, true));

        return $errors;
    }

    /**
     * Validate protocol (sitemodule)
     */
    private function validateProtocol(ImportRowDTO $row): array
    {
        if (empty($row->siteModule)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'sitemodule (protocol)',
                value: null,
                message: trans('Site module (protocol) is required')
            )];
        }

        $protocol = $this->getProtocolModule($row->siteModule);

        if (!$protocol) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'sitemodule (protocol)',
                value: $row->siteModule,
                message: trans('Invalid site module (protocol): :value', ['value' => $row->siteModule])
            )];
        }

        // Check if account has access to this protocol (unless unrestricted)
        if ($this->enforceModuleAccess && !$this->accountModules->contains($protocol->module_id)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'sitemodule (protocol)',
                value: $row->siteModule,
                message: trans('Account does not have access to this protocol: :value', ['value' => $row->siteModule])
            )];
        }

        return [];
    }

    /**
     * Get protocol module by name
     */
    private function getProtocolModule(?string $moduleName): ?Module
    {
        if (empty($moduleName)) {
            return null;
        }

        return $this->modules->first(function ($module) use ($moduleName) {
            return ($module->module_name === $moduleName || $module->module_desc === $moduleName)
                && $module->module_type->mt_type === 'PROTOCOL';
        });
    }

    /**
     * Validate phone numbers
     */
    private function validateNumbers(ImportRowDTO $row, Module $protocol): array
    {
        $errors = [];
        $numbers = $row->getNormalizedPhoneNumbers();

        // Check if numbers are required
        $areNumbersRequired = $this->profileAccess->isFieldRequired($protocol, 'numbers');

        if (empty($numbers) && $areNumbersRequired) {
            $errors[] = new ImportError(
                row: $row->rowNumber,
                column: 'numbers',
                value: null,
                message: trans('At least one phone number is required for this protocol')
            );
            return $errors;
        }

        // Validate each number
        foreach ($numbers as $type => $number) {
            // Check format (must start with +)
            if (!preg_match('/^\+\d{10,15}$/', $number)) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: $type . 'Number',
                    value: $number,
                    message: trans('Invalid phone number format. Must be +XXXXXXXXXXX (10-15 digits)')
                );
                continue;
            }

            // Check if number already exists and is assigned to another site
            $existingNumber = $this->existingNumbers->first(fn($n) => $n->number_value === $number);

            if ($existingNumber) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: $type . 'Number',
                    value: $number,
                    message: trans('Phone number :number is already assigned to another site', ['number' => $number]),
                    severity: 'warning' // Warning because it might be intentional (adding device to existing site)
                );
            }
        }

        return $errors;
    }

    /**
     * Validate address
     */
    private function validateAddress(ImportRowDTO $row, Module $protocol): array
    {
        $errors = [];

        $hasAnyAddressField = $row->hasAnyAddressField();
        $hasCompleteAddress = $row->hasCompleteAddress();
        $isAddressRequired = $this->profileAccess->isFieldRequired($protocol, 'address');

        // If address is required, all fields must be present
        if ($isAddressRequired && !$hasCompleteAddress) {
            if (!$row->street) {
                $errors[] = new ImportError($row->rowNumber, 'Street', null, trans('Street is required'));
            }
            if (!$row->zip) {
                $errors[] = new ImportError($row->rowNumber, 'ZIP', null, trans('ZIP code is required'));
            }
            if (!$row->city) {
                $errors[] = new ImportError($row->rowNumber, 'City', null, trans('City is required'));
            }
            if (!$row->country) {
                $errors[] = new ImportError($row->rowNumber, 'Country', null, trans('Country is required'));
            }
            return $errors;
        }

        // If any address field is filled, all must be filled
        if ($hasAnyAddressField && !$hasCompleteAddress) {
            $errors[] = new ImportError(
                row: $row->rowNumber,
                column: 'Address',
                value: null,
                message: trans('Address is incomplete. All address fields (Street, ZIP, City, Country) must be filled together.')
            );
            return $errors;
        }

        // If address is complete, validate each field
        if ($hasCompleteAddress) {
            // Validate country
            $country = $this->countriesCache->first(function ($c) use ($row) {
                return $c->country_iso === $row->country || strtolower($c->country_iso) === strtolower($row->country);
            });

            if (!$country) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: 'Country',
                    value: $row->country,
                    message: trans('Invalid country code: :value', ['value' => $row->country])
                );
            }

            // Validate street format - allow extended character set for international addresses
            if (!preg_match("/^[\p{L}0-9 '\",.\\-_()\/&#+*:;!?@]{1,90}$/u", $row->street)) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: 'Street',
                    value: $row->street,
                    message: trans('Street contains invalid characters')
                );
            }

            // Validate city format - allow extended character set for international addresses
            if (!preg_match("/^[\p{L}0-9 '\",.\\-_()\/&#+*:;!?@]{1,90}$/u", $row->city)) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: 'City',
                    value: $row->city,
                    message: trans('City contains invalid characters')
                );
            }

            // Validate ZIP format
            if (!preg_match("/^[[:alnum:]\-_]+$/", $row->zip)) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: 'ZIP',
                    value: $row->zip,
                    message: trans('ZIP code contains invalid characters')
                );
            }
        }

        return $errors;
    }

    /**
     * Validate custom fields (site or device)
     */
    private function validateCustomFields(ImportRowDTO $row, bool $isSiteField): array
    {
        $errors = [];
        $customFields = $isSiteField ? $row->siteCustomFields : $row->deviceCustomFields;

        foreach ($customFields as $fieldName => $value) {
            // Find config for this custom field
            $config = $this->customFieldConfigs->first(function ($cfg) use ($fieldName, $isSiteField) {
                return $cfg->cfc_name === $fieldName
                    && (bool)$cfg->cfc_is_device === !$isSiteField;
            });

            if (!$config) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: $fieldName . ($isSiteField ? '(sitecustomfield)' : '(devicecustomfield)'),
                    value: $value,
                    message: trans('Custom field :field does not exist for this account', ['field' => $fieldName])
                );
            }
        }

        return $errors;
    }

    /**
     * Validate device-level fields
     */
    private function validateDeviceFields(ImportRowDTO $row): array
    {
        $errors = [];

        // 1. Validate EquipmentID
        $errors = array_merge($errors, $this->validateEquipmentId($row));

        // 2. Validate devicetype
        $deviceTypeErrors = $this->validateDeviceType($row);
        $errors = array_merge($errors, $deviceTypeErrors);

        // 3. Validate device module
        $moduleErrors = $this->validateDeviceModule($row);
        $errors = array_merge($errors, $moduleErrors);

        // If protocol and device module are valid, validate compatibility
        if (empty($deviceTypeErrors) && empty($moduleErrors)) {
            $errors = array_merge($errors, $this->validateProtocolDeviceCompatibility($row));
        }

        // 4. Validate Identity (depends on module flags)
        $errors = array_merge($errors, $this->validateIdentity($row));

        // 5. Validate Module number (depends on module flags)
        $errors = array_merge($errors, $this->validateModuleNumber($row));

        // 6. Validate Pin (depends on module)
        $errors = array_merge($errors, $this->validatePin($row));

        // 7. Validate MAC/IMEI (for gateways)
        $errors = array_merge($errors, $this->validateMacImei($row));

        // 8. Validate DeviceStatus
        $errors = array_merge($errors, $this->validateDeviceStatus($row));

        // 9. Validate custom device fields
        $errors = array_merge($errors, $this->validateCustomFields($row, false));

        return $errors;
    }

    /**
     * Validate EquipmentID
     */
    private function validateEquipmentId(ImportRowDTO $row): array
    {
        if (empty($row->equipmentId)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'EquipmentID',
                value: null,
                message: trans('Equipment ID is required')
            )];
        }

        // Check if already exists
        $exists = $this->existingDevices->first(fn($d) => $d->device_equipment === $row->equipmentId);

        if ($exists) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'EquipmentID',
                value: $row->equipmentId,
                message: trans('Equipment ID :id already exists', ['id' => $row->equipmentId])
            )];
        }

        return [];
    }

    /**
     * Validate devicetype
     */
    private function validateDeviceType(ImportRowDTO $row): array
    {
        if (empty($row->deviceType)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'devicetype',
                value: null,
                message: trans('Device type is required')
            )];
        }

        $validTypes = ['gateway', 'telealarm', 'intercom', 'monitor'];
        $deviceType = strtolower($row->deviceType);

        if (!in_array($deviceType, $validTypes)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'devicetype',
                value: $row->deviceType,
                message: trans('Invalid device type. Must be one of: :types', [
                    'types' => implode(', ', $validTypes)
                ])
            )];
        }

        return [];
    }

    /**
     * Validate device module name
     */
    private function validateDeviceModule(ImportRowDTO $row): array
    {
        if (empty($row->deviceModule)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'devicemodule',
                value: null,
                message: trans('Device module is required')
            )];
        }

        $deviceModule = $this->getDeviceModule($row->deviceModule);

        if (!$deviceModule) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'devicemodule',
                value: $row->deviceModule,
                message: trans('Invalid device module: :value', ['value' => $row->deviceModule])
            )];
        }

        // Check if module type matches devicetype
        $expectedType = strtoupper($row->deviceType);
        if ($deviceModule->module_type->mt_type !== $expectedType) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'devicemodule',
                value: $row->deviceModule,
                message: trans('Device module :module type (:actual) does not match devicetype (:expected)', [
                    'module' => $row->deviceModule,
                    'actual' => $deviceModule->module_type->mt_type,
                    'expected' => $expectedType
                ])
            )];
        }

        return [];
    }

    /**
     * Get device module by name
     */
    private function getDeviceModule(?string $moduleName): ?Module
    {
        if (empty($moduleName)) {
            return null;
        }

        // Exact module name match first
        $nameMatch = $this->modules->first(function ($module) use ($moduleName) {
            return strcasecmp($module->module_name, $moduleName) === 0;
        });
        if ($nameMatch && $nameMatch->module_type && $nameMatch->module_type->mt_type !== 'PROTOCOL') {
            return $nameMatch;
        }

        // Prefer description matches for non-protocol modules
        $descMatch = $this->modules->first(function ($module) use ($moduleName) {
            return $module->module_type && $module->module_type->mt_type !== 'PROTOCOL'
                && strcasecmp($module->module_desc ?? '', $moduleName) === 0;
        });
        if ($descMatch) {
            return $descMatch;
        }

        // Fallback to any module with matching name/desc (including protocols)
        if ($nameMatch) {
            return $nameMatch;
        }

        return $this->modules->first(function ($module) use ($moduleName) {
            return strcasecmp($module->module_desc ?? '', $moduleName) === 0;
        });
    }

    /**
     * Validate protocol and device module compatibility
     */
    private function validateProtocolDeviceCompatibility(ImportRowDTO $row): array
    {
        $protocol = $this->getProtocolModule($row->siteModule);
        $deviceModule = $this->getDeviceModule($row->deviceModule);

        if (!$protocol || !$deviceModule) {
            return []; // Already validated separately
        }

        // Check modules_matrix
        $protocolName = $protocol->module_name;
        $deviceModuleName = $deviceModule->module_name;

        if (!isset($this->moduleCompatibilityMatrix[$protocolName]) ||
            !in_array($deviceModuleName, $this->moduleCompatibilityMatrix[$protocolName])) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'devicemodule',
                value: $deviceModuleName,
                message: trans('Device module :device is not compatible with protocol :protocol', [
                    'device' => $deviceModuleName,
                    'protocol' => $protocolName
                ])
            )];
        }

        return [];
    }

    /**
     * Validate Identity field
     */
    private function validateIdentity(ImportRowDTO $row): array
    {
        $deviceModule = $this->getDeviceModule($row->deviceModule);
        if (!$deviceModule) {
            return []; // Already validated
        }

        $protocol = $this->getProtocolModule($row->siteModule);
        if (!$protocol) {
            return []; // Already validated
        }

        // Check if identity is required (from module flags)
        $isIdentityRequired = boolval($deviceModule->module_flags & ModuleFlags::MODULE_FLAG_IDENTITY_REQUIRED->value) ||
                              boolval($protocol->module_flags & ModuleFlags::MODULE_FLAG_IDENTITY_REQUIRED->value);

        if ($isIdentityRequired && empty($row->identity) && $row->identity !== '0') {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'Identity',
                value: null,
                message: trans('Identity is required for this device module')
            )];
        }

        // If identity is provided, check uniqueness (identity + module combination)
        if ($row->identity !== null && $row->identity !== '') {
            $moduleNumber = $row->module ?? 0;

            $exists = $this->existingDevices->first(function ($device) use ($row, $moduleNumber) {
                return ($device->device_identity === $row->identity || $device->device_setidentity === $row->identity)
                    && (int)$device->device_module === (int)$moduleNumber;
            });

            if ($exists) {
                return [new ImportError(
                    row: $row->rowNumber,
                    column: 'Identity',
                    value: $row->identity,
                    message: trans('Identity :identity with module :module already exists', [
                        'identity' => $row->identity,
                        'module' => $moduleNumber
                    ])
                )];
            }
        }

        return [];
    }

    /**
     * Validate Module number field
     */
    private function validateModuleNumber(ImportRowDTO $row): array
    {
        $deviceModule = $this->getDeviceModule($row->deviceModule);
        if (!$deviceModule) {
            return []; // Already validated
        }

        $protocol = $this->getProtocolModule($row->siteModule);
        if (!$protocol) {
            return []; // Already validated
        }

        // Check if module number is required (from module flags)
        $isModuleRequired = boolval($deviceModule->module_flags & ModuleFlags::MODULE_FLAG_MODULE_REQUIRED->value) ||
                            boolval($protocol->module_flags & ModuleFlags::MODULE_FLAG_MODULE_REQUIRED->value);

        if ($isModuleRequired && ($row->module === null || $row->module === '')) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'Module (number)',
                value: null,
                message: trans('Module number is required for this device')
            )];
        }

        // Validate format (must be integer)
        if ($row->module !== null && $row->module !== '' && !is_numeric($row->module)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'Module (number)',
                value: $row->module,
                message: trans('Module must be a number')
            )];
        }

        return [];
    }

    /**
     * Validate Pin field
     */
    private function validatePin(ImportRowDTO $row): array
    {
        $protocol = $this->getProtocolModule($row->siteModule);
        if (!$protocol) {
            return []; // Already validated
        }

        $isPinRequired = $this->profileAccess->isFieldRequired($protocol, 'pin');

        if ($isPinRequired && empty($row->pin)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'Pin',
                value: null,
                message: trans('Pin is required for this protocol')
            )];
        }

        // Pin uniqueness will be validated in cross-row validation
        // because we need to know which site the device belongs to

        return [];
    }

    /**
     * Validate MAC/IMEI for gateways
     */
    private function validateMacImei(ImportRowDTO $row): array
    {
        $errors = [];

        // Only validate for gateways
        if (strtolower($row->deviceType) !== 'gateway') {
            return [];
        }

        // Validate MAC address
        if (!empty($row->macAddress)) {
            // Format: 12 hex digits
            $mac = strtolower(preg_replace('/[^0-9a-f]/i', '', $row->macAddress));

            if (!preg_match('/^([0-9a-f]{2}){6}$/', $mac)) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: 'MACAddress',
                    value: $row->macAddress,
                    message: trans('Invalid MAC address format. Expected 12 hex digits.')
                );
            } else {
                // Check if already exists and is unavailable
                // Gateway is unavailable if: different account OR (same account AND active AND assigned)
                $exists = $this->existingGateways->first(function ($gw) use ($mac) {
                    return strtolower($gw->dg_mac) === $mac
                        && (
                            $gw->dg_account_id != $this->accountId
                            || (!$gw->dg_deleted && $gw->dg_device_id)
                        );
                });

                if ($exists) {
                    $errors[] = new ImportError(
                        row: $row->rowNumber,
                        column: 'MACAddress',
                        value: $row->macAddress,
                        message: trans('MAC address :mac already exists', ['mac' => $row->macAddress])
                    );
                }
            }
        }

        // Validate IMEI
        if (!empty($row->imeiNumber)) {
            // IMEI: 15 digits, Luhn algorithm
            if (!$this->isValidImei($row->imeiNumber)) {
                $errors[] = new ImportError(
                    row: $row->rowNumber,
                    column: 'IMEI',
                    value: $row->imeiNumber,
                    message: trans('Invalid IMEI number')
                );
            } else {
                // Check if already exists and is unavailable
                // Gateway is unavailable if: different account OR (same account AND active AND assigned)
                $exists = $this->existingGateways->first(function ($gw) use ($row) {
                    return $gw->dg_imei === $row->imeiNumber
                        && (
                            $gw->dg_account_id != $this->accountId
                            || (!$gw->dg_deleted && $gw->dg_device_id)
                        );
                });

                if ($exists) {
                    $errors[] = new ImportError(
                        row: $row->rowNumber,
                        column: 'IMEI',
                        value: $row->imeiNumber,
                        message: trans('IMEI :imei already exists', ['imei' => $row->imeiNumber])
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * Validate IMEI using Luhn algorithm
     */
    private function isValidImei(?string $imei): bool
    {
        if (empty($imei) || strlen($imei) != 15 || !is_numeric($imei)) {
            return false;
        }

        // Luhn algorithm
        $sum = 0;
        foreach (str_split(strrev($imei)) as $i => $d) {
            $sum += $i % 2 !== 0 ? array_sum(str_split($d * 2)) : $d;
        }

        return $sum % 10 === 0;
    }

    /**
     * Validate DeviceStatus
     */
    private function validateDeviceStatus(ImportRowDTO $row): array
    {
        if (empty($row->deviceStatus)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'DeviceStatus',
                value: null,
                message: trans('Device status is required')
            )];
        }

        $validStatuses = ['enabled', 'disabled'];
        $status = strtolower($row->deviceStatus);

        if (!in_array($status, $validStatuses)) {
            return [new ImportError(
                row: $row->rowNumber,
                column: 'DeviceStatus',
                value: $row->deviceStatus,
                message: trans('Invalid device status. Must be "Enabled" or "Disabled"')
            )];
        }

        return [];
    }

    /**
     * Cross-row validation (check for duplicates within the file)
     */
    private function validateCrossRows(array $rowDTOs): array
    {
        $errors = [];

        // Check for duplicate equipment IDs within file
        $equipmentIds = [];
        foreach ($rowDTOs as $row) {
            if (!empty($row->equipmentId)) {
                if (in_array($row->equipmentId, $equipmentIds)) {
                    $errors[] = new ImportError(
                        row: $row->rowNumber,
                        column: 'EquipmentID',
                        value: $row->equipmentId,
                        message: trans('Duplicate Equipment ID in file')
                    );
                }
                $equipmentIds[] = $row->equipmentId;
            }
        }

        // Check for address conflicts for same phone number
        // Same number is OK - it means multiple devices on same site
        // But same number with DIFFERENT addresses is a conflict!
        $numberToAddressMap = [];
        foreach ($rowDTOs as $row) {
            foreach ($row->getNormalizedPhoneNumbers() as $type => $number) {
                $addressKey = $this->getAddressKey($row);

                if (isset($numberToAddressMap[$number])) {
                    // Number already seen - check if address matches
                    if ($numberToAddressMap[$number]['address'] !== $addressKey) {
                        $errors[] = new ImportError(
                            row: $row->rowNumber,
                            column: $type . 'Number',
                            value: $number,
                            message: trans('Phone number :number has conflicting addresses (row :row1 vs row :row2)', [
                                'number' => $number,
                                'row1' => $numberToAddressMap[$number]['row'],
                                'row2' => $row->rowNumber
                            ]),
                            severity: 'error'
                        );
                    }
                    // If addresses match - no problem, it's the same site
                } else {
                    $numberToAddressMap[$number] = [
                        'address' => $addressKey,
                        'row' => $row->rowNumber
                    ];
                }
            }
        }

        // Check for duplicate MAC addresses within file
        $macAddresses = [];
        foreach ($rowDTOs as $row) {
            if (!empty($row->macAddress)) {
                $mac = strtolower(preg_replace('/[^0-9a-f]/i', '', $row->macAddress));
                if (isset($macAddresses[$mac])) {
                    $errors[] = new ImportError(
                        row: $row->rowNumber,
                        column: 'MACAddress',
                        value: $row->macAddress,
                        message: trans('Duplicate MAC address in file (also in row :other)', [
                            'other' => $macAddresses[$mac]
                        ])
                    );
                } else {
                    $macAddresses[$mac] = $row->rowNumber;
                }
            }
        }

        // Validate PIN uniqueness per site
        $errors = array_merge($errors, $this->validatePinUniquenessPerSite($rowDTOs));

        // Validate partial number overlaps with existing sites
        $errors = array_merge($errors, $this->validatePartialNumberOverlaps($rowDTOs));

        return $errors;
    }

    /**
     * Validate PIN uniqueness per site (requires site identification)
     */
    private function validatePinUniquenessPerSite(array $rowDTOs): array
    {
        $errors = [];

        // Group rows by site key
        $siteGroups = [];
        foreach ($rowDTOs as $row) {
            $siteKey = $row->generateSiteKey();
            if (!isset($siteGroups[$siteKey])) {
                $siteGroups[$siteKey] = [];
            }
            $siteGroups[$siteKey][] = $row;
        }

        // Check PIN uniqueness within each site group
        foreach ($siteGroups as $siteKey => $rows) {
            $pinsPerModule = [];

            foreach ($rows as $row) {
                if (empty($row->pin)) {
                    continue;
                }

                $deviceModule = $this->getDeviceModule($row->deviceModule);
                if (!$deviceModule) {
                    continue;
                }

                $moduleId = $deviceModule->module_id;
                $pin = $row->pin;

                if (!isset($pinsPerModule[$moduleId])) {
                    $pinsPerModule[$moduleId] = [];
                }

                if (isset($pinsPerModule[$moduleId][$pin])) {
                    $errors[] = new ImportError(
                        row: $row->rowNumber,
                        column: 'Pin',
                        value: $pin,
                        message: trans('Duplicate PIN :pin for module :module in same site (also in row :other)', [
                            'pin' => $pin,
                            'module' => $row->deviceModule,
                            'other' => $pinsPerModule[$moduleId][$pin]
                        ])
                    );
                } else {
                    $pinsPerModule[$moduleId][$pin] = $row->rowNumber;
                }
            }

            // Also check against existing devices in database for this site
            // (if site exists based on phone numbers)
            foreach ($rows as $row) {
                $numbers = $row->getNormalizedPhoneNumbers();
                if (empty($numbers)) {
                    continue;
                }

                // Find existing site by numbers
                foreach ($numbers as $number) {
                    $existingNumber = $this->existingNumbers->first(fn($n) => $n->number_value === $number);
                    if ($existingNumber && $existingNumber->number_ds_id) {
                        // Found existing site, check pins
                        $deviceModule = $this->getDeviceModule($row->deviceModule);
                        if ($deviceModule && !empty($row->pin)) {
                            $pinExists = $this->existingDevices->first(function ($device) use ($existingNumber, $deviceModule, $row) {
                                return $device->device_ds_id == $existingNumber->number_ds_id
                                    && $device->device_module_id == $deviceModule->module_id
                                    && ($device->device_pin === $row->pin || $device->device_setpin === $row->pin);
                            });

                            if ($pinExists) {
                                $errors[] = new ImportError(
                                    row: $row->rowNumber,
                                    column: 'Pin',
                                    value: $row->pin,
                                    message: trans('PIN :pin already exists for this site and module', [
                                        'pin' => $row->pin
                                    ])
                                );
                            }
                        }
                        break; // Only need to check once per row
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Generate summary
     */
    private function generateSummary(array $rowDTOs, ImportValidationResult $result): ImportSummary
    {
        $summary = new ImportSummary();
        $summary->totalRows = count($rowDTOs);
        $summary->totalErrors = count($result->getErrors());
        $summary->totalWarnings = count($result->getWarnings());

        // Count valid vs invalid rows
        $errorsByRow = $result->getErrorsByRow();
        $summary->invalidRows = count($errorsByRow);
        $summary->validRows = $summary->totalRows - $summary->invalidRows;

        // Count new vs existing sites based on phone numbers
        $siteKeys = [];
        foreach ($rowDTOs as $row) {
            $siteKey = $row->generateSiteKey();

            if (!isset($siteKeys[$siteKey])) {
                // Check if site exists (by phone numbers)
                $numbers = $row->getNormalizedPhoneNumbers();
                $exists = false;

                foreach ($numbers as $number) {
                    if ($this->existingNumbers->first(fn($n) => $n->number_value === $number && $n->number_ds_id)) {
                        $exists = true;
                        break;
                    }
                }

                $siteKeys[$siteKey] = $exists;
                $summary->rowToSiteKeyMap[$row->rowNumber] = $siteKey;

                if ($exists) {
                    $summary->existingSites++;
                    $summary->existingSiteKeys[] = $siteKey;
                } else {
                    $summary->newSites++;
                    $summary->siteKeysToCreate[] = $siteKey;
                }
            }
        }

        // All rows are new devices
        $summary->newDevices = $summary->totalRows;

        return $summary;
    }

    /**
     * Generate address key for comparison
     * Returns normalized string of address components
     */
    private function getAddressKey(ImportRowDTO $row): string
    {
        // Normalize address components for comparison
        $parts = [
            strtolower(trim($row->street ?? '')),
            strtolower(trim($row->zip ?? '')),
            strtolower(trim($row->city ?? '')),
            strtolower(trim($row->country ?? ''))
        ];

        // Remove empty parts
        $parts = array_filter($parts);

        // If no address at all, return special marker
        if (empty($parts)) {
            return '__NO_ADDRESS__';
        }

        return implode('|', $parts);
    }

    /**
     * Validate that phone numbers don't partially overlap with existing sites
     * Partial overlap = some numbers match existing site but not all
     */
    private function validatePartialNumberOverlaps(array $rowDTOs): array
    {
        $errors = [];

        // Build map of existing site numbers
        $siteNumbersMap = [];
        foreach ($this->existingNumbers as $number) {
            if ($number->number_ds_id) {
                $siteId = $number->number_ds_id;
                if (!isset($siteNumbersMap[$siteId])) {
                    $siteNumbersMap[$siteId] = [];
                }
                $siteNumbersMap[$siteId][] = $number->number_value;
            }
        }

        // Check each row's numbers against existing sites
        foreach ($rowDTOs as $row) {
            $rowNumbers = $row->getNormalizedPhoneNumbers();

            if (empty($rowNumbers)) {
                continue;
            }

            // Sort row numbers ONCE before the loop (optimization)
            sort($rowNumbers);

            // Find potential sites (sites that have at least one matching number)
            $potentialSites = [];
            foreach ($rowNumbers as $number) {
                foreach ($siteNumbersMap as $siteId => $siteNumbers) {
                    if (in_array($number, $siteNumbers)) {
                        if (!isset($potentialSites[$siteId])) {
                            $potentialSites[$siteId] = [
                                'siteNumbers' => $siteNumbers,
                                'matchingNumbers' => []
                            ];
                        }
                        $potentialSites[$siteId]['matchingNumbers'][] = $number;
                    }
                }
            }

            // Check each potential site for 100% match
            foreach ($potentialSites as $siteId => $data) {
                $siteNumbers = $data['siteNumbers'];

                // Sort site numbers for comparison
                sort($siteNumbers);

                // If not 100% match - this is a partial overlap ERROR
                if ($rowNumbers !== $siteNumbers) {
                    $nonMatchingRow = array_diff($rowNumbers, $siteNumbers);
                    $nonMatchingSite = array_diff($siteNumbers, $rowNumbers);

                    $message = trans('Partial phone number overlap with existing site :site_id. ', [
                        'site_id' => $siteId
                    ]);

                    if (!empty($nonMatchingRow)) {
                        $message .= trans('Your numbers not in site: :numbers. ', [
                            'numbers' => implode(', ', $nonMatchingRow)
                        ]);
                    }

                    if (!empty($nonMatchingSite)) {
                        $message .= trans('Site has additional numbers: :numbers.', [
                            'numbers' => implode(', ', $nonMatchingSite)
                        ]);
                    }

                    $errors[] = new ImportError(
                        row: $row->rowNumber,
                        column: 'PhoneNumbers',
                        value: implode(', ', $rowNumbers),
                        message: $message
                    );
                }
            }
        }

        return $errors;
    }
}
