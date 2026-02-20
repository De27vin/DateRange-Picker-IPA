@props(['currentCustomFields', 'newCustomFields', 'languages', 'icons', 'target', 'iconModalTarget', 'deleteModalTarget'])

<div>
    {{-- Auto-save indicator --}}
    <div class="mb-4 text-sm text-gray-600">
        <i class="f7-icons inline-block mr-1">info_circle</i>
        @lang('Toggle settings for existing fields are saved automatically. New fields require clicking Update.')
    </div>

    <ul>
        @foreach($currentCustomFields as $key => $currentField)
            <li wire:key="current-{{ $target }}-{{ $key }}" class="">

                <div class="flex w-full my-4 bg-white bg-opacity-20 border border-slate-300">
                    <div class="flex flex-col px-4 py-2 w-full">
                        <div class="flex flex-col md:flex-row justify-between w-full py-2">
                            <div class="flex flex-col w-full md:flex-row flex-wrap">

                                {{-- default name --}}
                                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                                    <x-input.group for="name" label="{{__('Default name')}}" required="required" :error="$errors->first('currentCustomFields'.$target.'.'.$key.'.name')" :errNegMar="true">
                                        <x-input.text wire:model.defer="currentCustomFields{{$target}}.{{$key}}.name" class="w-full" required="required" />
                                    </x-input.group>
                                </div>

                                @foreach($languages as $lang)
                                    {{-- translations --}}
                                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                                        <x-input.group for="{{$lang}}" label="{{__($lang.' name')}}" :error="$errors->first('currentCustomFields'.$target.'.'.$key.'.translations.'.$lang)" :errNegMar="true">
                                            <x-input.text wire:model.defer="currentCustomFields{{$target}}.{{$key}}.translations.{{$lang}}" class="w-full"/>
                                        </x-input.group>
                                    </div>
                                @endforeach

                                {{-- icon input --}}
                                <div wire:click="showIconModal([{{$key}}, 'current', '{{$target}}'])" class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                                    <x-input.group for="icon" label="{{__('Chose icon')}}" :error="$errors->first('currentCustomFields'.$target.'.'.$key.'.icon')" :errNegMar="true">

                                        <div class="f7-icons-wrapper" style="position: absolute; top: 1.4rem; left: 0.5rem;">
                                            <i class="f7-icons md">{{ $currentField['icon'] ?? null }}</i>
                                        </div>

                                        <x-input.text wire:model.defer="currentCustomFields{{$target}}.{{$key}}.icon" class="w-full cursor-pointer" style="padding-left: 3rem" required="required"/>
                                    </x-input.group>
                                </div>

                                <div class="flex items-center ml-auto mr-1">

                                    {{-- dashboard display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('Compact view')</div>
                                        <div wire:model="currentCustomFields{{$target}}.{{$key}}.dashboard"
                                             class="btn switch @if(!empty($currentField['dashboard'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldDashboard("{{$key}}", "current", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($currentField['dashboard'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>

                                    {{-- equipment display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('Equipment view')</div>
                                        <div wire:model="currentCustomFields{{$target}}.{{$key}}.equipment"
                                             class="btn switch @if(!empty($currentField['equipment'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldEquipment("{{$key}}", "current", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($currentField['equipment'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>

                                    {{-- parrot app display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('Parrot App')</div>
                                        <div wire:model="currentCustomFields{{$target}}.{{$key}}.parrot_app"
                                             class="btn switch @if(!empty($currentField['parrot_app'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldParrotApp("{{$key}}", "current", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($currentField['parrot_app'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>

                                    @if($target === 'Device')
                                    {{-- qr code display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('QR Code')</div>
                                        <div wire:model="currentCustomFields{{$target}}.{{$key}}.qr_code"
                                             class="btn switch @if(!empty($currentField['qr_code'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldQrCode("{{$key}}", "current", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($currentField['qr_code'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- required display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('Required')</div>
                                        <div wire:model="currentCustomFields{{$target}}.{{$key}}.required"
                                             class="btn switch @if(!empty($currentField['required'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldRequired("{{$key}}", "current", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($currentField['required'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>

                                    {{-- delete button --}}
                                    <div wire:click="deleteCurrentField('{{$key}}', '{{$target}}')" class="justify-center items-center h-14 w-14 bg-red-300 cursor-pointer text-white f7-icons size-56" style="display: flex; font-size: 44px;">
                                        minus
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </li>
        @endforeach


        @foreach($newCustomFields as $key => $newField)
            <li wire:key="new-{{ $target }}-{{ $key }}" class="">

                <div class="flex w-full my-4 bg-white bg-opacity-20 border border-slate-300">
                    <div class="flex flex-col px-4 py-2 w-full">
                        <div class="flex flex-col md:flex-row justify-between w-full py-2">
                            <div class="flex flex-col w-full md:flex-row flex-wrap">

                                {{-- default name --}}
                                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 md:mb-0">
                                    <x-input.group for="name" label="{{__('Default name')}}" required="required" :error="$errors->first('newCustomFields'.$target.'.'.$key.'.name')" :errNegMar="true">
                                        <x-input.text wire:model.defer="newCustomFields{{$target}}.{{$key}}.name" class="w-full" required="required" />
                                    </x-input.group>
                                </div>

                                @foreach($languages as $lang)
                                    {{-- translations --}}
                                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                                        <x-input.group for="{{$lang}}" label="{{__($lang.' name')}}" :error="$errors->first('newCustomFields'.$target.'.'.$key.'.'.$lang)" :errNegMar="true">
                                            <x-input.text wire:model.defer="newCustomFields{{$target}}.{{$key}}.translations.{{$lang}}" class="w-full"/>
                                        </x-input.group>
                                    </div>
                                @endforeach

                                {{-- icon input --}}
                                <div wire:click="showIconModal([{{$key}}, 'new', '{{$target}}'])" class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                                    <x-input.group for="icon" label="{{__('Chose icon')}}" :error="$errors->first('newCustomFields'.$target.'.'.$key.'.icon')" :errNegMar="true">

                                        <div class="f7-icons-wrapper" style="position: absolute; top: 1.4rem; left: 0.5rem;">
                                            <i class="f7-icons md">{{ $newField['icon'] ?? null }}</i>
                                        </div>

                                        <x-input.text wire:model.defer="newCustomFields{{$target}}.{{$key}}.icon" class="w-full cursor-pointer" style="padding-left: 3rem" required="required"/>
                                    </x-input.group>
                                </div>

                                <div class="flex items-center ml-auto mr-1">

                                    {{-- dashboard display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('Compact view')</div>
                                        <div wire:model="newCustomFields{{$target}}.{{$key}}.dashboard"
                                             class="btn switch @if(!empty($newField['dashboard'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldDashboard("{{$key}}", "new", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($newField['dashboard'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>

                                    {{-- equipment display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('Equipment view')</div>
                                        <div wire:model="newCustomFields{{$target}}.{{$key}}.equipment"
                                             class="btn switch @if(!empty($newField['equipment'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldEquipment("{{$key}}", "new", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($newField['equipment'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>

                                    {{-- parrot app display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('Parrot App')</div>
                                        <div wire:model="newCustomFields{{$target}}.{{$key}}.parrot_app"
                                             class="btn switch @if(!empty($newField['parrot_app'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldParrotApp("{{$key}}", "new", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($newField['parrot_app'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>

                                    @if($target === 'Device')
                                    {{-- qr code display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('QR Code')</div>
                                        <div wire:model="newCustomFields{{$target}}.{{$key}}.qr_code"
                                             class="btn switch @if(!empty($newField['qr_code'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldQrCode("{{$key}}", "new", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($newField['qr_code'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- required display --}}
                                    <div class="flex mr-6">
                                        <div class="text-sm mr-2 flex">@lang('Required')</div>
                                        <div wire:model="newCustomFields{{$target}}.{{$key}}.required"
                                             class="btn switch @if(!empty($newField['required'])) active  bg-color-new-600 @else bg-gray-400 @endif"
                                             wire:click='toggleFieldRequired("{{$key}}", "new", "{{$target}}")'
                                             wire:loading.attr="disabled"
                                             wire:loading.class="opacity-50 cursor-not-allowed"
                                             x-data="{ clicking: false }"
                                             @click="if(clicking) return; clicking = true; setTimeout(() => clicking = false, 1000)">
                                            <span class="@if(!empty($newField['required'])) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" ></span>
                                        </div>
                                    </div>

                                    {{-- delete button --}}
                                    <div wire:click="deleteNewField('{{$key}}', '{{$target}}')" class="justify-center items-center h-14 w-14 bg-red-300 cursor-pointer text-white f7-icons size-56" style="display: flex; font-size: 44px;">
                                        minus
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </li>
        @endforeach


        <div class="flex justify-between mr-1">
            <div wire:click="insertNewField('{{$target}}')" class="justify-center items-center h-14 w-14 bg-gray-400 cursor-pointer text-white f7-icons size-56" style="display: flex; font-size: 44px;">
                plus
            </div>

            @if(count($currentCustomFields) || count($newCustomFields))
                <x-button.primary wire:click="saveCustomFields('{{$target}}')">@lang('update')</x-button.primary>
            @endif
        </div>

        {{-- ICON MODAL --}}
        <x-modal.scrollable-livewire :header="__('Select Icon')" :isModalOpenProp="'showIconModal'">
            <div class="flex flex-wrap">
                @foreach($icons as $icon)
                    <div wire:click="chooseIcon('{{$icon}}')" class="@if(!empty($iconModalTarget['current_icon']) && $iconModalTarget['current_icon'] === $icon) text-white bg-color-new-400 @endif border p-1 m-1 flex justify-center items-center f7-icons-wrapper hover:text-white hover:bg-color-new-400 cursor-pointer">
                        <i class="f7-icons xl">
                            {{$icon}}
                        </i>
                    </div>
                @endforeach
            </div>
        </x-modal.scrollable-livewire>

        {{-- DELETE MODAL --}}
        <x-modal.confirmation wire:model="showDeleteModal">
            <x-slot name="title">@lang('Delete Existing Custom Field')</x-slot>

            <x-slot name="content">
                <div class="py-8 text-cool-gray-700">@lang('This field already contains assigned values. Are you sure you want to delete it?')</div>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click.prevent.self="$set('showDeleteModal', false)">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary  wire:click.prevent.self="deleteCurrentField('{{ $deleteModalTarget[0] ?? null }}', '{{ $deleteModalTarget[1] ?? null }}', false)">{{ __('Delete') }}</x-button.primary>
            </x-slot>
        </x-modal.confirmation>

        {{-- PARROT APP SWITCH MODAL --}}
        <x-modal.confirmation-new wire:model="showParrotAppSwitchModal" maxWidth="md">
            <x-slot name="title">@lang('Switch Parrot App Display')</x-slot>

            <x-slot name="content">
                <div class="py-8 text-cool-gray-700">
                    @if($this->currentParrotAppField)
                        @lang('The field ":field" currently has Parrot App display enabled. Do you want to switch it to this field?', ['field' => $this->currentParrotAppField['name']])
                    @else
                        @lang('Do you want to enable Parrot App display for this field?')
                    @endif
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click.prevent="cancelParrotAppSwitch">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary wire:click.prevent="confirmParrotAppSwitch">{{ __('Confirm') }}</x-button.primary>
            </x-slot>
        </x-modal.confirmation-new>

        {{-- QR CODE SWITCH MODAL --}}
        <x-modal.confirmation-new wire:model="showQrCodeSwitchModal" maxWidth="md">
            <x-slot name="title">@lang('QR Code Display')</x-slot>

            <x-slot name="content">
                <div class="py-8 text-cool-gray-700">
                    @if($this->currentQrCodeField)
                        @lang('Are you sure you want to mark this field as QR Code? The field ":field" currently has QR Code display enabled.', ['field' => $this->currentQrCodeField['name']])
                    @else
                        @lang('Are you sure you want to mark this field as QR Code?')
                    @endif
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click.prevent="cancelQrCodeSwitch">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary wire:click.prevent="confirmQrCodeSwitch">{{ __('Confirm') }}</x-button.primary>
            </x-slot>
        </x-modal.confirmation-new>

    </ul>
</div>