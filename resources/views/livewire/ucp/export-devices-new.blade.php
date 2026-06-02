<div>
    <div class="flex flex-col items-top justify-between">
        <div class="block_header w-full pb-4">
            <h3 class="title" id="message-heading">
                @lang('Export Device-List')
            </h3>
            <p class="description pb-8 lg:pb-0">
                @lang('Specify which data you want to export by dragging the corresponding fields into the «Export list» area. The order can also be defined by dragging the chosen fields.')
            </p>
        </div>

        <div class="w-full flex-row flex gap-6">
            <div class="pt-2 flex-1 bg-white bg-opacity-20 border border-gray-200 py-4 px-8">
                <div class="mb-2 flex justify-between items-center">
                    <h4 class="font-bold text-sm text-gray-400 mb-2">{{ __('Device list') }}</h4>
                    <x-form.button size="sm" class="px-4" wire:click="moveAllDeviceFields">
                        @lang('Move All')
                        <span class="pl-4"><i class="f7-icons">chevron_right_2</i></span>
                    </x-form.button>
                </div>
                <div class="h-64 no-scrollbar overflow-y-auto border border-gray-200 py-2 px-4 bg-white bg-opacity-60">
                    <x-laravel-blade-sortable::sortable
                        group="devices_for_export"
                        class="flex-1 space-y-2"
                        name="device_list"
                        wire:onSortOrderChange.prevent="handleOnSortOrderChanged"
                        style="min-height:20rem;">
                        @foreach($device_list as $deviceItem)
                            <x-laravel-blade-sortable::sortable-item
                                sort-key="{{ $deviceItem }}"
                                class="cursor-pointer bg-white group group-hover:bg-opacity-100 bg-opacity-60 px-4 py-1 border border-gray-200 flex items-center justify-between">
                                <span class="font-bold text-sm opacity-60 group-hover:opacity-100">{{ $initialList[$deviceItem] }}</span>
                                <i class="f7-icons opacity-40 group-hover:opacity-100">circle_grid_3x3_fill</i>
                            </x-laravel-blade-sortable::sortable-item>
                        @endforeach
                    </x-laravel-blade-sortable::sortable>
                </div>
            </div>

            <div class="flex h-80 justify-center items-center w-12">
                <div class="text-gray-600">
                    <i class="f7-icons text-gray-600 text-2xl">arrow_right_arrow_left</i>
                </div>
            </div>

            <div class="pt-2 flex-1 bg-white bg-opacity-20 border border-gray-200 py-4 px-8">
                <div class="mb-2 flex justify-between items-center">
                    <h4 class="font-bold text-sm text-gray-400 mb-2">{{__('Export-List')}}</h4>
                    <x-form.button size="sm" class="px-4" wire:click="resetExportList">
                        @lang('Reset')
                    </x-form.button>
                </div>
                <div class="h-64 no-scrollbar overflow-y-auto border border-gray-200 py-2 px-4 bg-white bg-opacity-60">
                    @foreach($lockedFields as $lockedItem)
                        <div class="bg-white opacity-40 px-4 py-1 border border-gray-200 flex items-center justify-between">
                            <span class="font-bold text-sm">{{ __($lockedItem) }}</span>
                        </div>
                    @endforeach
                    <x-laravel-blade-sortable::sortable
                        group="devices_for_export"
                        class="flex-1 space-y-2"
                        name="export_list"
                        wire:onSortOrderChange.prevent="handleOnSortOrderChanged"
                        style="min-height:20rem;">
                        @foreach($export_list as $exportItem)
                            @if(!in_array($exportItem, $lockedFields))
                                <x-laravel-blade-sortable::sortable-item
                                    sort-key="{{ $exportItem }}"
                                    class="cursor-pointer bg-white group group-hover:bg-opacity-100 bg-opacity-60 px-4 py-1 border border-gray-200 flex items-center justify-between">
                                    <span class="font-bold text-sm opacity-60 group-hover:opacity-100">{{ $initialList[$exportItem] }}</span>
                                    <i class="f7-icons opacity-40 group-hover:opacity-100">circle_grid_3x3_fill</i>
                                </x-laravel-blade-sortable::sortable-item>
                            @endif
                        @endforeach
                    </x-laravel-blade-sortable::sortable>
                </div>
            </div>
        </div>
    </div>

    <!-- Field Presets Section -->
    <div class="bg-white bg-opacity-10 rounded-lg p-4">
        <div class="flex justify-between mb-4">
            <h4 class="font-bold text-sm text-gray-400">@lang('Field Presets')</h4>
        </div>

        <!-- Preset Controls - 4 States with fixed widths -->
        <div class="flex items-start align-start space-x-4 mb-4">
            <!-- Preset Dropdown (1/3 width, consistent height) -->
            <div class="w-1/3">
                <select 
                    class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500 h-10"
                    wire:model="selectedPreset" 
                    wire:change="loadPreset($event.target.value)"
                >
                    <option value="">@lang('Choose list item')...</option>
                    @foreach($presets as $presetId => $preset)
                        <option value="{{ $presetId }}">{{ $preset['name'] ?? 'Unnamed Preset' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- State 1: Selected preset - show trash icon -->
            @if($this->canDeletePreset)
                <div
                    wire:click="deletePreset('{{ $selectedPreset }}')"
                    wire:confirm="@lang('Are you sure you want to delete this preset?')"
                    class="cursor-pointer bg-white border border-gray-300 rounded text-gray-600 hover:text-red-600 hover:border-red-300 transition-colors h-10 w-10 flex items-center justify-center flex-shrink-0"
                    title="@lang('Delete Preset')"
                    wire:key="delete-{{ $selectedPreset }}"
                >
                    <i class="f7-icons text-lg">trash</i>
                </div>
            
            <!-- State 2: Fields selected but no matching preset exists - show save preset button -->
            @elseif($this->canSavePreset)
                <div
                    wire:click="showSavePresetForm"
                    class="cursor-pointer bg-white border border-gray-300 rounded px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors h-10 flex-shrink-0"
                    wire:key="save-preset-btn"
                >
                    @lang('Save preset')
                </div>
            @endif

            <!-- State 3: Saving new preset - show input and save/cancel buttons -->
            @if($showSavePreset)
                <div class="w-1/3">
                    <input 
                        type="text"
                        wire:model="newPresetName" 
                        placeholder="@lang('Preset name')"
                        maxlength="100"
                        style="background-color: white !important; padding-block: 0;"
                        class="p-0 w-full !bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500 h-10"
                    />
                </div>
            <div class="w-1/3 flex gap-2">
                <div
                        wire:click="savePreset"
                        class="cursor-pointer bg-white border border-gray-300 rounded px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors h-10 flex-shrink-0"
                >
{{--                    @lang('Save')--}}
                    Save
                </div>
                <div
                        wire:click="cancelSavePreset"
                        class="cursor-pointer bg-white border border-gray-300 rounded px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors h-10 flex-shrink-0"
                >
                    @lang('Cancel')
                </div>
            </div>
            @endif
        </div>
        
        <!-- Error Display -->
        @if($presetError)
            <div class="mb-4">
                <p class="text-sm text-red-800">{{ $presetError }}</p>
            </div>
        @endif
        
        <!-- Validation Error for Preset Name -->
        @if($showSavePreset)
            @error('newPresetName')
                <div class="mb-4">
                    <p class="text-sm text-red-800">{{ $message }}</p>
                </div>
            @enderror
        @endif
    </div>

    <div class="flex justify-end mt-4">
        <div x-data="exportHandler(@js([
                'type' => 'devices',
                'componentId' => $exportComponentId,
                'storeUrl' => route('exports.store'),
                'progressLabel' => __('Exporting Devices…'),
            ]))"
             x-init="init()"
             style="z-index: 10;"
             class="relative">
            <x-button.primary
                x-on:click="showFormat = true"
            >
                @lang('export')
            </x-button.primary>

            <x-export.format-dropdown wire-method="doExportDevices" />

            <x-export.progress-bar />
        </div>

        <x-button.secondary class="ml-4" x-on:click="$dispatch('dropdown-select', { element: '' })">
            @lang('cancel')
        </x-button.secondary>
    </div>

</div>
