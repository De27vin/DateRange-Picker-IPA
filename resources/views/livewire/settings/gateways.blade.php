{{--DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED --}}
<div class="mt-16 mx-auto px-12" style="padding-bottom: -8rem;">

    @php
        if($tabs['disabled']) {
            $gateways = $gateways_disabled;
            $tabState = 'disabled';
        } elseif ($tabs['enabled']) {
            $gateways = $gateways_enabled;
            $tabState = 'enabled';
        } elseif ($tabs['assigned']) {
            $gateways = $gateways_assigned;
            $tabState = 'assigned';
        } elseif ($tabs['unassigned']) {
            $gateways = $gateways_unassigned;
            $tabState = 'unassigned';
        }
    @endphp

    <x-page.header class="px-4">
        <x-slot name="title">@lang('Device Gateway Management')</x-slot>
        <x-slot name="description">
            @lang('Create new or manage existing device gateways.')
        </x-slot>
    </x-page.header>

    <div class="px-4 w-full search-group flex items-center sm:justify-between pb-4 mb-8 bottom-underline">
        <div class="relative _w-full _md:w-96 _lg:w-128 w-1/2 pl-1 pr-8 _mb-1 md:mb-0">
            <label class="default" for="gateway_search">
                @lang('Search')
            </label>
            <input wire:model="filters.search" class="" name="filters.search" type="text" value="" placeholder="@lang('Search for gateway') ...">
            </input>
        </div>
        <div class="relative pl-0 pr-1 mb-1">
            <span class="flex items-center space-x-4">
                    @if($isAdmin)
                        <x-form.button class=" " color="blue" type="button" wire:click.prevent="showNewGatewayForm">
                            <x-monoicon.add></x-monoicon.add>
                        </x-form.button>
                    @endif

                    <x-form.button type="button"><a class="whitespace-nowrap flex items-center" href="{{ route('export-gateways', ['tab' => $tabState, 'search' => $filters['search']])}}">
                        @lang('Export')
                    </a></x-form.button>
            </span>
        </div>
    </div>


    <div class="@if(!$showNewGateway) hidden @endif px-4 pb-5 w-full mb-8 bottom-underline">
        <p class="description pb-8">
            @lang('Enter new gateway. The password for device gateway is generated, gets the status disabled.')
        </p>
        <form wire:submit.prevent="saveGateways">
            <div class="relative flex w-full mb-1 md:mb-0">
                <div class="w-1/2 mr-1">
                    <x-input.group  for="newMacAddress" label="{{__('Mac Address')}}">
                        <x-input.text wire:model.defer="newMacAddress" class="w-full" name="newMacAddress" />
                    </x-input.group>
                    @error('newMacAddress')<div class="bg-danger-600 text-gray-50 dark:bg-danger-600 dark:text-gray-100 bg-opacity-40 font-medium flex items-center px-2 py-1 mx-auto pointer-events-none text-sm w-full error">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="w-1/2">
                    <x-input.group  for="newImei" label="{{__('Imei number')}}">
                        <x-input.text wire:model.defer="newImei" class="w-full" name="newImei" />
                    </x-input.group>
                    @error('newImei')<div class="bg-danger-600 text-gray-50 dark:bg-danger-600 dark:text-gray-100 bg-opacity-40 font-medium flex items-center px-2 py-1 mx-auto pointer-events-none text-sm w-full error">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div name="footer" class="flex justify-end">
                <div class="pl-4"><x-button.secondary wire:click.prevent.self="hideNewGatewayForm">@lang('Cancel')</x-button.secondary></div>
                <div class="pl-4"><x-button.primary type="submit">@lang('Save')</x-button.primary></div>
            </div>
        </form>
    </div>

    <div class="list-filter my-8 px-4 space-x-8 space-y-4 lg:space-y-0 flex flex-col justify-start lg:flex-row lg:justify-between">
        <div wire:model="tabs" class=" flex justify-start">
            <div wire:click="setTab('enabled')" class="@if($tabs['enabled']) active @endif tab relative z-0 inline-flex flex filter-enabled">
                <button class="btn-border" type="button">
                    @lang('enabled')
                </button>
                <button class="" type="button">
                    {{ $gateways_enabled->total() }}
                </button>
            </div>
            <div wire:click="setTab('disabled')" class="@if($tabs['disabled']) active @endif tab relative z-0 inline-flex filter-disabled">
                <button class="btn-border" type="button">
                    @lang('disabled')
                </button>
                <button class="" type="button">
                    {{ $gateways_disabled->total() }}
                </button>
            </div>
            <div wire:click="setTab('assigned')" class="@if($tabs['assigned']) active @endif tab relative z-0 inline-flex filter-disabled">
                <button class="btn-border" type="button">
                    @lang('assigned')
                </button>
                <button class="" type="button">
                    {{ $gateways_assigned->total() }}
                </button>
            </div>
            <div wire:click="setTab('unassigned')" class="@if($tabs['unassigned']) active @endif tab relative z-0 inline-flex filter-disabled">
                <button class="btn-border" type="button">
                    @lang('unassigned')
                </button>
                <button class="" type="button">
                    {{ $gateways_unassigned->total() }}
                </button>
            </div>
        </div>
    </div>

    {{-- list view --}}
    <div class="px-4 border-none">
        <ul class="divide-y-8 divide-transparent" role="list">

            @foreach($gateways as $key => $gateway)
                <livewire:settings.gateway-item :gateway="$gateway" :key="$gateway->dg_id" :fieldTranslations="$fieldTranslations" ></livewire:settings.gateway-item>
            @endforeach

{{--                @if($gateways->hasMorePages())--}}
{{--                    <div class="w-full justify-center relative">--}}
{{--                        <x-button.load-more wire:click.prevent="loadMore">@lang('Load more')</x-button.load-more>--}}
{{--                    </div>--}}
{{--                @endif--}}
        </ul>

        {{-- Add the loader here --}}
        <div
            x-data="{ isLoading: false }"
            x-show="isLoading"
            @loading.window="isLoading = true"
            @loading-complete.window="isLoading = false"
            class="flex justify-center items-center"
            style="padding-top: 4rem;"
        >
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>
    </div>

    {{-- advanced searchfilter --}}
    <div class="px-4 w-full block">
        @if($showFilters)
            <div class="w-full mx-auto pb-2 p-4 bg-gray-400 bg-opacity-20" id="gatewayFilter">
                {{__('Search Filter')}}
            </div>
        @endif
    </div>
    {{-- / advanced searchfilter --}}

    <div x-data="loadMore($wire)" x-init="init()"></div>

</div>

@push('scripts')
    <script>
        window.addEventListener("removeListItem", event => {
            document.getElementById(event.detail.item).remove();
        });

        function loadMore($wire) {
            return {
                canLoadMore: $wire.entangle('canLoadMore').defer,

                init() {
                    this.setupScrollEvent();
                },

                setupScrollEvent() {
                    window.addEventListener('scroll', this.handleScroll.bind(this));
                },

                handleScroll() {
                    if (this.canLoadMore && (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 10)) {
                        this.canLoadMore = false;
                        $wire.loadMore();
                    }
                }
            }
        }
    </script>
@endpush
