<?php

namespace App\Services\Import;

use App\DTO\Import\ImportRowDTO;
use App\Helpers\GroupCache;
use App\Models\Address;
use App\Models\Country;
use App\Models\CustomFieldConfig;
use App\Models\CustomFieldValue;
use App\Models\Device;
use App\Models\DeviceGateway;
use App\Models\DeviceSite;
use App\Models\DeviceSiteSetting;
use App\Models\Location;
use App\Models\Module;
use App\Models\Number;
use App\Models\NumberType;
use App\Services\DeviceGatewayPersistenceService;
use App\Services\ProfileAccessService;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class ImportService
{
    private const ALLOWED_EXTENSIONS = ['csv', 'xlsx', 'xls'];
    private const MAX_FILE_SIZE = 2 * 1024 * 1024;

    private ImportValidationService $validator;
    private ProfileAccessService $profileAccess;
    private DeviceGatewayPersistenceService $gatewayPersistence;
    private bool $copyNumberToCli = true;

    // Cache for lookups during import
    private array $moduleCache = [];
    private array $countryCache = [];
    private array $numberTypeCache = [];
    private array $customFieldConfigCache = [];
    private array $existingNumbersCache = [];
    private array $siteNumbersCache = []; // Map site_id => [all phone numbers]
    private array $cliSettingIds = [];

    // Site tracking
    private array $siteKeyToIdMap = [];
    private array $createdSites = [];

    public function __construct(
        ImportValidationService $validator,
        ProfileAccessService $profileAccess,
        DeviceGatewayPersistenceService $gatewayPersistence
    ) {
        $this->validator = $validator;
        $this->profileAccess = $profileAccess;
        $this->gatewayPersistence = $gatewayPersistence;
        ini_set('max_execution_time', 120);
    }


    public function validateFile(UploadedFile $file, int $accountId): ImportValidationResult
    {
        return $this->validator->validate($this->parse($file), $accountId);
    }


    public function execute(UploadedFile $file, int $accountId, bool $copyNumberToCli = true): array
    {
        $this->copyNumberToCli = $copyNumberToCli;

        $rows = $this->parse($file);

        // Validate first
        $validationResult = $this->validator->validate($rows, $accountId);

        if (!$validationResult->isValid()) {
            throw new Exception(trans('Import file contains errors. Please fix them and try again.'));
        }

        // Parse rows to DTOs
        $rowDTOs = $this->parseRowsToDTO($rows);

        // Pre-cache necessary data
        $this->preCacheData($accountId);

        // START SQL QUERY LOGGING
        $queryLog = [];
        DB::listen(function ($query) use (&$queryLog) {
            $queryLog[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];
        });

        // Execute import in transaction
        DB::beginTransaction();
        try {
            $result = $this->executeImport($rowDTOs, $accountId, $copyNumberToCli);

            DB::commit();

            // FLUSH SQL QUERIES TO LOG
            Log::info('IMPORT SQL QUERIES', [
                'account_id' => $accountId,
                'total_queries' => count($queryLog),
                'queries' => $queryLog
            ]);

            // Invalidate caches
            $this->invalidateCaches();

            return [
                'success' => true,
                'message' => trans('Import completed successfully'),
                'created' => $result
            ];

        } catch (Exception $e) {
            DB::rollBack();

            // FLUSH SQL QUERIES TO LOG (even on error)
            Log::error('IMPORT SQL QUERIES FAILED', [
                'account_id' => $accountId,
                'total_queries' => count($queryLog),
                'queries' => $queryLog
            ]);

            Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception(trans('Import failed: :error', ['error' => $e->getMessage()]));
        }
    }

    private function normalizeColumnName(string $columnName): string
    {
        $normalized = strtolower($columnName);
        $normalized = preg_replace('/\s*\([^)]*\)/', '', $normalized);
        $normalized = preg_replace('/[\s\-_]+/', '', $normalized);
        return $normalized;
    }

    private function parseRowsToDTO(array $rows): array
    {
        $dtos = [];

        // Skip header row and optional requirement row
        $startIndex = 1;
        if (isset($rows[1]) && $this->isRequirementRow($rows[1])) {
            $startIndex = 2;
        }

        // Normalized column mapping (same as in ImportValidationService)
        $columnMapping = [
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

        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNumber = $i + 1;

            $dto = new ImportRowDTO($rowNumber, $row);

            // Map standard columns (case-insensitive)
            foreach ($row as $originalColumn => $value) {
                $normalizedColumn = $this->normalizeColumnName($originalColumn);

                // Check if this is a standard column
                if (isset($columnMapping[$normalizedColumn])) {
                    $property = $columnMapping[$normalizedColumn];
                    $trimmedValue = trim($value);
                    $dto->$property = empty($trimmedValue) ? null : $trimmedValue;
                }
                // Check for custom fields (case-insensitive)
                elseif (stripos($originalColumn, 'sitecustomfield') !== false) {
                    $fieldName = preg_replace('/\s*\(?\s*sitecustomfield\s*\)?\s*/i', '', $originalColumn);
                    $dto->siteCustomFields[trim($fieldName)] = trim($value);
                }
                elseif (stripos($originalColumn, 'devicecustomfield') !== false) {
                    $fieldName = preg_replace('/\s*\(?\s*devicecustomfield\s*\)?\s*/i', '', $originalColumn);
                    $dto->deviceCustomFields[trim($fieldName)] = trim($value);
                }
            }

            $dtos[] = $dto;
        }

        return $dtos;
    }

    private function isRequirementRow(array $row): bool
    {
        foreach ($row as $value) {
            if (in_array(strtolower(trim($value)), ['mandatory', 'notmandatory'])) {
                return true;
            }
        }
        return false;
    }

    private function preCacheData(int $accountId): void
    {
        // Cache modules
        Module::all()->each(function($module) {
            $this->moduleCache[$module->module_name] = $module;
            if ($module->module_desc) {
                $this->moduleCache[$module->module_desc] = $module;
            }
        });

        // Cache countries
        Country::all()->each(function($country) {
            $this->countryCache[strtoupper($country->country_iso)] = $country;
        });

        // Cache number types
        NumberType::all()->each(function($type) {
            $this->numberTypeCache[$type->nt_type] = $type;
        });

        // Cache custom field configs
        CustomFieldConfig::where('cfc_account_id', $accountId)->get()->each(function($config) {
            $key = $config->cfc_name . '_' . ($config->cfc_is_device ? 'device' : 'site');
            $this->customFieldConfigCache[$key] = $config;
        });

        // Cache existing numbers for site identification
        Number::whereNotNull('number_ds_id')->get()->each(function($number) {
            $this->existingNumbersCache[$number->number_value] = $number->number_ds_id;

            // Also build site numbers cache (all numbers per site)
            $siteId = $number->number_ds_id;
            if (!isset($this->siteNumbersCache[$siteId])) {
                $this->siteNumbersCache[$siteId] = [];
            }
            $this->siteNumbersCache[$siteId][] = $number->number_value;
        });
    }

    private function executeImport(array $rowDTOs, int $accountId, bool $copyNumberToCli): array
    {
        $stats = [
            'sites' => 0,
            'devices' => 0,
            'existingSites' => 0
        ];

        // Group rows by site
        $siteGroups = $this->groupRowsBySite($rowDTOs);

        // Process each site group
        foreach ($siteGroups as $siteKey => $rows) {
            $siteId = $this->processSiteGroup($siteKey, $rows, $accountId, $copyNumberToCli);

            if (isset($this->createdSites[$siteKey])) {
                $stats['sites']++;
            } else {
                $stats['existingSites']++;
            }

            // Track devices
            $stats['devices'] += count($rows);
        }

        return $stats;
    }

    /**
     * Group rows by site (based on phone numbers)
     */
    private function groupRowsBySite(array $rowDTOs): array
    {
        $groups = [];

        foreach ($rowDTOs as $row) {
            $siteKey = $row->generateSiteKey();

            if (!isset($groups[$siteKey])) {
                $groups[$siteKey] = [];
            }

            $groups[$siteKey][] = $row;
        }

        return $groups;
    }

    /**
     * Process site group (create or find site, create devices)
     */
    private function processSiteGroup(string $siteKey, array $rows, int $accountId, bool $copyNumberToCli): int
    {
        // Use first row for site data
        $firstRow = $rows[0];

        // Try to find existing site by phone numbers
        $existingSiteId = $this->findExistingSite($firstRow);

        if ($existingSiteId) {
            $siteId = $existingSiteId;
            $this->siteKeyToIdMap[$siteKey] = $siteId;
        } else {
            // Create new site
            $siteId = $this->createSite($firstRow, $accountId, $copyNumberToCli);
            $this->siteKeyToIdMap[$siteKey] = $siteId;
            $this->createdSites[$siteKey] = $siteId;
        }

        // Create devices for this site
        foreach ($rows as $row) {
            $this->createDevice($row, $siteId, $accountId);
        }

        return $siteId;
    }

    /**
     * Find existing site by phone numbers
     * Requires 100% match - all numbers must match exactly
     */
    private function findExistingSite(ImportRowDTO $row): ?int
    {
        $rowNumbers = $row->getNormalizedPhoneNumbers();

        if (empty($rowNumbers)) {
            return null;
        }

        sort($rowNumbers);

        // Find potential sites (sites that have at least one matching number)
        $potentialSites = [];
        foreach ($rowNumbers as $number) {
            if (isset($this->existingNumbersCache[$number])) {
                $siteId = $this->existingNumbersCache[$number];
                $potentialSites[$siteId] = true;
            }
        }

        if (empty($potentialSites)) {
            return null;
        }

        // Check each potential site for 100% match
        foreach (array_keys($potentialSites) as $siteId) {
            $siteNumbers = $this->siteNumbersCache[$siteId] ?? [];

            sort($siteNumbers);

            if ($rowNumbers === $siteNumbers) {
                return $siteId;
            }
        }

        // No exact match found - partial overlap exists but not 100% - this should be caught by validation
        return null;
    }

    private function createSite(ImportRowDTO $row, int $accountId, bool $copyNumberToCli): int
    {
        $protocol = $this->moduleCache[$row->siteModule];

        $addressId = null;
        if ($row->hasCompleteAddress()) {
            $addressId = $this->createAddress($row);
        }

        $site = DeviceSite::create([
            'ds_name' => $row->siteName ?: null,
            'ds_protocol_id' => $protocol->module_id,
            'ds_account_id' => $accountId,
            'ds_address_id' => $addressId,
            'ds_link' => $row->externalLink,
        ]);

        // Create numbers
        $createdNumbers = $this->createNumbers($row, $site->ds_id, $accountId);

        // Create custom site fields
        $this->createCustomFields($row->siteCustomFields, $site->ds_id, null, false);

        if ($copyNumberToCli) {
            $this->copyPrimaryNumberToCli($site->ds_id, $createdNumbers);
        }

        return $site->ds_id;
    }

    private function createAddress(ImportRowDTO $row): ?int
    {
        $country = $this->countryCache[strtoupper($row->country)] ?? null;

        if (!$country) {
            return null;
        }

        $location = Location::firstOrCreate([
            'location_value' => $row->city,
            'location_postcode' => $row->zip,
            'location_country_id' => $country->country_id,
        ]);

        $address = Address::firstOrCreate([
            'address_location_id' => $location->location_id,
            'address_value' => $row->street,
        ]);

        return $address->address_id;
    }

    private function createNumbers(ImportRowDTO $row, int $siteId, int $accountId): array
    {
        $numbers = $row->getNormalizedPhoneNumbers();
        $created = [];

        foreach ($numbers as $type => $number) {
            // Check if number already exists
            $existingNumber = Number::where('number_value', $number)->first();

            if ($existingNumber) {
                // Update to assign to this site
                $existingNumber->update([
                    'number_ds_id' => $siteId,
                    'number_account_id' => $accountId,
                ]);
            } else {
                // Create new number
                Number::create([
                    'number_ds_id' => $siteId,
                    'number_account_id' => $accountId,
                    'number_nt_id' => $this->numberTypeCache[$type]->nt_id,
                    'number_value' => $number,
                ]);
            }

            // Update cache
            $this->existingNumbersCache[$number] = $siteId;
            $created[strtolower($type)] = $number;
        }

        return $created;
    }

    private function createDevice(ImportRowDTO $row, int $siteId, int $accountId): void
    {
        $deviceModule = $this->moduleCache[$row->deviceModule];
        $protocol = $this->moduleCache[$row->siteModule];

        // Determine if identity/module are required
        $requiresIdentity = $this->profileAccess->isFieldRequired($protocol, 'identity') ||
                            $this->profileAccess->isFieldRequired($deviceModule, 'identity');

        $requiresModule = $this->profileAccess->isFieldRequired($protocol, 'module') ||
                          $this->profileAccess->isFieldRequired($deviceModule, 'module');

        // Create device
        // Note: For UI import, we don't populate set_xxx fields (setidentity, setmodule, setpin)
        // These are only used for FreeSwitch configuration changes
        $device = Device::create([
            'device_ds_id' => $siteId,
            'device_account_id' => $accountId,
            'device_module_id' => $deviceModule->module_id,
            'device_equipment' => $row->equipmentId,
            'device_identity' => $row->identity,
            'device_setidentity' => null, // Not populated for UI import
            'device_module' => $row->module ?? 0,
            'device_setmodule' => null, // Not populated for UI import
            'device_pin' => $row->pin,
            'device_setpin' => null, // Not populated for UI import
            'device_firmware' => $row->firmwareVersion,
            'device_enabled' => strtolower($row->deviceStatus) === 'enabled' ? 1 : 0,
        ]);

        // Create gateway if MAC or IMEI provided
        if (strtolower($row->deviceType) === 'gateway' && ($row->macAddress || $row->imeiNumber)) {
            $this->createGateway($row, $device->device_id, $accountId);
        }

        // Create custom device fields
        $this->createCustomFields($row->deviceCustomFields, null, $device->device_id, true);
    }

    private function createGateway(ImportRowDTO $row, int $deviceId, int $accountId): void
    {
        $mac = $row->macAddress ? strtolower(preg_replace('/[^0-9a-f]/i', '', $row->macAddress)) : null;
        $imei = $row->imeiNumber;

        if (!$mac && !$imei) {
            return;
        }

        $this->gatewayPersistence->findOrRestoreGateway($mac, $imei, $accountId, $deviceId);
    }

    private function createCustomFields(array $fields, ?int $siteId, ?int $deviceId, bool $isDevice): void
    {
        foreach ($fields as $fieldName => $value) {
            if (empty($value)) {
                continue;
            }

            $key = $fieldName . '_' . ($isDevice ? 'device' : 'site');
            $config = $this->customFieldConfigCache[$key] ?? null;

            if (!$config) {
                continue;
            }

            CustomFieldValue::create([
                'cfv_cfc_id' => $config->cfc_id,
                'cfv_device_id' => $deviceId,
                'cfv_ds_id' => $siteId,
                'cfv_value' => $value,
            ]);
        }
    }

    private function invalidateCaches(): void
    {
        GroupCache::forgetGroup('sites');
        GroupCache::forgetGroup('devices');
        GroupCache::forgetGroup('settings');
        GroupCache::forgetGroup('numbers');
        GroupCache::forgetGroup('gateways');
    }

    private function copyPrimaryNumberToCli(int $siteId, array $numbers): void
    {
        if (empty($numbers)) {
            return;
        }

        $priority = ['sip', 'sim', 'pbx', 'pstn'];
        $selected = null;

        foreach ($priority as $type) {
            if (!empty($numbers[$type])) {
                $selected = $numbers[$type];
                break;
            }
        }

        if (!$selected) {
            return;
        }

        $settingIds = $this->getCliSettingIds();
        if (empty($settingIds)) {
            return;
        }

        foreach ($settingIds as $settingId) {
            DeviceSiteSetting::updateOrCreate(
                [
                    'dss_ds_id' => $siteId,
                    'dss_setting_id' => $settingId,
                ],
                [
                    'dss_value' => $selected,
                ]
            );
        }

        GroupCache::forgetGroup('settings');
    }

    private function getCliSettingIds(): array
    {
        if (!empty($this->cliSettingIds)) {
            return $this->cliSettingIds;
        }

        $keys = [
            'call.alarm.route1.cli.number',
            'call.outbound.trunk.cli.number',
        ];

        $this->cliSettingIds = Setting::whereIn('setting_key', $keys)
            ->pluck('setting_id', 'setting_key')
            ->values()
            ->all();

        return $this->cliSettingIds;
    }

    private function parse(UploadedFile $file): array
    {
        $this->validateUpload($file);

        return strtolower($file->getClientOriginalExtension()) === 'csv'
            ? $this->parseCsv($file)
            : $this->parseExcel($file);
    }

    private function validateUpload(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new Exception(trans('Uploaded file is invalid'));
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception(trans('File size exceeds maximum allowed size of :size MB', [
                'size' => self::MAX_FILE_SIZE / 1024 / 1024,
            ]));
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new Exception(trans('Invalid file format. Allowed formats: :formats', [
                'formats' => implode(', ', self::ALLOWED_EXTENSIONS),
            ]));
        }

        $allowedMimes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new Exception(trans('Invalid file type'));
        }
    }

    private function parseCsv(UploadedFile $file): array
    {
        $rows = [];

        if (($handle = fopen($file->getRealPath(), 'r')) === false) {
            throw new Exception(trans('Failed to open CSV file'));
        }

        $headers = fgetcsv($handle, 0, ',');
        if ($headers === false || empty($headers)) {
            fclose($handle);
            throw new Exception(trans('CSV file is empty or has invalid headers'));
        }

        $headers = array_map(fn ($header) => trim(str_replace("\xEF\xBB\xBF", '', $header)), $headers);
        $rows[] = array_combine($headers, $headers);

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (count(array_filter($data)) === 0) {
                continue;
            }

            if (count($data) < count($headers)) {
                $data = array_pad($data, count($headers), '');
            } elseif (count($data) > count($headers)) {
                $data = array_slice($data, 0, count($headers));
            }

            $data = array_map(fn ($value) => mb_convert_encoding($value, 'UTF-8', 'auto'), $data);
            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);

        return $rows;
    }

    private function parseExcel(UploadedFile $file): array
    {
        try {
            $sheetRows = IOFactory::load($file->getRealPath())->getActiveSheet()->toArray(null, true, true, true);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            throw new Exception(trans('Failed to parse Excel file: :error', [
                'error' => $e->getMessage(),
            ]));
        }

        if (empty($sheetRows)) {
            throw new Exception(trans('Excel file is empty'));
        }

        $headers = array_map(fn ($header) => trim($header), array_filter(array_shift($sheetRows)));
        if (empty($headers)) {
            throw new Exception(trans('Excel file has invalid headers'));
        }

        $rows = [array_combine($headers, $headers)];

        foreach ($sheetRows as $rowData) {
            if (count(array_filter($rowData)) === 0) {
                continue;
            }

            $rowData = array_slice($rowData, 0, count($headers));
            if (count($rowData) < count($headers)) {
                $rowData = array_pad($rowData, count($headers), '');
            }

            $rows[] = array_combine($headers, array_map(
                fn ($value) => $value === null ? '' : trim((string) $value),
                $rowData
            ));
        }

        return $rows;
    }
}
