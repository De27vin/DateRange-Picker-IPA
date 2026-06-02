<?php

namespace App\Http\Livewire\Ucp;

use App\Jobs\ExportJob;
use App\Services\CustomFieldsService;
use Livewire\Component;
use App\Traits\TranslationsTrait;
use App\Traits\AccountsTrait;
use App\Traits\SearchFiltersTrait;
use App\Services\SearchDeviceService;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Device;

class ExportDevicesNew extends Component
{
    use TranslationsTrait, AccountsTrait, SearchFiltersTrait;

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
    public $exportFormat = 'csv';
    public $initialList;
    public string $exportComponentId;
    
    // Preset management properties
    public $presets = [];
    public $selectedPreset = null;
    public $showSavePreset = false;
    public $newPresetName = '';
    public $presetError = '';

    protected $listeners = ['doExportDevices'];
    
    protected $rules = [
        'newPresetName' => 'required|string|min:1|max:100|regex:/^[\p{L}\p{N}\s\-_&.,()]+$/u',
    ];
    
    protected function messages()
    {
        return [
            'newPresetName.required' => trans('Preset name is required'),
            'newPresetName.min' => trans('Preset name must be at least 1 character'),
            'newPresetName.max' => trans('Preset name cannot exceed 100 characters'),
            'newPresetName.regex' => trans('Preset name can contain letters, numbers, spaces, hyphens, underscores and basic punctuation'),
        ];
    }
    private SearchDeviceService $searchService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->searchService = new SearchDeviceService();
    }

    public function mount(string $filtersId, bool $exportSites = false)
    {
        $this->filtersId = $filtersId;
        $this->exportSites = $exportSites;
        $this->locale = session('locale', 'en');
        $this->initExportList();
        $this->loadPresets();
        $this->exportComponentId = (string) Str::uuid();
    }

    public function render()
    {
        return view('livewire.ucp.export-devices-new');
    }

    public function handleOnSortOrderChanged($sortOrder, $previousSortOrder, $name, $from, $to)
    {
        $this->$name = $sortOrder;
        
        // Check if current field configuration matches any existing preset
        if ($name === 'export_list' || $name === 'device_list') {
            $this->checkAndSelectMatchingPreset();
        }
    }

    private function prepareFields()
    {
        $fieldList = $this->getFieldTranslations($this->locale);
        unset($fieldList['numbers']);

        $customFieldService = new CustomFieldsService();
        [$customSiteFields, $customDeviceFields] = $this->getCustomFields($customFieldService);

        $this->siteFields = $this->getSiteFields($customSiteFields);
        $this->additionalFields = $this->getAdditionalFields($customDeviceFields);

        return array_merge($this->siteFields, $fieldList, $this->additionalFields);
    }

    private function getCustomFields($service)
    {
        $customFields = $service->getAccountCustomFieldsConfig(session('account.id'));
        $siteFields = [];
        $deviceFields = [];

        foreach ($customFields as $field) {
            $key = 'custom_' . $field['cfc_id'];
            if ($field['cfc_is_device']) {
                $deviceFields[$key] = $field['cfc_name'] . ' ('.trans('Device Custom Field').')';
            } else {
                $siteFields[$key] = $field['cfc_name'] . ' ('.trans('Site Custom Field').')';
            }
        }

        return [$siteFields, $deviceFields];
    }

    private function getSiteFields($customFields)
    {
        return array_merge([
            'site_name' => trans('Installation Name'),
            'site_module_name' => trans('Site module type'),
            'mac_address' => trans('Mac Address'),
            'imei_number' => trans('Imei number'),
        ], $customFields);
    }

    private function getAdditionalFields($customFields)
    {
        return array_merge([
            'device_module_type' => trans('device type'),
            'device_module_name' => trans('device module type'),
            'device_firmware'     => trans('Firmware Version'),
            'device_enabled'      => trans('Device Status'),
            'device_created' => trans('device_created'),
            'device_deleted' => trans('Deleted at'),
            'device_lastset' => trans('Last set date'),
            'device_lastrevival' => trans('Last revival date'),
            'device_lastreported' => trans('Last reported date'),
            'device_lasttech' => trans('Last tech date'),
            'device_lastalarm' => trans('Last active alarm'),
            'active_warnings' => trans('Active Warnings'),
            'active_errors' => trans('Active Errors'),
            'overdue' => trans('Overdue')
        ], $customFields);
    }

    public function initExportList()
    {
        $this->alertTranslations = $this->getAlertTranslations($this->locale);
        $this->initialList = $this->prepareFields();
        $this->csvHeaderLabels = array_merge(
            ['site_id' => trans('Installation ID'), 'device_id' => trans('Device ID')],
            $this->initialList
        );
        $this->device_list = array_keys($this->initialList);
        $this->export_list = [];
        $this->lockedFields = ['Installation ID', 'Device ID'];
    }

    public function doExportDevices($format = null, $delivery = 'browser')
    {
        if ($format) {
            $this->exportFormat = $format;
        }

        $downloadId = (string) Str::uuid();
        $locale     = $this->locale ?? session('locale', 'en');

        $params = [
            'filters'     => $this->getDeviceSearchFilter($this->filtersId),
            'exportList'  => $this->export_list,
            'exportSites' => $this->exportSites,
            'locale'      => $locale,
            'accountId'   => session('account.id'),
        ];

        $this->dispatchBrowserEvent('start-export', [
            'type'    => 'devices',
            'component_id' => $this->exportComponentId,
            'request' => [
                'type'        => 'devices',
                'format'      => $this->exportFormat,
                'delivery'    => $delivery,
                'params'      => $params,
                'download_id' => $downloadId,
                'component_id' => $this->exportComponentId,
            ],
        ]);
    }

    public function moveAllDeviceFields()
    {
        // MERGE: Add all device_list fields to export_list (preserve existing fields)
        $this->export_list = array_unique(array_merge($this->export_list, $this->device_list));
        $this->device_list = [];
        
        // Check if this configuration matches any preset
        $this->checkAndSelectMatchingPreset();
    }

    public function resetExportList()
    {
        $this->initExportList();
        $this->selectedPreset = null;
        $this->presetError = '';
        $this->showSavePreset = false;
        $this->newPresetName = '';
    }

    // Legacy note kept for context; exports now handled by ExportJob via ExportController
    
    /**
     * Load all presets for current account from profile data
     */
    public function loadPresets()
    {
        try {
            $profileData = $this->getProfileData();
            $this->presets = $profileData['export']['devices']['presets'] ?? [];
        } catch (\Throwable $e) {
            // If presets fail to load, just use empty array
            // Core export functionality remains unaffected
            $this->presets = [];
            \Log::warning('Failed to load export presets', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Load fields from selected preset
     */
    public function loadPreset($presetId)
    {
        try {
            // Handle empty selection (reset to default)
            if (empty($presetId)) {
                $this->selectedPreset = null;
                $this->presetError = '';
                return;
            }
            
            // Validate preset exists
            if (!isset($this->presets[$presetId])) {
                $this->presetError = 'Preset not found';
                $this->selectedPreset = null;
                return;
            }
            
            $preset = $this->presets[$presetId];
            
            // Validate preset structure
            if (!isset($preset['fields']) || !is_array($preset['fields'])) {
                $this->presetError = 'Invalid preset data';
                $this->selectedPreset = null;
                return;
            }
            
            // Validate fields against available fields
            $validFields = $this->validatePresetFields($preset['fields']);
            if ($validFields === false) {
                $this->presetError = 'Preset contains invalid fields';
                $this->selectedPreset = null;
                return;
            }
            
            // Apply the preset
            $this->export_list = $validFields;
            $this->device_list = array_diff(array_keys($this->initialList), $validFields);
            $this->selectedPreset = $presetId;
            $this->presetError = '';
            
            // Close save preset form if it was open
            $this->showSavePreset = false;
            $this->newPresetName = '';
            
        } catch (\Throwable $e) {
            // Never break the component, just show error
            $this->presetError = 'Failed to load preset';
            $this->selectedPreset = null;
            \Log::error('Failed to load preset', ['preset_id' => $presetId, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Save current field selection as new preset
     */
    public function savePreset()
    {
        try {
            // Check if user has admin or site role
            if (!auth()->user()->isAdmin) {
                $this->presetError = 'You do not have permission to save presets';
                return;
            }
            
            // Validate preset name
            $this->validate();
            
            // Check for duplicate names
            foreach ($this->presets as $preset) {
                if (isset($preset['name']) && trim(strtolower($preset['name'])) === trim(strtolower($this->newPresetName))) {
                    $this->presetError = 'Preset with this name already exists';
                    return;
                }
            }
            
            // Validate current export list
            $validFields = $this->validatePresetFields($this->export_list);
            if ($validFields === false || empty($validFields)) {
                $this->presetError = 'Cannot save preset with invalid or empty field selection';
                return;
            }
            
            // Generate unique ID
            $presetId = 'preset_' . uniqid() . '_' . time();
            
            // Create new preset
            $newPreset = [
                'name' => trim($this->newPresetName),
                'fields' => $validFields,
                'created_at' => now()->toISOString()
            ];
            
            // Load current profile data safely
            $profileData = $this->getProfileData();
            if (!$profileData) {
                $this->presetError = 'Failed to access profile data';
                return;
            }
            
            // Ensure export.devices.presets structure exists
            if (!isset($profileData['export'])) {
                $profileData['export'] = [];
            }
            if (!isset($profileData['export']['devices'])) {
                $profileData['export']['devices'] = [];
            }
            if (!isset($profileData['export']['devices']['presets'])) {
                $profileData['export']['devices']['presets'] = [];
            }
            
            // Add new preset
            $profileData['export']['devices']['presets'][$presetId] = $newPreset;
            
            // Save with validation
            $this->saveProfileData($profileData);
            
            // Update local state
            $this->presets[$presetId] = $newPreset;
            $this->selectedPreset = $presetId;
            $this->showSavePreset = false;
            $this->newPresetName = '';
            $this->presetError = '';
            
            session()->flash('message', 'Preset saved successfully');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let validation errors bubble up normally
            throw $e;
        } catch (\Throwable $e) {
            // Never break the component
            $this->presetError = 'Failed to save preset';
            \Log::error('Failed to save preset', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Delete a preset with confirmation
     */
    public function deletePreset($presetId)
    {
        try {
            // Check if user has admin or site role
            if (!auth()->user()->isAdmin) {
                $this->presetError = 'You do not have permission to delete presets';
                return;
            }
            
            // Validate preset exists
            if (!isset($this->presets[$presetId])) {
                $this->presetError = 'Preset not found';
                return;
            }
            
            // Load current profile data safely
            $profileData = $this->getProfileData();
            if (!$profileData) {
                $this->presetError = 'Failed to access profile data';
                return;
            }
            
            // Remove preset
            if (isset($profileData['export']['devices']['presets'][$presetId])) {
                unset($profileData['export']['devices']['presets'][$presetId]);
            }
            
            // Save with validation
            $this->saveProfileData($profileData);
            
            // Update local state
            unset($this->presets[$presetId]);
            
            // Clear selection and reset state if deleted preset was selected
            if ($this->selectedPreset === $presetId) {
                $this->selectedPreset = null;
                $this->showSavePreset = false;
                $this->newPresetName = '';
            }
            
            $this->presetError = '';
            
            session()->flash('message', 'Preset deleted successfully');
            
        } catch (\Throwable $e) {
            // Never break the component
            $this->presetError = 'Failed to delete preset';
            \Log::error('Failed to delete preset', ['preset_id' => $presetId, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Show save preset form
     */
    public function showSavePresetForm()
    {
        // Check if user has admin or site role
        if (!auth()->user()->isAdmin) {
            $this->presetError = 'You do not have permission to save presets';
            return;
        }
        
        // Only allow saving if there are fields selected
        if (empty($this->export_list)) {
            $this->presetError = 'Select some fields before saving a preset';
            return;
        }
        
        $this->showSavePreset = true;
        $this->newPresetName = '';
        $this->presetError = '';
    }
    
    /**
     * Cancel save preset operation
     */
    public function cancelSavePreset()
    {
        $this->showSavePreset = false;
        $this->newPresetName = '';
        $this->presetError = '';
    }
    
    /**
     * Clear preset selection (keep current field selection)
     */
    public function clearPresetSelection()
    {
        $this->selectedPreset = null;
        $this->presetError = '';
    }
    
    /**
     * Validate preset fields against available fields
     * 
     * @param array $fields
     * @return array|false Valid fields array or false if invalid
     */
    private function validatePresetFields($fields)
    {
        try {
            if (!is_array($fields)) {
                return false;
            }
            
            $availableFields = array_keys($this->initialList);
            $validFields = [];
            
            foreach ($fields as $field) {
                // Only allow valid field names
                if (is_string($field) && in_array($field, $availableFields, true)) {
                    $validFields[] = $field;
                }
            }
            
            return $validFields;
            
        } catch (\Throwable $e) {
            \Log::warning('Preset field validation failed', ['fields' => $fields, 'error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Get all presets formatted for dropdown
     */
    public function getPresetsForDropdown()
    {
        $options = [];
        foreach ($this->presets as $id => $preset) {
            if (isset($preset['name'])) {
                $options[$id] = $preset['name'];
            }
        }
        return $options;
    }
    
    /**
     * Check if current field configuration matches any existing preset and auto-select it
     */
    public function checkAndSelectMatchingPreset()
    {
        // Reset preset selection first
        $this->selectedPreset = null;
        $this->presetError = '';
        $this->showSavePreset = false;
        
        if (empty($this->export_list)) {
            return;
        }
        
        // Sort current fields for comparison
        $currentFields = array_values($this->export_list);
        sort($currentFields);
        
        foreach ($this->presets as $presetId => $preset) {
            if (isset($preset['fields']) && is_array($preset['fields'])) {
                // Sort preset fields for comparison
                $presetFields = array_values($preset['fields']);
                sort($presetFields);
                
                if ($currentFields === $presetFields) {
                    // Found matching preset - auto-select it
                    $this->selectedPreset = $presetId;
                    return;
                }
            }
        }
    }

    /**
     * Check if current field selection matches any existing preset (for UI logic)
     * Using computed property to ensure proper reactivity
     */
    public function getCanSavePresetProperty()
    {
        // Can save if user has permission, there are fields selected, not showing save form, and current selection doesn't match any preset
        return auth()->user()->isAdmin &&
               !empty($this->export_list) && 
               !$this->showSavePreset && 
               !$this->getCurrentSelectionMatchesPreset();
    }
    
    /**
     * Check if should show delete button for selected preset
     */
    public function getCanDeletePresetProperty()
    {
        // Show delete button if user has permission and there's a selected preset
        // This allows deletion of desynchronized presets caused by field name changes
        return auth()->user()->isAdmin &&
               !empty($this->selectedPreset);
    }
    
    /**
     * Check if current field selection matches any existing preset (for UI logic)
     */
    public function getCurrentSelectionMatchesPreset()
    {
        if (empty($this->export_list)) {
            return false;
        }
        
        $currentFields = array_values($this->export_list);
        sort($currentFields);
        
        foreach ($this->presets as $id => $preset) {
            if (isset($preset['fields']) && is_array($preset['fields'])) {
                $presetFields = array_values($preset['fields']);
                sort($presetFields);
                
                if ($currentFields === $presetFields) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Force component state refresh to fix reactivity issues
     */
    public function refreshComponentState()
    {
        // This method exists to be called via $refresh or wire:key changes
        // Forces Livewire to re-evaluate all computed properties and template logic
        $this->checkAndSelectMatchingPreset();
    }
}
