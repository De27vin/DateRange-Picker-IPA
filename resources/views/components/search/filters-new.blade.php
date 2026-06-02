{{-- @deprecated --}}
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
    'showCreateSite' => false,
    'exportSites' => false,
    'countIcon' => 'building'
])

<div x-data="{activeTabs: @entangle('searchTabs'), selected: ''}">

    <div>
        <div class="w-full">
            <div class="w-full block lg:flex items-center mb-4">
                <div x-cloak class="h-24 bottom-underline flex flex-grow justify-between bg-white bg-opacity-60 justify-between items-center">
                    <div class="flex max-w-4xl gap-4 p-2 pr-4 items-center">

                        @foreach($searchTabs as $tab => $active)
                            <x-button.primary-new _wire:click="toggleSearchTab('{{$tab}}')" x-show="activeTabs['{{$tab}}']" >
                                @lang($tab)
                                @if(!empty($listCount))<span class="pl-4">{{ $listCount[$tab] }}</span>@endif
                            </x-button.primary-new>

                            <x-button.white-new x-cloak wire:click="toggleSearchTab('{{$tab}}')" x-show="!activeTabs['{{$tab}}']" >
                                @lang($tab)
                                @if(!empty($listCount))<span class="pl-4">{{ $listCount[$tab] }}</span>@endif
                            </x-button.white-new>
                        @endforeach

                        <div class="ml-8">
                            <x-search.counter :icon="$countIcon" ></x-search.counter>
                        </div>
                    </div>

                    <div class="flex items-center mx-5 pl-12" style="width: 54%;">
                        <x-button.secondary class="text-center" x-on:click="$dispatch('dropdown-select', { element: 'toggleSearchFilter' })"
                        style="background-color: #8fabdd;">
                            @lang('Filters')
{{--                            <i class="f7-icons text-lg ml-4">slider_horizontal_3</i>--}}
                        </x-button.secondary>

                        <div class="w-full flex items-center justify-center relative ml-5 @if(!$showMenu && !$showCreateSite) mr-6 @endif">
                            <div class="absolute ml-4 left-0"><x-input.select-combobox></x-input.select-combobox></div>
                            <input class="searchfield" name="filters.search" type="text" value="" wire:model.debounce.1200ms="filters.search" style="padding-left:4.5rem; height: 45px; box-shadow: none;">
                            <x-monoicon.search class="absolute right-5 text-gray-400"/>
                        </div>

                        @if($showMenu)
                            <div class="boxitemDropdown z-20">
                                <x-form.actionmenu icon="square_arrow_down" :data="''">
                                    @if(Auth::user()->canImport())
                                        <x-form.dropdown-item x-on:click="$dispatch('dropdown-select', { element: 'toggleImport' });open=false">@lang('Import devices') ...</x-form.dropdown-item>
                                    @endif
                                    <x-form.dropdown-item x-on:click="$dispatch('dropdown-select', { element: 'toggleExport' });open=false">@lang('Export current list') ...</x-form.dropdown-item>
                                    <x-form.dropdown-item x-on:click="$dispatch('dropdown-select', { element: 'toggleExportComments' });open=false">@lang('Export comments of listed devices') ...</x-form.dropdown-item>
                                </x-form.actionmenu>
                            </div>
                        @endif

                        @if($showCreateSite)
                            <a href="/devices-site-create" style="margin-left: 0.3rem; padding-bottom: 0.6rem;">
                              <span class="tt">
                                <i class="f7-icons icon icon-sm tts cursor-pointer" style="color: white; background-color: #8faadc;">plus</i>
                                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ __('Create Site') }}</span>
                              </span>
                            </a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak @dropdown-select.window="selected == $event.detail.element ? selected = null : selected = $event.detail.element">
        <div class="">
            @if(Auth::user()->canImport())
                <div x-show="selected === 'toggleImport'" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                    <livewire:ucp.import-devices></livewire:ucp.import-devices>
                </div>
            @endif

            <div x-show="selected === 'toggleExport'" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <livewire:ucp.export-devices-new
                        :filtersId="$filtersId"
                        :exportSites="($exportSites ?? false)"
                ></livewire:ucp.export-devices-new>
            </div>
            <div x-show="selected === 'toggleExportComments'" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <livewire:ucp.export-comments-new
                        :filtersId="$filtersId"
                        :exportSites="($exportSites ?? false)"
                ></livewire:ucp.export-comments-new>
            </div>
            <div x-show="selected === 'toggleSearchFilter'" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">


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
                                    placeholder="{{ __('Select a sort') }}"
{{--                                    :color="'primary'" - hint : --}}
                            >@lang('sorted by')</x-forms.select2>
                        </div>
                        <span x-data="{sortDirection: '{{ $filters['sortDirection'] }}' }" wire:model="filters.sortDirection" class="button-group h-9">
                            <x-monoicon.chevron-up  x-on:click="$dispatch('input', 'asc')" class="{{ ($filters['sortDirection'] == 'asc' ? 'active' : '') }} h-9 w-8 pt-3 ml-1 chevron-dev"></x-monoicon.chevron-up>
                            <x-monoicon.chevron-down x-on:click="$dispatch('input', 'desc')" class="{{ ($filters['sortDirection'] == 'desc' ? 'active' : '') }} h-9 w-8 pt-2 ml-1 chevron-dev"></x-monoicon.chevron-down>
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
        </div>
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

    .icon-wrapper {
      margin-left: 1rem;
    }

    .icon {
      padding: 0.1rem;
      border: solid 2px $darken4;
      border-radius: 0.2rem;
      width: 1.5rem;
      height: 1.5rem;

      &.default {
        background-color: lightblue;
      }
    }

    .icon-sm {
      font-size: 1rem;
    }

    .icon-md {
      font-size: 1.3rem;
    }

    .icon-lg {
      font-size: 1.6rem;
    }
</style>