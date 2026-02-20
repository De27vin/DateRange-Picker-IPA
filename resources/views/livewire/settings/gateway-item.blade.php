{{--DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED DEPRECAED --}}
<div>
{{--    @dump($gateway->dg_sippwd)--}}
    <li
        id="gateway{{$gateway->dg_id}}"
        wire:key="gateway{{$gateway->dg_id}}"
        x-cloak
        x-data="{ mydelete{{$gateway->dg_id}}: false }"
        class="list-item items-center">
        <div class="relative devicebox block my-4 bg-white bg-opacity-50 hover:bg-white border border-slate-300">

            @if($isAdmin)
                <div
                        x-show="mydelete{{$gateway->dg_id}}"
                        :x-ref="gateway{{$gateway->dg_id}}"
                        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                        class="absolute top-0 right-0 w-48 bottom-0 z-50">
                    <div class="absolute top-0 left-0 bottom-0 w-1/2 h-full items-center">
                        <button wire:click="deleteGateway({{$gateway->dg_id}})" type="button" class="inline-flex justify-center items-center p-0 m-0 rounded-none bg-red-600 h-full w-full text-white">
                            <x-monoicon.delete />
                        </button>
                    </div>
                    <div class="absolute top-0 right-0 bottom-0 w-1/2">
                        <button x-on:click="mydelete{{$gateway->dg_id}} = false" type="button" class="inline-flex justify-center items-center p-0 m-0 rounded-none bg-white h-full w-full text-gray-800">
                            <x-monoicon.close />
                        </button>
                    </div>
                </div>
            @endif

            {{-- UPPER ROW --}}
            <div class="flex items-center pl-6 md:pl-4 py-4">
                <div class="rows w-full" style="margin-right: -25px;">
                    <div class="top w-full items-center" style="grid-template-columns: repeat(6,minmax(0,1fr)); padding-top: 0;">
                        <div class="flex flex-col">
                            @if($gateway->dg_mac)
                                <x-devices.box-input class="text-color-new-600 text-bold text-base" label="{{ __('Mac Address') }}">{{ $gateway->dg_mac }}</x-devices.box-input>
                            @else
                                <x-devices.box-input label="{{ __('Mac Address') }}">{{ __('Not applied') }}</x-devices.box-input>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            @if($gateway->dg_imei)
                                <x-devices.box-input class="text-color-new-600 text-bold text-base" label="{{ __('Imei number') }}">{{ $gateway->dg_imei }}</x-devices.box-input>
                            @else
                                <x-devices.box-input label="{{ __('Imei number') }}">{{ __('Not applied') }}</x-devices.box-input>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            @if ($editedGatewayIndex === $gateway->dg_id)
                                <x-input.group :error="$errors->first('editedPasswordField')" required="required" for="editedPasswordField" label="{{ __('Password') }}">
                                    <x-input.text wire:model="editedPasswordField"  class="w-full" required="required" name="editedPasswordField" />
                                </x-input.group>
                            @else
                                <x-devices.box-input label="{{ __('Password') }}">{{ $gateway->dg_sippwd }}</x-devices.box-input>
                            @endif
                        </div>
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
                        <div class="flex flex-col">
                            @php
                                $color = ($gateway->is_valid ? 'green-600' : 'red-400');
                            @endphp
                            <p class="flex h-9 my-2 items-center text-sm text-medium ">
                                <button title="expire datetime" type="button" class="w-full h-6 m-0 flex justify-between items-center p-0 pr-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none focus:outline-none focus:ring-0" style="padding-right: 0;">
                                    <span class="px-3 hover:bg-gray-300 uppercase">{{ toUserTimezone($gateway->dg_expires) }}</span>
                                    <span title=" @if($color == 'red-400')not valid @else valid @endif " class="@if($color == 'red-400') expired @endif h-full w-12 flex  justify-center items-center text-medium text-white bg-{{$color}} hover:bg-{{$color}}">
									<x-monoicon.clock />
								</span>
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
                @if ($editedGatewayIndex === $gateway->dg_id)
                    <div class="flex items-center py-2 ">
                        <button wire:click.prevent="editGatewaySave({{$gateway->dg_id}}, '{{$editedGatewayField}}', '{{$editedPasswordField}}')" title="save" type="button" class="w-full h-6 m-0 flex justify-between items-center p-0 pr-0 border-none">
                            <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-green-600 hover:bg-green-800">
                                <x-monoicon.check />
                            </span>
                        </button>
                        <button wire:click="editGatewayCancel()" title="cancel" type="button" class="w-full h-6 m-0 flex justify-between items-center p-0 pr-0 border-none">
                            <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-gray-400 hover:bg-gray-600">
                                <x-monoicon.close />
                            </span>
                        </button>
                    </div>
                @else
                    <div class="flex items-center py-2">

                        <div class="boxitemDropdown z-20">
                            <x-forms.actionmenu icon="options-vertical" :data="''">
                                <x-forms.dropdown-item wire:click.prevent.stop="editGateway({{$gateway->dg_id}})">@lang('Edit ...')</x-forms.dropdown-item>
                                @if($isAdmin)
                                    <x-forms.dropdown-item wire:click.prevent.stop="refreshPassword({{$gateway->dg_id}})">@lang('Refresh password')</x-forms.dropdown-item>
                                @endif
                                @if($isSite && !$gateway->device)
                                    <x-forms.dropdown-item x-on:click.prevent="open=false;$parent.mydelete{{$gateway->dg_id}}=true;">@lang('Delete') ...</x-forms.dropdown-item>
                                @endif
                            </x-forms.actionmenu>

                        </div>
                    </div>
                @endif
            </div>

        </div>
    </li>

    <style>
        .expired {
            background-color: rgb(248 113 113);
        }
    </style>
</div>
