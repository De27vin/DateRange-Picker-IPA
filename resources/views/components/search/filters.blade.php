@props([
    'searchTabs',
    'listCount',
    'filtersId',
    'filters',
    'groups',
    'sortOptions',
    'alertTranslations',
    'alertsCountGrouped',
    'showMenu' => false,
    'exportSites' => false,
])

<div x-data="{activeTabs: @entangle('searchTabs'), selected: ''}">

    <div>
        <div class="w-full mt-8">
            <div class="w-full block lg:flex items-center mb-4">
                <div x-cloak class="h-24 flex flex-grow my-4 justify-between bg-gray-200 bg-opacity-60 shadow-md justify-between items-center border-t border-slate-300">
                    <div class="flex max-w-2xl space-x-4 p-2 pr-4">

                        @foreach($searchTabs as $tab => $active)
                            <x-button.primary wire:click="toggleSearchTab('{{$tab}}')" x-show="activeTabs['{{$tab}}']" class="shadow-md">
                                @lang($tab)
                                @if(!empty($listCount))<span class="pl-4">{{ $listCount[$tab] }}</span>@endif
                            </x-button.primary>

                            <x-button.white x-cloak wire:click="toggleSearchTab('{{$tab}}')" x-show="!activeTabs['{{$tab}}']" class="shadow-md">
                                @lang($tab)
                                @if(!empty($listCount))<span class="pl-4">{{ $listCount[$tab] }}</span>@endif
                            </x-button.white>
                        @endforeach
                    </div>

                    <div class="flex items-center mx-5 pl-12" style="width: 54%;">
                        <x-button.secondary class="flex items-center pr-4 shadow-md" x-on:click="$dispatch('dropdown-select', { element: 'toggleSearchFilter' })">
                            @lang('Filters')
                            <i class="f7-icons text-lg ml-4">slider_horizontal_3</i>
                        </x-button.secondary>

                        <div class="w-full flex items-center justify-center relative ml-5 @if(!$showMenu) mr-6 @endif">
                            <div class="absolute ml-4 left-0"><x-input.select-combobox></x-input.select-combobox></div>
                            <input class="searchfield" name="filters.search" type="text" value="" wire:model.debounce.1200ms="filters.search" style="padding-left:4rem; height: 45px;">
                            <x-monoicon.search class="absolute right-5 text-gray-400"/>
                        </div>

                        @if($showMenu)
                            <div class="boxitemDropdown z-20">
                                <x-form.actionmenu icon="options-vertical" :data="''">
                                    <x-form.dropdown-item x-on:click="$dispatch('dropdown-select', { element: 'toggleExport' });open=false">@lang('Export current list') ...</x-form.dropdown-item>
                                    <x-form.dropdown-item x-on:click="$dispatch('dropdown-select', { element: 'toggleExportComments' });open=false">@lang('Export comments of listed devices') ...</x-form.dropdown-item>
                                </x-form.actionmenu>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak @dropdown-select.window="selected == $event.detail.element ? selected = null : selected = $event.detail.element">
        <dic class="">
            <div x-show="selected === 'toggleExport'" class="relative bg-white bg-opacity-20 w-full shadow-lg p-8 pt-4 my-4">
                <livewire:ucp.export-devices
                        :filtersId="$filtersId"
                        :exportSites="($exportSites ?? false)"
                ></livewire:ucp.export-devices>
            </div>
            <div x-show="selected === 'toggleExportComments'" class="relative bg-white bg-opacity-20 w-full shadow-lg p-8 pt-4 my-4">
                <livewire:ucp.export-comments
                        :filtersId="$filtersId"
                        :exportSites="($exportSites ?? false)"
                ></livewire:ucp.export-comments>
            </div>
            <div x-show="selected === 'toggleSearchFilter'" class="relative bg-white bg-opacity-20 w-full shadow-lg p-8 pt-4 my-4">


{{--                HIDE LABELS--}}
{{--                <div class="md:flex justify-between pb-4">--}}
{{--                    --}}{{-- LABELS --}}
{{--                    <div class="relative flex-auto w-full pl-1 pr-0">--}}
{{--                        <div class="relative flex justify-between items-center">--}}
{{--                            <x-groups.dropdown-node :groups="$groups"></x-groups.dropdown-node>--}}
{{--                            <div class="groups-container m-0 pl-36">--}}
{{--                                @foreach($filters['groups'] as $deviceLabel)--}}
{{--                                    <x-groups.active-node :id="$deviceLabel['dl_id']">{{$deviceLabel['dl_name']}}</x-groups.active-node>--}}
{{--                                @endforeach--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    --}}{{-- LABELS --}}
{{--                </div>--}}

                {{-- ALERTS --}}
                <x-search.alerts
                        :filters="$filters"
                        :alertsCountGrouped="$alertsCountGrouped"
                        :alertTranslations="$alertTranslations">
                </x-search.alerts>
                {{-- ALERTS --}}

                <div class="sort-panel relative flex space-x-8 justify-end h-9">
                    {{-- SORT --}}
                    <div class="flex sort-group">
                        <div class="text-white text-bold">
                            <x-forms.select2
                                    :model="'filters.sortedby'"
                                    :options="$sortOptions"
                                    :placeholder="@lang('Select a sort')"
                            >@lang('sorted by')</x-forms.select2>
                        </div>
                        <span x-data="{sortDirection: '{{ $filters['sortDirection'] }}' }" wire:model="filters.sortDirection" class="button-group h-9">
                            <x-monoicon.chevron-down x-on:click="$dispatch('input', 'desc')" class="{{ ($filters['sortDirection'] == 'desc' ? 'active' : '') }} h-9 w-8 pt-2 ml-1 chevron-dev"></x-monoicon.chevron-down>
                            <x-monoicon.chevron-up  x-on:click="$dispatch('input', 'asc')" class="{{ ($filters['sortDirection'] == 'asc' ? 'active' : '') }} h-9 w-8 pt-3 ml-1 chevron-dev"></x-monoicon.chevron-up>
                        </span>
                        <span class="h-9 w-5 bg-gray-400 rounded-r-full" style="margin-left: 2px;"></span>
                    </div>
                    {{-- SORT --}}

                    {{-- RESET --}}
                    <div class="flex items-center">
                        <x-form.button wire:click="resetFilters">
                            @lang('Reset Filters')
                        </x-form.button>
                    </div>
                    {{-- RESET --}}
                </div>

            </div>
        </dic>
    </div>
    {{-- / top section --}}
</div>


<style>
    .chevron-dev {
        background-color: #94a3b8 !important;
    }
    .chevron-dev.active {
        background-color: #9398a4 !important;
    }
</style>