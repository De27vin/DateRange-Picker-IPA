{{-- history-filter-ucp2.blade.php --}}
<div class="info-container text-medium block py-2 text-sm hover:text-gray-900 has-children">
    <div class="flex w-full justify-between items-center ml-0">
        <span class="flex justify-start w-full text-base h-12 items-center">
            @if($deviceSite) {{ __('Site History') }} @else {{ __('Device History') }} @endif
        </span>
    </div>

    <div class="flex justify-between">
        <div class="w-full md:w-1/2 space-x-0 md:space-x-8 space-y-4 md:space-y-0 flex flex-col justify-start md:flex-row md:justify-start">
            <div class="w-1/2">
                <x-input.group inline for="filter-dateFromValue" :label="__('From')">
                    <x-input.date wire:model="dateFilter.dateFromValue" id="filter-dateFromValue" placeholder="dd.mm.yyyy" />
                </x-input.group>
            </div>
            <div class="w-1/2">
                <x-input.group inline for="filter-dateToValue" :label="__('To')">
                    <x-input.date wire:model="dateFilter.dateToValue" id="filter-dateToValue" placeholder="dd.mm.yyyy" />
                </x-input.group>
            </div>
            @if($deviceSite)
                <div class="w-full h-15">
                    <x-input.group inline for="filter-historyContext" :label="__('History Context')">
                        <x-input.select class="w-full" wire:model="context" id="module_id">
                            @php arsort($contextOptions); @endphp
                            @foreach ($contextOptions as $value => $option)
                                <option value="{{ $value }}">{{ $option }}</option>
                            @endforeach
                        </x-input.select>
                    </x-input.group>
                </div>
            @endif
        </div>

        <div class="items-center justify-end">
{{--            <div class="mx-2 bottom-underline-light uppercase text-xs">@lang('Actions')</div>--}}

            <div class="flex">
                <div x-data="{ showFormat: false, polling: false, progress: 0 }" style="z-index: 10;">
                    <x-button.white
                        class="ml-0 border border-slate-200"
                        x-on:click="showFormat = true"
                    >
                        @lang('Export History')
                    </x-button.white>

                    <!-- Format Selection Dropdown -->
                    <div
                        x-cloak
                        x-show="showFormat"
                        x-on:click.away="showFormat = false"
                        class="absolute mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100"
                    >
                        <div class="py-1">
                            <button
                                wire:click="$set('exportFormat', 'csv')"
                                x-on:click="
                                    showFormat = false;
                                    polling = true;
                                    Livewire.emitTo('ucp.device-history-new', 'exportHistory');
                                    let checkProgress = setInterval(() => {
                                        fetch('{{ route('exportHistoryProgress') }}')
                                            .then(response => response.json())
                                            .then(data => {
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
                                class="w-full text-left px-4 py-2 text-sm text-gray-700"
                            >
                                CSV
                            </button>
                            <button
                                wire:click="$set('exportFormat', 'xlsx')"
                                x-on:click="
                                    showFormat = false;
                                    polling = true;
                                    Livewire.emitTo('ucp.device-history-new', 'exportHistory');
                                    let checkProgress = setInterval(() => {
                                        fetch('{{ route('exportHistoryProgress') }}')
                                            .then(response => response.json())
                                            .then(data => {
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
                                class="w-full text-left px-4 py-2 text-sm text-gray-700"
                            >
                                Excel (XLSX)
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar (keep existing) -->
                    <div x-show="polling" x-cloak>
                        <div class="absolute bottom-4 right-4 w-48 bg-white rounded-lg shadow-lg p-4 border z-50">
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium">{{ __('Downloading') }}...</span>
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

                <x-button.white class="ml-1 border border-slate-200" wire:click="resetFilters">
                    @lang('Reset Filters')
                </x-button.white>
            </div>
        </div>

    </div>

    {{-- historyfilter section --}}
{{--    <div class="space-x-0 space-y-0 flex justify-between mt-4">--}}

{{--        <div class="justify-start">--}}

{{--            <div class="mx-2 bottom-underline-light uppercase text-xs">@lang('Session Type')</div>--}}

{{--            <div class="flex">--}}
{{--                <div wire:click="toggleHistoryFilter('calls')">--}}
{{--                    @if($historyFilter['calls'])<x-button.primary class="ml-0 border border-slate-200">@lang('Alarm calls')</x-button.primary>--}}
{{--                    @else<x-button.white class="ml-0 border border-slate-200">@lang('Alarm calls')</x-button.white>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <div wire:click="toggleHistoryFilter('carcalls')">--}}
{{--                    @if($historyFilter['carcalls'])<x-button.primary class="border border-slate-200">@lang('Car calls')</x-button.primary>--}}
{{--                    @else<x-button.white class="border border-slate-200">@lang('Car calls')</x-button.white>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <div wire:click="toggleHistoryFilter('periodicals')">--}}
{{--                    @if($historyFilter['periodicals'])<x-button.primary class="border border-slate-200">@lang('Periodicals')</x-button.primary>--}}
{{--                    @else<x-button.white class="border border-slate-200">@lang('Periodicals')</x-button.white>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <div wire:click="toggleHistoryFilter('sets')">--}}
{{--                    @if($historyFilter['sets'])<x-button.primary class="border border-slate-200">@lang('SETS')</x-button.primary>--}}
{{--                    @else<x-button.white class="border border-slate-200">@lang('SETS')</x-button.white>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <div wire:click="toggleHistoryFilter('revivals')">--}}
{{--                    @if($historyFilter['revivals'])<x-button.primary class="border border-slate-200">@lang('REVIVALS')</x-button.primary>--}}
{{--                    @else<x-button.white class="border border-slate-200">@lang('REVIVALS')</x-button.white>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <div wire:click="toggleHistoryFilter('triggers')">--}}
{{--                    @if($historyFilter['triggers'])<x-button.primary class="border border-slate-200">@lang('TRIGGERS')</x-button.primary>--}}
{{--                    @else<x-button.white class="border border-slate-200">@lang('TRIGGERS')</x-button.white>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}

{{--        <div class="justify-end">--}}

{{--            <div class="ml-4 mx-2 bottom-underline-light uppercase text-xs">@lang('Session Severity')</div>--}}

{{--            <div class="flex">--}}
{{--                <div wire:click="toggleSeverityFilter('warnings')">--}}
{{--                    @if($severityFilter['warnings'])<x-button.primary class="border border-slate-200">@lang('Warnings')</x-button.primary>--}}
{{--                    @else<x-button.white class="border border-slate-200">@lang('Warnings')</x-button.white>--}}
{{--                    @endif--}}
{{--                </div>--}}

{{--                <div wire:click="toggleSeverityFilter('errors')">--}}
{{--                    @if($severityFilter['errors'])<x-button.primary class="border border-slate-200">@lang('Errors')</x-button.primary>--}}
{{--                    @else<x-button.white class="border border-slate-200">@lang('Errors')</x-button.white>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--    </div>--}}

    <div class="mt-4">
        <!-- Full width header section with underline -->
        <div class="flex justify-between mb-1 bottom-underline-light px-3">
            <div class="uppercase text-sm">@lang('Session Type')</div>
            <div class="uppercase text-sm text-right">@lang('Session Severity')</div>
        </div>
{{--        <div class="w-full h-px bg-slate-400 mb-2"></div> <!-- Full width line -->--}}

        <!-- Buttons section -->
        <div class="flex justify-between">
            <!-- Left button group -->
            <div class="flex">
                <div wire:click="toggleHistoryFilter('alarms')">
                    @if($historyFilter['alarms'])<x-button.primary class="ml-0 border border-slate-200">@lang('Alarm calls')</x-button.primary>
                    @else<x-button.white class="ml-0 border border-slate-200">@lang('Alarm calls')</x-button.white>
                    @endif
                </div>
                <div wire:click="toggleHistoryFilter('carcalls')">
                    @if($historyFilter['carcalls'])<x-button.primary class="border border-slate-200">@lang('Car calls')</x-button.primary>
                    @else<x-button.white class="border border-slate-200">@lang('Car calls')</x-button.white>
                    @endif
                </div>
                <div wire:click="toggleHistoryFilter('periodicals')">
                    @if($historyFilter['periodicals'])<x-button.primary class="border border-slate-200">@lang('Periodicals')</x-button.primary>
                    @else<x-button.white class="border border-slate-200">@lang('Periodicals')</x-button.white>
                    @endif
                </div>
                <div wire:click="toggleHistoryFilter('sets')">
                    @if($historyFilter['sets'])<x-button.primary class="border border-slate-200">@lang('SETS')</x-button.primary>
                    @else<x-button.white class="border border-slate-200">@lang('SETS')</x-button.white>
                    @endif
                </div>
                <div wire:click="toggleHistoryFilter('revivals')">
                    @if($historyFilter['revivals'])<x-button.primary class="border border-slate-200">@lang('REVIVALS')</x-button.primary>
                    @else<x-button.white class="border border-slate-200">@lang('REVIVALS')</x-button.white>
                    @endif
                </div>
                <div wire:click="toggleHistoryFilter('triggers')">
                    @if($historyFilter['triggers'])<x-button.primary class="border border-slate-200">@lang('TRIGGERS')</x-button.primary>
                    @else<x-button.white class="border border-slate-200">@lang('TRIGGERS')</x-button.white>
                    @endif
                </div>
                <div wire:click="toggleHistoryFilter('calls')">
                    @if($historyFilter['calls'])<x-button.primary class="border border-slate-200">@lang('Calls')</x-button.primary>
                    @else<x-button.white class="border border-slate-200">@lang('Calls')</x-button.white>
                    @endif
                </div>
            </div>

            <!-- Right button group -->
            <div class="flex">
                <div wire:click="toggleSeverityFilter('warnings')">
                    @if($severityFilter['warnings'])<x-button.primary class="border border-slate-200">@lang('Warnings')</x-button.primary>
                    @else<x-button.white class="border border-slate-200">@lang('Warnings')</x-button.white>
                    @endif
                </div>
                <div wire:click="toggleSeverityFilter('errors')">
                    @if($severityFilter['errors'])<x-button.primary class="border border-slate-200">@lang('Errors')</x-button.primary>
                    @else<x-button.white class="border border-slate-200">@lang('Errors')</x-button.white>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
