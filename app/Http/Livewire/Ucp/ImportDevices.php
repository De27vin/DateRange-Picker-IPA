<?php

namespace App\Http\Livewire\Ucp;

use App\Services\RolesService;
use App\Traits\AccountsTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportDevices extends Component
{
    use WithFileUploads;
    use AccountsTrait;
    use TranslationsTrait;

    private RolesService $rolesService;

    public $importFile;
    public $validationResult = null;
    public $isValidating = false;
    public $isExecuting = false;
    public $importCompleted = false;
    public $importStats = null;
    public $currentStep = 'upload'; // upload, validated, executing, completed
    public $showInstructions = true;
    public $locale;
    public $copyNumberToCli = true;

    protected $listeners = ['fileValidated', 'importExecuted'];

    public function boot(RolesService $rolesService)
    {
        $this->rolesService = $rolesService;
    }

    public function mount()
    {
        $this->checkPermission();
        $this->locale = session('locale', 'default');
    }

    private function checkPermission()
    {
        if (!$this->rolesService->canUserImport(Auth::user())) {
            abort(403, trans('You do not have permission to access import functionality'));
        }
    }

    public function render()
    {
        return view('livewire.ucp.import-devices', [
            'validationRules' => $this->getValidationRules(),
        ]);
    }

    /**
     * Auto-validate when file is uploaded
     * This hook is called automatically by Livewire when importFile property changes
     */
    public function updatedImportFile()
    {
        // Reset previous validation results
        $this->validationResult = null;
        $this->currentStep = 'upload';

        // Automatically start validation
        if ($this->importFile) {
            $this->validateFile();
        }
    }

    /**
     * Reset import state
     */
    public function resetImport()
    {
        $this->importFile = null;
        $this->validationResult = null;
        $this->isValidating = false;
        $this->isExecuting = false;
        $this->importCompleted = false;
        $this->importStats = null;
        $this->currentStep = 'upload';
    }

    /**
     * Toggle instructions panel
     */
    public function toggleInstructions()
    {
        $this->showInstructions = !$this->showInstructions;
    }

    /**
     * Get validation rules for display
     */
    private function getValidationRules(): array
    {
        return [
            trans('File format: CSV or XLSX'),
            trans('Maximum file size: 2 MB'),
            trans('Maximum rows: 500 devices'),
            trans('Required columns: sitemodule, EquipmentID, devicetype, module, DeviceStatus'),
            trans('Equipment IDs must be unique globally'),
            trans('Phone numbers must be unique (not assigned to other sites)'),
            trans('At least one phone number required per site (if protocol requires)'),
            trans('PIN must be unique per site and device module'),
            trans('Identity must be unique per Identity + Module combination'),
            trans('All address fields must be filled together (or all empty)'),
            trans('Device module must be compatible with site protocol'),
            trans('MAC addresses must be 12 hex digits (for gateways)'),
            trans('IMEI must be 15 digits with valid checksum (for gateways)'),
            trans('Custom fields must exist in account configuration'),
        ];
    }

    /**
     * Handle file validation completion (called from JavaScript)
     */
    public function fileValidated($result)
    {
        \Log::info('[IMPORT] fileValidated() called', [
            'valid' => $result['valid'] ?? null,
            'errors_count' => isset($result['errors']) ? count($result['errors']) : 0
        ]);

        $this->validationResult = $result;
        $this->isValidating = false;

        // Always show validation results (errors or success)
        $this->currentStep = 'validated';
    }

    /**
     * Handle import execution completion (called from JavaScript)
     */
    public function importExecuted($result)
    {
        $this->isExecuting = false;

        if ($result['success']) {
            $this->importCompleted = true;
            $this->importStats = $result['created'];
            $this->currentStep = 'completed';

            // Emit event to refresh equipment list
            $this->emit('refreshEquipmentList');
        }
    }

    /**
     * Download template
     */
    public function downloadTemplate($format = 'xlsx')
    {
        $url = route('import.devices.template', ['format' => $format]);
        $this->dispatchBrowserEvent('download-template', ['url' => $url]);
    }

    /**
     * Download instructions
     */
    public function downloadInstructions()
    {
        $url = route('import.devices.instructions');
        $this->dispatchBrowserEvent('download-template', ['url' => $url]);
    }

    /**
     * Validate uploaded file
     */
    public function validateFile()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,xlsx,xls|max:2048', // 2MB
        ]);

        $this->isValidating = true;

        try {
            $accountId = session('account.id');

            // File is already uploaded by Livewire to livewire-tmp
            // Create UploadedFile instance from Livewire temporary file
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $this->importFile->getRealPath(),
                $this->importFile->getClientOriginalName(),
                $this->importFile->getClientMimeType(),
                null,
                true
            );

            // Call validation service
            $importService = app(\App\Services\Import\ImportService::class);
            $result = $importService->validateFile($uploadedFile, $accountId);

            // Store result
            $this->validationResult = $result->toArray();
            $this->isValidating = false;
            $this->currentStep = 'validated';

            // Store temp file path in session for execute
            session(['import_livewire_file' => $this->importFile->getRealPath()]);

            \Log::info('[IMPORT] Validation completed', [
                'valid' => $result->isValid(),
                'errors_count' => count($result->getErrors())
            ]);

        } catch (\Exception $e) {
            $this->isValidating = false;
            $this->addError('general', $e->getMessage());

            \Log::error('[IMPORT] Validation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Execute import
     */
    public function executeImport()
    {
        if (!$this->validationResult || !$this->validationResult['valid']) {
            $this->addError('general', trans('Please validate file first'));
            return;
        }

        $this->isExecuting = true;
        $this->currentStep = 'executing';

        try {
            $accountId = session('account.id');

            // Get file path from session
            $filePath = session('import_livewire_file');

            if (!$filePath || !file_exists($filePath)) {
                throw new \Exception(trans('Temporary file not found. Please upload and validate file again.'));
            }

            // Create UploadedFile instance
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $filePath,
                basename($filePath),
                mime_content_type($filePath),
                null,
                true
            );

            // Execute import
            $importService = app(\App\Services\Import\ImportService::class);
            $result = $importService->execute($uploadedFile, $accountId, $this->copyNumberToCli);

            $this->isExecuting = false;

            if ($result['success']) {
                $this->importCompleted = true;
                $this->importStats = $result['created'];
                $this->currentStep = 'completed';

                // Emit event to refresh equipment list
                $this->emit('refreshEquipmentList');

                \Log::info('[IMPORT] Import completed', [
                    'sites' => $result['created']['sites'] ?? 0,
                    'devices' => $result['created']['devices'] ?? 0
                ]);
            } else {
                $this->addError('general', $result['message'] ?? trans('Import failed'));
            }

            // Clean up session
            session()->forget('import_livewire_file');

        } catch (\Exception $e) {
            $this->isExecuting = false;
            $this->currentStep = 'validated';
            $this->addError('general', $e->getMessage());

            \Log::error('[IMPORT] Import execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
