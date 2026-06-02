<div class="import-devices-container">
    {{-- Header --}}
    <div class="import-header mb-4">
        <h3 class="text-xl font-semibold">{{ __('Import Devices') }}</h3>
        <p class="text-gray-600 mt-1">{{ __('Import multiple devices and sites from CSV or Excel file') }}</p>
    </div>

    @if($currentStep !== 'completed')
        {{-- Template Download Buttons --}}
        <div class="mb-2 flex flex-wrap gap-2 items-center">
            <x-form.button size="compact" color="primary" wire:click="downloadTemplate('xlsx')">
                {{ __('Download Excel Template') }}
            </x-form.button>
            <x-form.button size="compact" color="primary" wire:click="downloadTemplate('csv')">
                {{ __('Download CSV Template') }}
            </x-form.button>
            <x-form.button size="compact" color="primary" wire:click="downloadInstructions">
                {{ __('Download Instructions') }}
            </x-form.button>
        </div>

        {{-- CLI copy toggle --}}
        <div class="mb-4 ml-3 flex w-full items-center" style="align-items: self-end;">
            <input wire:model.defer="copyNumberToCli" class="uiswitch uiswitch-new" type="checkbox" />
            <label class="text-sm ml-2">{{ __('Update CLI') }}</label>
            <div class="f7-icons-wrapper tt" style="height: 1.6rem; cursor: help;">
                <i class="f7-icons sm tts">question_circle</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm">
                    {{ __('use primary phone number as CLI settings (call.alarm.route1.cli.number & call.outbound.trunk.cli.number)') }}
                </span>
            </div>
        </div>
    @endif

    {{-- Upload Section --}}
    @if($currentStep === 'upload')
    <div class="upload-section p-6 border-2 border-dashed border-gray-300 bg-gray-50">
        <div class="text-center">
            {{-- SVG Icon - COMMENTED FOR DEMO
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            --}}
            <div class="my-4">
                <label for="file-upload" class="cursor-pointer">
                    <span class="mt-2 block text-sm font-medium text-gray-900">
                        {{ __('Click to upload or drag and drop') }}
                    </span>
                    <span class="mt-1 block text-xs text-gray-500">
                        {{ __('CSV or XLSX up to 2MB') }}
                    </span>
                    <input
                        id="file-upload"
                        type="file"
                        class="sr-only"
                        accept=".csv,.xlsx,.xls"
                        wire:model="importFile"
                    >
                </label>
            </div>

            @if($importFile)
            <div class="mt-4 p-3 bg-white border border-gray-200 inline-block">
                <p class="text-sm text-gray-700">
                    <span class="font-medium">{{ __('Selected file:') }}</span>
                    {{ $importFile->getClientOriginalName() }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ __('Size:') }} {{ number_format($importFile->getSize() / 1024, 2) }} KB
                </p>
            </div>
            @endif

            @error('importFile')
            <div class="mt-3 text-red-600 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>

        @if($importFile && !$isValidating)
        <div class="mt-6 text-center flex gap-3 justify-center">
            <x-form.button size="md" color="primary" wire:click="validateFile">
                {{ __('Validate File') }}
            </x-form.button>
            <x-form.button size="md" color="default" wire:click="resetImport">
                {{ __('Cancel') }}
            </x-form.button>
        </div>
        @endif

        @if($isValidating)
        <div class="mt-6 text-center">
            <div class="inline-flex items-center">
                <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700">{{ __('Validating file...') }}</span>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Validation Results --}}
    @if($currentStep === 'validated' && $validationResult)
    <div class="validation-results">
        {{-- Summary --}}
        @if($validationResult['valid'])
        <div class="p-4 bg-green-50 border border-green-200 mb-4">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-medium text-green-900">{{ __('Validation Passed!') }}</h3>
                    <div class="mt-2 text-base text-gray-700">
                        <p>{{ __('File is ready for import') }}</p>
                        @if(isset($validationResult['summary']))
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li><strong>{{ __('Total rows:') }}</strong> {{ $validationResult['summary']['totalRows'] }}</li>
                            <li><strong>{{ __('New sites:') }}</strong> {{ $validationResult['summary']['newSites'] }}</li>
                            <li><strong>{{ __('Existing sites:') }}</strong> {{ $validationResult['summary']['existingSites'] }}</li>
                            <li><strong>{{ __('New devices:') }}</strong> {{ $validationResult['summary']['newDevices'] }}</li>
                        </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Warnings --}}
        @if(isset($validationResult['warnings']) && count($validationResult['warnings']) > 0)
        <div class="p-4 bg-yellow-50 border border-yellow-200 mb-4">
            <h4 class="font-medium text-yellow-900 mb-2">{{ __('Warnings') }} ({{ count($validationResult['warnings']) }})</h4>
            <div class="max-h-60 overflow-y-auto">
                @foreach($validationResult['warningsByRow'] as $rowNum => $warnings)
                <div class="mb-3 p-2 bg-white border border-yellow-300">
                    <p class="font-medium text-yellow-900">{{ __('Row') }} {{ $rowNum }}:</p>
                    <ul class="list-disc list-inside mt-1 text-sm text-yellow-800">
                        @foreach($warnings as $warning)
                        <li>
                            @if($warning['column'])
                                <strong>{{ $warning['column'] }}:</strong>
                            @endif
                            {{ $warning['message'] }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex gap-3 justify-end">
            <x-form.button size="md" color="default" wire:click="resetImport">
                {{ __('Cancel') }}
            </x-form.button>
            <x-form.button size="md" color="primary" wire:click="executeImport">
                {{ __('Execute Import') }}
            </x-form.button>
        </div>

        @else
        {{-- Errors --}}
                    <div class="p-4 bg-red-50 border border-red-200 mb-4">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-medium text-red-900">{{ __('Validation Failed') }}</h3>
                    <p class="text-sm text-red-800 mt-1">
                        {{ __('Found') }} {{ count($validationResult['errors']) }} {{ __('errors. Please fix them and try again.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto border border-red-200 pt-2 pl-4 space-y-4">
            @foreach($validationResult['errorsByRow'] as $rowNum => $errors)
            <div class="p-3 border-b border-red-100 bg-white hover:bg-red-50">
                <p class="font-medium text-red-900">{{ __('Row') }} {{ $rowNum }}:</p>
                <ul class="list-disc list-inside mt-1 text-sm text-red-800 space-y-1">
                    @foreach($errors as $error)
                    <li>
                        @if($error['column'])
                            <strong>{{ $error['column'] }}:</strong>
                        @endif
                        @if($error['value'])
                            "{{ $error['value'] }}" -
                        @endif
                        {{ $error['message'] }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            <x-form.button size="md" color="primary" wire:click="resetImport">
                {{ __('Upload Different File') }}
            </x-form.button>
        </div>
        @endif
    </div>
    @endif

    {{-- Executing --}}
    @if($isExecuting)
    <div class="p-6 text-center">
        <div class="inline-flex items-center">
            <svg class="animate-spin h-8 w-8 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xl text-gray-700">{{ __('Importing devices...') }}</span>
        </div>
        <p class="text-gray-600 mt-2">{{ __('This may take a few moments. Please do not close this window.') }}</p>
    </div>
    @endif

    {{-- Completed --}}
    @if($importCompleted && $importStats)
    <div class="p-4 bg-green-50 border border-green-200 mb-4">
        <div class="flex items-start">
            <svg class="h-6 w-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ml-3 flex-1">
                <h3 class="text-lg font-medium text-green-900">{{ __('Import Completed Successfully!') }}</h3>
                <div class="mt-2 text-base text-gray-700">
                    <p>{{ __('Imported data summary') }}</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li><strong>{{ __('New sites created:') }}</strong> {{ $importStats['sites'] }}</li>
                        <li><strong>{{ __('Devices added to existing sites:') }}</strong> {{ $importStats['existingSites'] }}</li>
                        <li><strong>{{ __('Total devices imported:') }}</strong> {{ $importStats['devices'] }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="flex justify-end mt-4">
        <x-form.button size="md" color="primary" wire:click="resetImport">
            {{ __('Import More Devices') }}
        </x-form.button>
    </div>
    @endif

    @push('scripts')
    <script>
        // Handle template download
        window.addEventListener('download-template', event => {
            // Create a temporary link and trigger download
            const link = document.createElement('a');
            link.href = event.detail.url;
            link.download = ''; // Force download instead of opening
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
    @endpush
</div>
