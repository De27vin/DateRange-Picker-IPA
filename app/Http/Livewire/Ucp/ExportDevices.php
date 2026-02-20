<?php

namespace App\Http\Livewire\Ucp;

use App\Services\CustomFieldsService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Traits\TranslationsTrait;
use App\Traits\AccountsTrait;
use App\Traits\SearchFiltersTrait;
use Illuminate\Support\Arr;
use App\Services\SearchDeviceService;
use Illuminate\Support\Str;

/**
 * @deprecated
 */
class ExportDevices extends Component
{
    use TranslationsTrait;
    use AccountsTrait;
    use SearchFiltersTrait;

    public $csvHeaderLabels;
    public $device_list;
    public $export_list;
    public $lockedFields;
    public $locale;
    public $columnTitles;
    public $filtersId;
    public bool $exportSites = false;
    public $siteFields;
    public $additionalFields;
    public $deviceAlerts;
    public $alertTranslations;

    protected $listeners = [
        'doExportDevices',
    ];

    private SearchDeviceService $searchService;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->searchService = new SearchDeviceService();
    }

    public function mount(string $filtersId, bool $exportSites = false)
    {
        $this->filtersId = $filtersId;
        $this->exportSites = $exportSites;
        $this->locale = session('locale', 'en');
        $this->initExportList();
    }

    public function render()
    {
        return view('livewire.ucp.export-devices');
    }

    public function handleOnSortOrderChanged($sortOrder, $previousSortOrder, $name, $from, $to)
    {
        $this->$name = $sortOrder;
    }

    public function initExportList()
    {
    $this->alertTranslations = $this->getAlertTranslations($this->locale);
    $fieldList = $this->getFieldTranslations($this->locale);
    unset($fieldList['numbers']);

    // Add custom fields - start
    $customFieldService = new CustomFieldsService();
    $customFields = $customFieldService->getAccountCustomFieldsConfig(session('account.id'));
    $customSiteFields = [];
    $customDeviceFields = [];
    foreach ($customFields as $field) {
        $key = 'custom_' . $field['cfc_id'];
        if ($field['cfc_is_device']) {
            $customDeviceFields[$key] = $field['cfc_name'] . ' ('.trans('Device Custom Field').')';
        } else {
            $customSiteFields[$key] = $field['cfc_name'] . ' ('.trans('Site Custom Field').')';
        }
    }
    // Add custom fields - end

    $this->siteFields = array_merge([
        'site_name' => trans('Installation Name'),
        'site_module_name' => trans('Site module type'),
        'mac_address' => trans('Mac Address'),
        'imei_number' => trans('Imei number'),
    ], $customSiteFields);

    $this->additionalFields = array_merge([
        'device_module_type'  => trans('device type'),
        'device_module_name'  => trans('device module type'),
        'device_created'      => trans('device_created'),
        'device_deleted'      => trans('Deleted at'),
        'device_lastset'      => trans('Last set date'),
        'device_lastrevival'  => trans('Last revival date'),
        'device_lastreported' => trans('Last reported date'),
        'device_lastalarm'    => trans('Last active alarm'),
        'active_warnings'     => trans('Active Warnings'),
        'active_errors'       => trans('Active Errors'),
        'overdue'            => trans('Overdue')
    ], $customDeviceFields);

        $device_list = array_merge($this->siteFields, $fieldList, $this->additionalFields);
        $this->initialList = $device_list;

        $this->csvHeaderLabels = $device_list;
        $idList = [
            'site_id' => trans('Installation ID'),
            'device_id' => trans('Device ID')
        ];
        $this->deviceAlerts = $this->updateDeviceAlerts()->toArray();
        $this->csvHeaderLabels = array_merge($idList,$this->csvHeaderLabels);
        $this->device_list = array_keys($device_list);
        $this->export_list = [];
        $this->lockedFields = ['Installation ID', 'Device ID'];
    }

    public function getCsvHeader()
    {
        $fieldsToExport = Arr::prepend($this->export_list, 'site_id');
        $fieldsToExport = Arr::prepend($fieldsToExport, 'device_id');
        $headerLabels = array_intersect_key($this->csvHeaderLabels, array_flip($fieldsToExport));
        return $headerLabels;
    }

    public function doExportDevices()
    {
        try {
            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '500M');

            // Create progress file
            $progressFile = storage_path('framework/cache/export_devices_' . auth()->id() . '.txt');
            file_put_contents($progressFile, '0');

            return response()->streamDownload(function() use ($progressFile) {
                $file = fopen('php://output', 'a');
                // add UTF-8 BOM
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                $header = $this->getCsvHeader();
                $outputOptions = [
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'escape_char' => '\\',
                    'eol' => "\n"
                ];

                // Force quotes around all header fields
                $quotedHeader = array_map(function($field) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }, $header);

                // Write the header line manually to ensure proper quoting
                fwrite($file, implode(',', $quotedHeader) . "\n");

                $activeFilters = $this->getDeviceSearchFilter($this->filtersId);

                if ($this->exportSites) {
                    $deviceSites = $this->searchService->searchDeviceSites($activeFilters);

                    $deviceSites->load([
                        'devices',
                        'devices.gateway',
                        'devices.custom_fields',
                        'devices.module.funktions',
                        'devices.module.module_type',
                        'module',
                        'module.module_type',
                        'numbers',
                        'address',
                        'address.location',
                        'custom_fields',
                        'labels',
                    ]);

                    $totalRecords = $deviceSites->sum(function($site) {
                        return $site->devices->count();
                    });
                    $processed = 0;

                    while ($deviceSite = $deviceSites->shift()) {
                        if ($deviceSite->devices->count() === 0) {
                            continue;
                        }

                        while ($device = $deviceSite->devices->shift()) {
                            if (empty($activeFilters['search_tabs']) || in_array('all', $activeFilters['search_tabs']) ||
                                ($device->device_enabled && in_array('enabled', $activeFilters['search_tabs'])) ||
                                (!$device->device_enabled && in_array('disabled', $activeFilters['search_tabs']))) {

                                fputcsv($file, $this->generateCsvRow($header, $device),
                                    $outputOptions['delimiter'],
                                    $outputOptions['enclosure'],
                                    $outputOptions['escape_char'],
                                    $outputOptions['eol'],
                                );

                                $processed++;
                                file_put_contents($progressFile, round(($processed / $totalRecords) * 100));
                            }
                        }
                    }
                } else {
                    $devices = $this->searchService->searchDevices($activeFilters);

                    $devices->load([
                        'module',
                        'module.module_type',
                        'device_site',
                        'device_site.address',
                        'custom_fields',
                    ]);

                    $totalRecords = $devices->count();
                    $processed = 0;

                    while ($device = $devices->shift()) {
                        if (empty($activeFilters['search_tabs']) || in_array('all', $activeFilters['search_tabs']) ||
                            ($device->device_enabled && in_array('enabled', $activeFilters['search_tabs'])) ||
                            (!$device->device_enabled && in_array('disabled', $activeFilters['search_tabs']))) {

                            // Use fputcsv with explicit options for consistent quoting
                            fputcsv($file, $this->generateCsvRow($header, $device),
                                $outputOptions['delimiter'],
                                $outputOptions['enclosure'],
                                $outputOptions['escape_char']
                            );

                            $processed++;
                            file_put_contents($progressFile, round(($processed / $totalRecords) * 100));
                        }
                    }
                }

                file_put_contents($progressFile, '100');
                fclose($file);

                // Clean up after delay
                register_shutdown_function(function() use ($progressFile) {
                    sleep(2);
                    if (file_exists($progressFile)) {
                        unlink($progressFile);
                    }
                });

            }, 'devices_' . date('d-m-Y') . '.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (\Exception $e) {
            $progressFile = storage_path('framework/cache/export_devices_' . auth()->id() . '.txt');
            if (file_exists($progressFile)) {
                unlink($progressFile);
            }
            throw $e;
        }
    }

    private function generateCsvRow($header, $device)
    {
        $row = [];
        $index = 0;
        foreach ($header as $key => $value) {
            // Handle custom fields
            if (str_starts_with($key, 'custom_')) {
                $customFieldId = (int) substr($key, 7); // Remove 'custom_' prefix
                $customValue = '';
                // For device custom fields
                if ($device->custom_fields) {
                    if ($fieldValue = $device->custom_fields->where('cfv_cfc_id', $customFieldId)->first()) {
                        $customValue = $fieldValue->cfv_value;
                    }
                }
                // For site custom fields
                if (empty($customValue) && $device->device_site?->custom_fields) {
                    if ($fieldValue = $device->device_site->custom_fields->where('cfv_cfc_id', $customFieldId)->first()) {
                        $customValue = $fieldValue->cfv_value;
                    }
                }
                $row[$index] = $customValue;
            }
            // site fields
            elseif (in_array($key, array_keys($this->siteFields))) {
                if ($key == 'site_name') {
                    $row[$index] = $device['device_site']['ds_name'];
                }
                if ($key == 'site_module_name') {
                    $row[$index] = $device['device_site']['module']['module_desc'] ?? $device['device_site']['module']['module_name'] ?? '';
                }
                if ($key == 'mac_address') {
                    $row[$index] = $device['device_site']['gateway']['dg_mac'] ?? '';
                }
                if ($key == 'imei_number') {
                    $row[$index] = $device['device_site']['gateway']['dg_imei'] ?? '';
                }
            // additional fields (device)
            } elseif (in_array($key, array_keys($this->additionalFields))) {
                if ($key === 'device_lastalarm') {
                    $lastAlarm = $device->getLastActiveAlarm();
                    $row[$index] = $lastAlarm ? toUserDateTime($lastAlarm) : null;
                } elseif (Str::contains($key,'device_')) {
                    $row[$index] =  !empty($device[$key]) ? toUserDateTime($device[$key]) : '' ;
                } elseif ($key == 'active_warnings') {
                    $rowItem = [];
                    foreach ($device['warnings'] as $warning) {
                        $rowItem[] = $this->alertTranslations[$warning['alert_type']['at_type']];
                        if($warning['alert_type']['at_type'] == 'PERIODICAL'){
                            $overdueCell = toUserDate($warning['da_timestamp']);
                        }
                    }
                    $row[$index] = implode(' | ', $rowItem);
                } elseif($key == 'active_errors') {
                    $rowItem = [];
                    foreach ($device['errors'] as $errorItem) {
                        $rowItem[] = $this->alertTranslations[$errorItem['alert_type']['at_type']];
                    }
                    $row[$index] = implode(' | ', $rowItem);
                } elseif($key == 'overdue') {
                    foreach ($device['warnings'] as $warning) {
                        if($warning['alert_type']['at_type'] == 'PERIODICAL'){
                            $row[$index] = toUserDateTime($warning['da_timestamp']);
                        } else {
                            $row[$index] = '';
                        }
                    }
                } else {
                    if (array_key_exists($device['device_id'],$this->deviceAlerts)) {
                        $deviceAlerts = $this->deviceAlerts[$device['device_id']];
                        $row[$index] = '';
                        // $row[$index] = $deviceAlerts[$key];
                    } else {
                        $row[$index] = '';
                    }
                }

                // DEVICE TYPE
                if ($key == 'device_module_type') {
                    $row[$index] = $device->module?->module_type?->mt_type ?? $device->module?->module_type?->mt_desc ?? '';
                }
                // DEVICE MODULE
                if ($key == 'device_module_name') {
                    $row[$index] = $device->module?->module_desc ?? $device->module?->module_name ?? '';
                }

            // all other fields not specified before
            } elseif (in_array($key, ['pbx','pstn','sim','sip'])) {
                $row[$index] = $device['device_site'][$key]['number_value'] ?? '';
            } elseif ($key == 'address') {
                $row[$index] = !empty($device['device_site']['address']) ? $device['device_site']['address']['in_one_line'] : '';
            } elseif ($key == 'site_id') {
                $row[$index] = $device['device_site']['ds_id'];
            } elseif ($key == 'device_id') {
                $row[$index] = $device['device_id'];
            } elseif ($key == 'comments') {
                $row[$index] = $device['latest_comment']['dc_text'] ?? '';
            } elseif ($key == 'labels') {
                $rowItem = [];
                foreach ($device['device_labels'] as $labelItem) {
                    $rowItem[] = $labelItem['dl_name'];
                }
                $row[$index] = implode(' | ', $rowItem);
            } else {
                $row[$index] = $device['device_'.$key];
            }


            $index = $index+1;
        }
        return $row;
    }


    public function moveAllDeviceFields()
    {
        $deviceList = $this->device_list;
        foreach ($deviceList as $listItem) {
            unset($this->device_list[$listItem]);
            array_push($this->export_list,$listItem);
        }
        $this->device_list = [];
    }

    public function resetExportList()
    {
        $this->initExportList();
    }
}
