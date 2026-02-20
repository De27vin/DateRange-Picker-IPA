<div class="mt-16 mx-auto pb-12 px-12">
    <x-page.header class="px-4">
        <x-slot name="title">@lang('Device Gateway Management')</x-slot>
        <x-slot name="description">@lang('Create new or manage existing device gateways.')</x-slot>
    </x-page.header>

    {{-- Search and Actions --}}
    <div class="px-4 w-full search-group flex items-center sm:justify-between pb-4 mb-8 bottom-underline">
        <div class="relative w-1/2 pl-1 pr-8">
            <label class="default" for="gateway_search">@lang('Search')</label>
            <input wire:model="filters.search" class="" name="filters.search" type="text" placeholder="@lang('Search for gateway') ...">
        </div>

        <div class="relative pl-0 pr-1 mb-1">
            <span class="flex items-center space-x-4">
                @if($isAdmin)
                    <x-form.button class="" color="blue" type="button" wire:click.prevent="toggleNewGatewayForm">
                        <x-monoicon.add></x-monoicon.add>
                    </x-form.button>
                @endif

                <div x-data="{ showFormat: false }" class="relative">
                    <x-form.button
                        type="button"
                        @click="showFormat = !showFormat"
                        class="relative">
                        @lang('Export')
                    </x-form.button>

                    <!-- Format Selection Dropdown -->
                    <div
                        x-show="showFormat"
                        @click.away="showFormat = false"
                        x-cloak
                        class="absolute z-50 right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100"
                        style="min-width: 120px;"
                    >
                        <div class="py-1">
                            <a href="{{ route('export-gateways', ['tab' => array_search(true, $tabs), 'search' => $filters['search'], 'format' => 'csv']) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                               @click="showFormat = false"
                            >
                                CSV
                            </a>
                            <a href="{{ route('export-gateways', ['tab' => array_search(true, $tabs), 'search' => $filters['search'], 'format' => 'xlsx']) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                               @click="showFormat = false"
                            >
                                Excel (XLSX)
                            </a>
                        </div>
                    </div>
                </div>
            </span>
        </div>

    </div>

    {{-- New Gateway Form --}}
    <div class="@if(!$showNewGateway) hidden @endif px-4 pb-5 w-full mb-8 bottom-underline">
        <p class="description pb-8">@lang('Enter new gateway. The password for device gateway is generated, gets the status disabled.')</p>
        <form wire:submit.prevent="saveGateways">
            <div class="relative flex w-full mb-1 md:mb-0">
                <div class="w-1/2 mr-1">
                    <x-input.group for="newMacAddress" label="{{__('Mac Address')}}">
                        <x-input.text wire:model.defer="newMacAddress" class="w-full" name="newMacAddress" />
                    </x-input.group>
                    @error('newMacAddress')<div class="bg-danger-600 text-gray-50 dark:bg-danger-600 dark:text-gray-100 bg-opacity-40 font-medium flex items-center px-2 py-1 mx-auto pointer-events-none text-sm w-full error">{{ $message }}</div>@enderror
                </div>
                <div class="w-1/2">
                    <x-input.group for="newImei" label="{{__('Imei number')}}">
                        <x-input.text wire:model.defer="newImei" class="w-full" name="newImei" />
                    </x-input.group>
                    @error('newImei')<div class="bg-danger-600 text-gray-50 dark:bg-danger-600 dark:text-gray-100 bg-opacity-40 font-medium flex items-center px-2 py-1 mx-auto pointer-events-none text-sm w-full error">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="flex justify-end">
                <div class="pl-4"><x-button.secondary wire:click.prevent="toggleNewGatewayForm">@lang('Cancel')</x-button.secondary></div>
                <div class="pl-4"><x-button.primary type="submit">@lang('Save')</x-button.primary></div>
            </div>
        </form>
    </div>

    {{-- Tabs --}}
    <div class="list-filter my-8 px-4 space-x-8 space-y-4 lg:space-y-0 flex flex-col justify-start lg:flex-row lg:justify-between">
        <div class="flex justify-start">
            @php
                $tabList = [
                    'enabled' => 'gateways_enabled',
                    'disabled' => 'gateways_disabled',
                    'assigned' => 'gateways_assigned',
                    'unassigned' => 'gateways_unassigned'
                ];
            @endphp

            @foreach($tabList as $tab => $variable)
                <div wire:click="setTab('{{ $tab }}')" class="@if($tabs[$tab]) active @endif tab relative z-0 inline-flex filter-{{ $tab }}">
                    <button class="btn-border" type="button">@lang($tab)</button>
                    <button class="" type="button">{{ $$variable->total() }}</button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Gateway List --}}
    <div class="px-4 border-none">
        <ul class="divide-y-8 divide-transparent" role="list">
            @php
                if($tabs['disabled']) {
                    $gateways = $gateways_disabled;
                } elseif ($tabs['enabled']) {
                    $gateways = $gateways_enabled;
                } elseif ($tabs['assigned']) {
                    $gateways = $gateways_assigned;
                } elseif ($tabs['unassigned']) {
                    $gateways = $gateways_unassigned;
                }
            @endphp

            @forelse($gateways as $gateway)
                <li id="gateway{{$gateway->dg_id}}"
                    wire:key="gateway{{$gateway->dg_id}}"
                    x-data="{ mydelete{{$gateway->dg_id}}: false }"
                    class="list-item items-center">
                    <div class="relative devicebox block my-4 bg-white bg-opacity-50 hover:bg-white border border-slate-300">

                        <!-- Delete confirmation modal - show for both admin and site users -->
                        @if($isSite && !$gateway->device)
                            <div
                                x-show="mydelete{{$gateway->dg_id}}"
                                x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                                x-transition:enter-start="translate-x-full"
                                x-transition:enter-end="translate-x-0"
                                x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                                x-transition:leave-start="translate-x-0"
                                x-transition:leave-end="translate-x-full"
                                class="absolute top-0 right-0 w-48 bottom-0 z-50">
                                <div class="absolute top-0 left-0 bottom-0 w-1/2 h-full items-center">
                                    <button wire:click="deleteGateway({{$gateway->dg_id}})"
                                            type="button"
                                            class="inline-flex justify-center items-center p-0 m-0 rounded-none bg-red-600 h-full w-full text-white">
                                        <x-monoicon.delete />
                                    </button>
                                </div>
                                <div class="absolute top-0 right-0 bottom-0 w-1/2">
                                    <button @click="mydelete{{$gateway->dg_id}} = false"
                                            type="button"
                                            class="inline-flex justify-center items-center p-0 m-0 rounded-none bg-white h-full w-full text-gray-800">
                                        <x-monoicon.close />
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Gateway Information --}}
                        <div class="flex items-center pl-6 md:pl-4 py-4">
                            <div class="rows w-full" style="margin-right: -25px;">
                                <div class="top w-full items-center" style="grid-template-columns: repeat(7,minmax(0,1fr)); padding-top: 0;">
                                    {{-- Mac Address --}}
                                    <div class="flex flex-col">
                                        @if($gateway->dg_mac)
                                            <x-devices.box-input class="text-color-new-600 text-bold text-base" label="{{ __('Mac Address') }}">{{ $gateway->dg_mac }}</x-devices.box-input>
                                        @else
                                            <x-devices.box-input label="{{ __('Mac Address') }}">{{ __('Not applied') }}</x-devices.box-input>
                                        @endif
                                    </div>

                                    {{-- IMEI --}}
                                    <div class="flex flex-col">
                                        @if($gateway->dg_imei)
                                            <x-devices.box-input class="text-color-new-600 text-bold text-base" label="{{ __('Imei number') }}">{{ $gateway->dg_imei }}</x-devices.box-input>
                                        @else
                                            <x-devices.box-input label="{{ __('Imei number') }}">{{ __('Not applied') }}</x-devices.box-input>
                                        @endif
                                    </div>

                                    {{-- Password --}}
                                    <div class="flex flex-col">
                                        @if($editedGatewayIndex === $gateway->dg_id)
                                            <x-input.group :error="$errors->first('editedPasswordField')" required="required" for="editedPasswordField" label="Password">
                                                <x-input.text wire:model="editedPasswordField" class="w-full" required="required" name="editedPasswordField" />
                                            </x-input.group>
                                        @else
                                            <x-devices.box-input label="Password">{{ $gateway->dg_sippwd }}</x-devices.box-input>
                                        @endif
                                    </div>

                                    {{-- Type --}}
                                    <div class="flex flex-col">
                                        @if($gateway->device?->module)
                                            <x-devices.box-input class="text-bold text-base" label="{{__('Module')}}">
                                                {{ $gateway->device->module->module_desc ?? $gateway->device->module->module_name ?? '' }}
                                            </x-devices.box-input>
                                        @else
                                            <x-devices.box-input label="{{__('Module')}}">
                                                {{__('not assigned')}}
                                            </x-devices.box-input>
                                        @endif
                                    </div>

                                    {{-- Connected Site --}}
                                    <div class="flex flex-col">
                                        @if($gateway->device)
                                            <x-devices.box-input class="text-color-new-600 text-bold text-base" label="{{__('Connected Site')}}">
                                                <a href="/device-site/{{ $gateway->device->device_site->ds_id }}">
                                                    {{ $gateway->device->device_site->ds_name }}
                                                </a>
                                            </x-devices.box-input>
                                        @else
                                            <x-devices.box-input label="{{__('Connected Site')}}">
                                                {{__('not assigned')}}
                                            </x-devices.box-input>
                                        @endif
                                    </div>

                                    {{-- Connected Device --}}
                                    <div class="flex flex-col">
                                        @if($gateway->device)
                                            <x-devices.box-input class="text-color-new-600 text-bold text-base" label="{{__('Connected Device')}}">
                                                <a href="/device-site/{{ $gateway->device->device_site->ds_id }}">
                                                    {{ $gateway->device->device_equipment }}
                                                </a>
                                            </x-devices.box-input>
                                        @else
                                            <x-devices.box-input label="{{__('Connected Device')}}">
                                                {{__('not assigned')}}
                                            </x-devices.box-input>
                                        @endif
                                    </div>

                                    {{-- Expiry Status --}}
                                    <div class="flex flex-col">
                                        @php
                                            $color = ($gateway->is_valid ? 'green-600' : 'red-400');
                                        @endphp
                                        <p class="flex h-9 my-2 items-center text-sm text-medium">
                                            <button title="expire datetime" type="button" class="w-full h-6 m-0 flex justify-between items-center p-0 pr-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none focus:outline-none focus:ring-0" style="padding-right: 0;">
                                                <span class="px-3 hover:bg-gray-300 uppercase">{{ toUserTimezone($gateway->dg_expires) }}</span>
                                                <span title=" @if($color == 'red-400')not valid @else valid @endif " class="@if($color == 'red-400') expired @endif h-full w-12 flex justify-center items-center text-medium text-white bg-{{$color}} hover:bg-{{$color}}">
                                                    <x-monoicon.clock />
                                                </span>
                                            </button>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            @if ($editedGatewayIndex === $gateway->dg_id)
                                <div class="flex items-center py-2">
                                    <button wire:click.prevent="editGatewaySave({{$gateway->dg_id}}, '{{$editedGatewayField}}', '{{$editedPasswordField}}')" title="save" type="button" class="w-full h-6 m-0 flex justify-between items-center p-0 pr-0 border-none">
                                        <span class="h-full w-12 flex justify-center items-center text-medium text-white bg-green-600 hover:bg-green-800">
                                            <x-monoicon.check />
                                        </span>
                                    </button>
                                    <button wire:click="editGatewayCancel()" title="cancel" type="button" class="w-full h-6 m-0 flex justify-between items-center p-0 pr-0 border-none">
                                        <span class="h-full w-12 flex justify-center items-center text-medium text-white bg-gray-400 hover:bg-gray-600">
                                            <x-monoicon.close />
                                        </span>
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center py-2">
                                    <div class="boxitemDropdown z-20">
                                        <x-forms.actionmenu icon="options-vertical" :data="''">
                                            <x-forms.dropdown-item wire:click.prevent.stop="editGateway({{$gateway->dg_id}})">
                                                @lang('Edit ...')
                                            </x-forms.dropdown-item>
                                            @if($isAdmin)
                                                <x-forms.dropdown-item wire:click.prevent.stop="refreshPassword({{$gateway->dg_id}})">
                                                    @lang('Refresh password')
                                                </x-forms.dropdown-item>
                                            @endif
                                            @if($isSite && !$gateway->device)
                                                <x-forms.dropdown-item @click.prevent="$parent.mydelete{{$gateway->dg_id}} = true; open = false">
                                                    @lang('Delete') ...
                                                </x-forms.dropdown-item>
                                            @endif
                                        </x-forms.actionmenu>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @empty
                <div class="mt-12 p-4 text-center text-lg text-white bg-color-new-400 border border-slate-400 shadow-lg">
                    @lang('No results found')
                </div>
            @endforelse
        </ul>

        @if($canLoadMore)
            <div
                x-data="{ isLoading: false }"
                x-show="isLoading"
                @loading.window="isLoading = true"
                @loading-complete.window="isLoading = false"
                class="flex justify-center items-center pt-16">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>
        @endif
    </div>

    {{-- Original infinite scroll handler --}}
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