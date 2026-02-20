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

        <div class="w-full flex-row flex">
            <div class="flex-1 bg-white bg-opacity-20 shadow-lg py-4 px-8">
                <div class="flex justify-between items-center">
                    <h4 class="font-bold text-sm text-gray-400 mb-2">{{ __('Device list') }}</h4>
                    <x-form.button size="sm" class="px-4" wire:click="moveAllDeviceFields">
                        @lang('Move All')
                        <span class="pl-4"><i class="f7-icons">chevron_right_2</i></span>
                    </x-form.button>
                </div>
                <div class="h-64 no-scrollbar overflow-y-auto border border-gray-300 shadow-md py-2 px-4">
                    <x-laravel-blade-sortable::sortable
                        group="devices_for_export"
                        class="flex-1 space-y-2"
                        name="device_list"
                        wire:onSortOrderChange.prevent="handleOnSortOrderChanged"
                        style="min-height:20rem;">
                        @foreach($device_list as $deviceItem)
                            <x-laravel-blade-sortable::sortable-item
                                sort-key="{{ $deviceItem }}"
                                class="cursor-pointer bg-white group group-hover:bg-opacity-100 bg-opacity-60 px-4 py-1 shadow border flex items-center justify-between">
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

            <div class="flex-1 bg-white bg-opacity-20 shadow-lg py-4 px-8">
                <div class="flex justify-between items-center">
                    <h4 class="font-bold text-sm text-gray-400 mb-2">{{__('Export-List')}}</h4>
                    <x-form.button size="sm" class="px-4" wire:click="resetExportList">
                        @lang('Reset')
                    </x-form.button>
                </div>
                <div class="h-64 no-scrollbar overflow-y-auto border border-gray-300 shadow-md py-2 px-4">
                    @foreach($lockedFields as $lockedItem)
                        <div class="bg-white opacity-40 px-4 py-1 shadow border flex items-center justify-between">
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
                                    class="cursor-pointer bg-white group group-hover:bg-opacity-100 bg-opacity-60 px-4 py-1 shadow border flex items-center justify-between">
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
        <div x-data="{ showFormat: false, polling: false, progress: 0, iframeRef: null }" 
             style="z-index: 10;" 
             class="relative"
             @start-download-csv.window="
                 const data = $event.detail;
                 const downloadId = data.download_id;
                 // Build and submit a hidden form that targets a hidden iframe so the page does not navigate
                 const iframe = document.createElement('iframe');
                 iframe.style.display = 'none';
                 iframe.name = `download_iframe_${downloadId}`;

                 // link iframe to Alpine state for later cleanup
                 this.iframeRef && this.iframeRef.remove(); // remove previous if any
                 this.iframeRef = iframe;
                 document.body.appendChild(iframe);

                 const form = document.createElement('form');
                 form.method = 'POST';
                 form.action = data.url;
                 form.style.display = 'none';
                 form.target = iframe.name;

                 // CSRF token (Laravel embeds this meta tag by default)
                 const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                 if (token) {
                     const csrfInput = document.createElement('input');
                     csrfInput.type = 'hidden';
                     csrfInput.name = '_token';
                     csrfInput.value = token;
                     form.appendChild(csrfInput);
                 }

                 // Append all payload fields
                 for (const [key, value] of Object.entries(data.params || {})) {
                     const input = document.createElement('input');
                     input.type = 'hidden';
                     input.name = key;
                     input.value = value;
                     form.appendChild(input);
                 }

                 document.body.appendChild(form);
                 form.submit();
                 document.body.removeChild(form);
                   
                   // Start polling for progress
                   polling = true;
                   progress = 0;
                   if (window.__exportProgressTimer) clearInterval(window.__exportProgressTimer);
                   window.__exportProgressTimer = setInterval(() => {
                       fetch(`${'{{ route('exportDevicesProgress') }}'}?id=${downloadId}`)
                           .then(r => r.json())
                           .then(d => {
                               if (d.progress === null || d.progress >= 100) {
                                   progress = 100;
                                   clearInterval(window.__exportProgressTimer);
                                   polling = false;

                                   // Clean up iframe once the backend finished writing the file
                                   if (this.iframeRef) {
                                       // give browser a short moment to start download
                                       setTimeout(() => {
                                           this.iframeRef.remove();
                                           this.iframeRef = null;
                                       }, 2000);
                                   }
                               } else {
                                   progress = d.progress;
                               }
                           })
                           .catch(() => {
                               clearInterval(window.__exportProgressTimer);
                               polling = false;
                           });
                   }, 500);
                "
              @start-export-excel.window="
                  const data = $event.detail;
                  const downloadId = data.download_id;

                  // Start async job via fetch POST
                  fetch(data.url, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                      },
                      body: JSON.stringify(data.params)
                      ,credentials: 'same-origin'
                  }).then(r => r.json()).then(() => {
                      // begin polling
                      polling = true;
                      progress = 0;

                      if (window.__exportProgressTimer) clearInterval(window.__exportProgressTimer);
                      window.__exportProgressTimer = setInterval(() => {
                          fetch(`${'{{ route('exportDevicesProgress') }}'}?id=${downloadId}`)
                              .then(r => r.json())
                              .then(d => {
                                  if (d.ready) {
                                      progress = 100;
                                      clearInterval(window.__exportProgressTimer);
                                      polling = false;

                                      const iframe = document.createElement('iframe');
                                      iframe.style.display = 'none';
                                      iframe.src = `${'{{ url('/download/devices') }}'}/${downloadId}`;
                                      document.body.appendChild(iframe);

                                      setTimeout(() => iframe.remove(), 20000);
                                  } else {
                                      progress = d.progress ?? progress;
                                  }
                              })
                              .catch(() => {
                                  clearInterval(window.__exportProgressTimer);
                                  polling = false;
                              });
                      }, 1000);
                  });
              "
        >
            <x-button.primary
                x-on:click="showFormat = true"
            >
                @lang('export')
            </x-button.primary>

            <!-- Format Selection Dropdown -->
            <div
                x-show="showFormat"
                x-on:click.away="showFormat = false"
                class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100"
            >
                <div class="py-1">
                    <button
                        wire:click.prevent="doExportDevices('csv')"
                        x-on:click="showFormat=false"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700"
                    >
                        CSV
                    </button>
                    <button
                        wire:click.prevent="doExportDevices('xlsx')"
                        x-on:click="showFormat=false"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700"
                    >
                        Excel (XLSX)
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div x-show="polling" x-cloak>
                <div class="absolute bottom-4 right-4 w-48 bg-white rounded-lg shadow-lg p-4 border z-50">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">@lang('Exporting Devices')...</span>
                            <template x-if="progress >= 100">
                                <span class="text-green-500">✓</span>
                            </template>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                 :style="`width: ${progress}%`">
                            </div>
                        </div>
                        <span class="text-xs text-gray-500" x-text="`${progress}% complete`"></span>
                    </div>
                </div>
            </div>
        </div>

        <x-button.secondary class="ml-4" x-on:click="$dispatch('dropdown-select', { element: '' })">
            @lang('cancel')
        </x-button.secondary>
    </div>

</div>