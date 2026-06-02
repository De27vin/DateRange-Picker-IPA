<?php

namespace App\Http\Controllers;

use App\Services\Import\ImportService;
use App\Services\Import\ImportTemplateService;
use App\Services\RolesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportDevicesController extends Controller
{
    private ImportService $importService;
    private ImportTemplateService $templateService;
    private RolesService $rolesService;

    public function __construct(
        ImportService $importService,
        ImportTemplateService $templateService,
        RolesService $rolesService
    ) {
        $this->importService = $importService;
        $this->templateService = $templateService;
        $this->rolesService = $rolesService;
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
                'message' => trans('You do not have permission to access import functionality')
            ];
        }

        return null;
    }

    /**
     * Validate import file
     *
     * POST /import-devices-validate
     */
    public function validateImportFile(Request $request)
    {
        if ($error = $this->checkImportPermission()) {
            return response()->json($error, 403);
        }

        set_time_limit(120);
        ini_set('memory_limit', '512M');

        try {
            // Validate request
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB
            ]);

            $file = $request->file('file');
            $accountId = session('account.id');

            if (!$accountId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Account not found in session')
                ], 401);
            }

            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0700, true);
            }

            $tempPath = 'temp/import_' . uniqid() . '_' . $file->getClientOriginalName();
            $file->storeAs('', $tempPath);
            chmod(storage_path('app/' . $tempPath), 0600);

            // Save temp file path in session
            session(['import_temp_file' => $tempPath]);

            // Validate file
            $storedFile = storage_path('app/' . $tempPath);
            $result = $this->importService->validateFile(new \Illuminate\Http\UploadedFile(
                $storedFile,
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                null,
                true
            ), $accountId);

            Log::info('[IMPORT] Sending validation response', [
                'errors' => count($result->getErrors()),
                'valid' => $result->isValid(),
                'temp_file' => $tempPath
            ]);

            return response()->json([
                'success' => true,
                'validation' => $result->toArray()
            ]);

        } catch (Exception $e) {
            Log::error('Import validation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Execute import
     *
     * POST /import-devices-execute
     */
    public function execute(Request $request)
    {
        if ($error = $this->checkImportPermission()) {
            return response()->json($error, 403);
        }

        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        try {
            $accountId = session('account.id');

            if (!$accountId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Account not found in session')
                ], 401);
            }

            // Get temp file from session
            $tempPath = session('import_temp_file');

            if (!$tempPath) {
                return response()->json([
                    'success' => false,
                    'message' => trans('No validated file found. Please validate file first.')
                ], 400);
            }

            $storedFile = storage_path('app/' . $tempPath);

            if (!file_exists($storedFile)) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Temporary file not found. Please upload and validate file again.')
                ], 400);
            }

            // Create UploadedFile instance from stored file
            $file = new \Illuminate\Http\UploadedFile(
                $storedFile,
                basename($tempPath),
                mime_content_type($storedFile),
                null,
                true
            );

            Log::info('[IMPORT] Starting import execution', [
                'account_id' => $accountId,
                'temp_file' => $tempPath
            ]);

            $copyCli = $request->boolean('copy_cli', true);

            // Execute import
            $result = $this->importService->execute($file, $accountId, $copyCli);

            Log::info('[IMPORT] Import execution completed', [
                'success' => $result['success'] ?? false,
                'imported_sites' => $result['summary']['importedSites'] ?? 0,
                'imported_devices' => $result['summary']['importedDevices'] ?? 0
            ]);

            // Clean up temp file
            if (file_exists($storedFile)) {
                @unlink($storedFile);
            }
            session()->forget('import_temp_file');

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('Import execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Download import template
     *
     * GET /import-devices-template?format=csv|xlsx
     */
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
                    'message' => trans('Account not found in session')
                ], 401);
            }

            if (!in_array($format, ['csv', 'xlsx'])) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Invalid format. Allowed: csv, xlsx')
                ], 400);
            }

            // Generate template
            $file = $this->templateService->generateTemplate($accountId, $format);

            $filename = 'import_template.' . $format;

            if ($format === 'csv') {
                return response()->download($file, $filename, [
                    'Content-Type' => 'text/csv',
                ])->deleteFileAfterSend();
            } else {
                return response()->download($file, $filename, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])->deleteFileAfterSend();
            }

        } catch (Exception $e) {
            Log::error('Template download failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Download import instructions
     *
     * GET /import-devices-instructions
     */
    public function downloadInstructions(Request $request)
    {
        if ($error = $this->checkImportPermission()) {
            return response()->json($error, 403);
        }

        try {
            $locale = session('locale', 'en');

            // Try to get localized instructions file, fallback to English
            $instructionsPath = resource_path("import/import_instructions_{$locale}.txt");
            if (!file_exists($instructionsPath)) {
                $instructionsPath = resource_path('import/import_instructions_en.txt');
            }

            if (!file_exists($instructionsPath)) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Instructions file not found')
                ], 404);
            }

            // Load static instructions content
            $content = file_get_contents($instructionsPath);

            // Generate modules section from database
            $modulesSection = $this->generateModulesSection();

            // Replace placeholder with generated modules
            $content = str_replace('{MODULES_SECTION}', $modulesSection, $content);

            // Create temporary file with combined content
            $tempFile = tempnam(sys_get_temp_dir(), 'import_instructions_');
            file_put_contents($tempFile, $content);

            $filename = 'import_instructions.txt';

            return response()->download($tempFile, $filename, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ])->deleteFileAfterSend();

        } catch (Exception $e) {
            Log::error('Instructions download failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Generate modules section from database
     */
    private function generateModulesSection(): string
    {
        // Hidden module types (internal/system modules)
        $hiddenTypes = ['EVENT', 'SYSTEM'];

        // Get all modules grouped by type, excluding hidden types
        $modulesByType = \App\Models\Module::with('module_type')
            ->get()
            ->filter(function($module) use ($hiddenTypes) {
                return !in_array($module->module_type->mt_type ?? '', $hiddenTypes);
            })
            ->groupBy('module_type.mt_type')
            ->sortKeys()
            ->sortBy(function($modules, $type) {
                // PROTOCOL first, then alphabetically
                return $type === 'PROTOCOL' ? '0' : '1' . $type;
            });

        $sections = [];
        $sectionNumber = 7; // Start after static sections (1-6)

        foreach ($modulesByType as $moduleType => $modules) {
            $typeLabel = ucfirst(strtolower($moduleType));
            $section = "{$sectionNumber}. Available {$typeLabel} Modules:\n";

            $sortedModules = $modules->pluck('module_desc')->sort()->values();

            foreach ($sortedModules as $index => $moduleName) {
                $section .= "  {$sectionNumber}." . ($index + 1) . " {$moduleName}\n";
            }

            $sections[] = $section;
            $sectionNumber++;
        }

        return implode("\n", $sections);
    }
}
