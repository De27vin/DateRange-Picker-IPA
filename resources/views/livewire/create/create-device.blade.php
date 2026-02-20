{{--create-device.blade.php--}}
<div class="mx-auto w-full px-4 pb-5 font-medium">
    <div class="relative w-full text-sm">
        {{-- page header of alert types --}}
        <div class="block lg:flex sm:justify-between sm:items-center pb-8">
            <div class="page_header">
                <h1 class="title" id="message-heading">
                    @lang('Enter new device')
                </h1>
                <p class="description pb-8 lg:pb-0">
                    @lang('Fill in required fields. Depending on chosen device type and module different sets of fields are required')
                </p>
            </div>
        </div>
        {{-- </div> --}}

        @error('general')<div class="text-base text-white bg-red-400 p-4 my-4">{{ $message }}</div>@enderror

        <div class="md:flex flex-wrap mb-0 pb-4">
            <div class="block w-full">
                <div class="justify-between grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">

                    {{--MODULES INFO--}}
                    <div class="block mb-2 px-1 col-span-1 md:col-span-2 lg:col-span-4 pl-3">
                        <label class="text-base font-medium text-gray-900">@lang('Type and Module')</label>
                        <p class="text-sm leading-5 text-gray-500">@lang('Fill in type information of new device')</p>
                    </div>

                    <x-forms.grid-group class="mb-4">
                        <x-forms.label for="module" :fallback="''">
                            @lang('Device Type')
                            <x-monoicon.required/>
                        </x-forms.label>
                        <x-input.select class="w-full" wire:model.lazy="moduleType">
                            @foreach ($moduleTypeOptions as $moduleTypeId => $moduleTypeName)
                                <option value="{{ $moduleTypeId }}">{{ $moduleTypeName }}</option>
                            @endforeach
                        </x-input.select>
                        @error('moduleType')
                        <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner> @enderror
                    </x-forms.grid-group>

                    @if(!empty($moduleOptions))
                        <x-forms.grid-group class="mb-4">
                            <x-forms.label for="module" :fallback="''">
                                @lang('Device Module')
                                <x-monoicon.required/>
                            </x-forms.label>
                            <x-input.select class="w-full" wire:model.lazy="module">
                                @foreach ($moduleOptions as $moduleId => $moduleName)
                                    <option value="{{ $moduleId }}">{{ $moduleName }}</option>
                                @endforeach
                            </x-input.select>
                            @error('module')
                            <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner> @enderror
                        </x-forms.grid-group>
                    @endif

{{--                    @if(!empty($gatewayOptions))--}}
{{--                        <x-forms.grid-group class="mb-4">--}}
{{--                            <x-forms.label for="gateway" :fallback="''">--}}
{{--                                @lang('Device Gateway')--}}
{{--                            </x-forms.label>--}}
{{--                            <x-input.select class="w-full" wire:model.lazy="gateway">--}}
{{--                                @foreach ($gatewayOptions as $gatewayId => $gatewayMacOrImei)--}}
{{--                                    <option value="{{ $gatewayId }}">{{ $gatewayMacOrImei }}</option>--}}
{{--                                @endforeach--}}
{{--                            </x-input.select>--}}
{{--                            @error('gateway')--}}
{{--                            <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner> @enderror--}}
{{--                        </x-forms.grid-group>--}}
{{--                    @endif--}}


                    @if(!empty($gatewayOptions))
                        <x-forms.grid-group class="mb-4">
                            <x-forms.label for="gateway" :fallback="''">
                                @lang('Device Gateway')
                            </x-forms.label>
                            <x-input.searchable-select-v4 class="w-full" wire:model.lazy="gateway">
                                @foreach ($gatewayOptions as $gatewayId => $gatewayMacOrImei)
                                    <option value="{{ $gatewayId }}">{{ $gatewayMacOrImei }}</option>
                                @endforeach
                            </x-input.searchable-select-v4>
                            @error('gateway')
                            <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner> @enderror
                        </x-forms.grid-group>
                    @endif


                    {{--ADDRESS INFO--}}
{{--                    <div class="block mb-2 px-1 col-span-1 md:col-span-2 lg:col-span-4 pl-3">--}}
{{--                        <label class="text-base font-medium text-gray-900">@lang('Address Information')</label>--}}
{{--                        <p class="text-sm leading-5 text-gray-500">@lang('Fill in address information of new device')</p>--}}
{{--                    </div>--}}

{{--                    <x-forms.grid-group class="" tile="1">--}}
{{--                        <x-form.label for="addressFields.address_value">{{ __('address') }}--}}
{{--                            @if(in_array('address', $requiredFields))<x-monoicon.required/>@endif--}}
{{--                        </x-form.label>--}}
{{--                        <x-input.text wire:model.defer="addressFields.address_value" type="text" value="{{ $addressFields['address_value'] ?? '' }}"/>--}}
{{--                        @error('addressFields.address_value')--}}
{{--                        <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>--}}
{{--                        @enderror--}}
{{--                    </x-forms.grid-group>--}}

{{--                    <x-forms.grid-group class="" tile="1">--}}
{{--                        <x-form.label for="addressFields.location_postcode">--}}
{{--                            {{ __('postcode') }}--}}
{{--                            @if(in_array('address', $requiredFields))<x-monoicon.required/>@endif--}}
{{--                        </x-form.label>--}}
{{--                        <x-input.text wire:model.defer="addressFields.location_postcode" type="text" value="{{ $addressFields['location_postcode'] ?? '' }}"/>--}}
{{--                        @error('addressFields.location_postcode')--}}
{{--                        <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>--}}
{{--                        @enderror--}}
{{--                    </x-forms.grid-group>--}}

{{--                    <x-forms.grid-group class="" tile="1">--}}
{{--                        <x-form.label for="addressFields.location_value">--}}
{{--                            {{ __('location') }}--}}
{{--                            @if(in_array('address', $requiredFields))<x-monoicon.required/>@endif--}}
{{--                        </x-form.label>--}}
{{--                        <x-input.text wire:model.defer="addressFields.location_value" type="text" value="{{ $addressFields['location_value'] ?? '' }}"/>--}}
{{--                        @error('addressFields.location_value')--}}
{{--                        <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>--}}
{{--                        @enderror--}}
{{--                    </x-forms.grid-group>--}}

{{--                    <x-forms.grid-group class="" tile="1">--}}
{{--                        <x-forms.label for="addressFields.location_country_id" :fallback="''">--}}
{{--                            @lang('country')--}}
{{--                            @if(in_array('address', $requiredFields))<x-monoicon.required/>@endif--}}
{{--                        </x-forms.label>--}}
{{--                        <x-input.select class="w-full" wire:model.defer="addressFields.location_country_id" value="{{ $addressFields['location_country_id'] ?? '' }}">--}}
{{--                            @foreach ($countries as $id => $label)--}}
{{--                                <option @if(isset($location) && intval($location->location_country_id) == $id) selected="selected" @endif value="{{ $id }}">{{ $label }}</option>--}}
{{--                            @endforeach--}}
{{--                        </x-input.select>--}}
{{--                        @error('addressFields.location_country_id')--}}
{{--                        <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>--}}
{{--                        @enderror--}}
{{--                    </x-forms.grid-group>--}}


                    {{--DEVICE INFO--}}
                    @if(!empty($deviceFields))
                        <div class="block mb-2 px-1 col-span-1 md:col-span-2 lg:col-span-4 pl-3">
                            <label class="text-base font-medium text-gray-900">@lang('Device Information')</label>
                            <p class="text-sm leading-5 text-gray-500">@lang('Fill in device information of new device')</p>
                        </div>

                        @foreach($deviceFields as $field => $value)
                            @continue($field === 'labels')
                            <x-forms.grid-group class="mb-4">
                                <x-forms.label for="{{$field}}" :fallback="''">
                                    {{ $fieldTranslations[$field] }}
                                    @if(in_array($field, $requiredFields))<x-monoicon.required/>@endif
                                </x-forms.label>
                                <x-input.text wire:model.lazy="deviceFields.{{$field}}" type="text"/>
                                @error('deviceFields.'.$field)
                                <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                                @enderror
                            </x-forms.grid-group>
                        @endforeach
                    @endif

                </div>
            </div>

{{--            HIDE LABELS--}}
{{--            --}}{{--LABELS INFO--}}
{{--            @if(array_key_exists('labels', $deviceFields))--}}
{{--                <div class="block mb-2 px-1 col-span-1 md:col-span-2 lg:col-span-4 pl-3">--}}
{{--                    <label class="text-base font-medium text-gray-900">@lang('Labels')</label>--}}
{{--                    <p class="text-sm leading-5 text-gray-500">@lang('Fill in labels of new device')</p>--}}
{{--                </div>--}}

{{--                <div class="pl-1 w-full mx-auto relative flex-auto pr-0">--}}
{{--                    <div class="relative flex justify-between items-center">--}}
{{--                        <x-groups.dropdown-node :groups="$labelOptions"></x-groups.dropdown-node>--}}
{{--                        <div class="groups-container m-0 pl-36">--}}
{{--                            @if(in_array('labels', $requiredFields))<x-monoicon.required/>@endif--}}
{{--                            @foreach($labels as $deviceLabel)--}}
{{--                                <x-groups.active-node :id="$deviceLabel['dl_id']">{{$deviceLabel['dl_name']}}</x-groups.active-node>--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    @error('deviceFields.labels')<div class="bg-danger-600 text-gray-50 dark:bg-danger-600 dark:text-gray-100 bg-opacity-40 font-medium flex items-center px-2 py-1 mx-auto pointer-events-none text-sm w-full error">--}}
{{--                        {{ $message }}--}}
{{--                    </div>@enderror--}}
{{--                </div>--}}
{{--            @endif--}}

        </div>

        <div class="flex pt-4 mt-4 justify-end gap-4">
            <x-button.secondary wire:click="cancel">@lang('Cancel')</x-button.secondary>
            <x-button.primary wire:click="create">@lang('Create')</x-button.primary>
        </div>

    </div>


</div>