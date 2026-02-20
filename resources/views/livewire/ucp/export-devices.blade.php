<div>
    <div class="flex flex-col items-top justify-between">
        <div class="block_header w-full pb-4">
            <h3 class="title" id="message-heading">
                @lang('Export Device-List')
            </h3>
            <p class="description pb-8 lg:pb-0">
                @lang('Specify which data you want to export by dragging the corresponding fields into the «Export list» area. The order can also be defined by dragging the chosen fields.' )
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
                        class="flex-1 space-y-2 "
                        name="device_list"
                        wire:onSortOrderChange.prevent="handleOnSortOrderChanged"
                        style="min-height:20rem;">
                        @foreach($device_list as $deviceItem)
                            <x-laravel-blade-sortable::sortable-item
                                sort-key="{{ $deviceItem }}"
                                class="bg-white group group-hover:bg-opacity-100 bg-opacity-60 px-4 py-1 shadow border flex items-center justify-between">
                                <span class="font-bold text-sm opacity-60 group-hover:opacity-100">{{ $initialList[$deviceItem] }}</span><i class="f7-icons  opacity-40 group-hover:opacity-100">circle_grid_3x3_fill</i>
                            </x-laravel-blade-sortable::sortable-item>
                        @endforeach
                    </x-laravel-blade-sortable::sortable>
                </div>
            </div>
            <div class="flex h-80 justify-center items-center w-12">
                <div class="text-gray-600"><i class="f7-icons text-gray-600 text-2xl">arrow_right_arrow_left</i></div>
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
                            <span class="font-bold text-sm ">{{ __($lockedItem) }}</span>
                        </div>
                    @endforeach
                    <x-laravel-blade-sortable::sortable
                        group="devices_for_export"
                        class="flex-1 space-y-2 "
                        name="export_list"
                        wire:onSortOrderChange.prevent="handleOnSortOrderChanged"
                        style="min-height:20rem;">
                        @foreach($export_list as $exportItem)
                            @if(!in_array($exportItem, $lockedFields))
                                <x-laravel-blade-sortable::sortable-item
                                    sort-key="{{ $exportItem }}"
                                    class="bg-white group group-hover:bg-opacity-100 bg-opacity-60 px-4 py-1 shadow border flex items-center justify-between">
                                    <span class="font-bold text-sm opacity-60 group-hover:opacity-100">{{ $initialList[$exportItem] }}</span><i class="f7-icons  opacity-40 group-hover:opacity-100">circle_grid_3x3_fill</i>
                                </x-laravel-blade-sortable::sortable-item>
                            @endif
                        @endforeach
                    </x-laravel-blade-sortable::sortable>
                </div>
            </div>
        </div>
    </div>
    <div class="flex justify-end mt-4">
        <div x-data="{ polling: false, progress: 0 }" style="z-index: 10;">
            <x-button.primary
                class="ml-4"
                x-on:click="
                    $wire.doExportDevices();
                    polling = true;
                    let checkProgress = setInterval(() => {
                        fetch('{{ route('exportDevicesProgress') }}')
                            .then(response => response.json())
                            .then(data => {
                                console.log('Progress:', data.progress);
                                if (data.progress === null) {
                                    clearInterval(checkProgress);
                                    polling = false;
                                } else {
                                    progress = data.progress;
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                clearInterval(checkProgress);
                                polling = false;
                            });
                    }, 500);
                "
            >
                @lang('export')
            </x-button.primary>

            <!-- Progress Bar -->
            <div x-show="polling">
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
