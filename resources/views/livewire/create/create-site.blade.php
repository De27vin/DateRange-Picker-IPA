{{-- create-site.blade.php --}}
<div class="mt-12 mx-auto w-full px-12 pb-5 pt-8 font-medium" style="width: 100%;">
    <div class="relative w-full text-sm">
        {{-- page header of alert types --}}
        <div class="bottom-underline block lg:flex sm:justify-between sm:items-center pb-2 mb-8">
            <div class="page_header">
                <h1 class="title" id="message-heading">
                    @lang('Create new Site')
                </h1>
                <p class="description pb-8 lg:pb-0">
                    @lang('Description of site and device create page' )
                </p>
            </div>
        </div>
        {{-- </div> --}}

        @php
            natsort($moduleOptions);
            natsort($countries);
        @endphp

        <div class="md:flex flex-wrap mb-0 bottom-underline">
            <div class="block w-full">
                <div class="justify-between pb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">

                    {{--BASIC INFO--}}
                    <div class="block mb-2 px-1 col-span-1 md:col-span-2 lg:col-span-4 pl-3">
                        <label class="text-base font-medium text-gray-900">@lang('Basic Information')</label>
                        <p class="text-sm leading-5 text-gray-500">@lang('Fill in basic information of new site')</p>
                    </div>

                    <x-forms.grid-group class="mb-4">
                        <x-forms.label for="module" :fallback="''">
                            @lang('Module')
                            <x-monoicon.required/>
                        </x-forms.label>
                        <x-input.select class="w-full" wire:model.lazy="module" name="module">
                            @foreach ($moduleOptions as $moduleId => $moduleName)
                                <option value="{{ $moduleId }}">{{ $moduleName }}</option>
                            @endforeach
                        </x-input.select>
                        @error('module')
                        <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner> @enderror
                    </x-forms.grid-group>

                    <x-forms.grid-group class="mb-4">
                        <x-forms.label for="name" :fallback="''">
                            @lang('Name')
                        </x-forms.label>
                        <x-input.text wire:model.defer="name" type="text" name="name"/>
                        @error('name')
                        <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                        @enderror
                    </x-forms.grid-group>

                    @if(!empty($fieldSettings['link']['visible']))
                        <x-forms.grid-group class="mb-4">
                            <x-forms.label for="link" :fallback="''">
                                @lang('Link')
                                @if($fieldSettings['link']['required'])<x-monoicon.required/>@endif
                            </x-forms.label>
                            <x-input.text wire:model.defer="link" type="text" name="link"/>
                            @error('link')
                            <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                            @enderror
                        </x-forms.grid-group>
                    @endif

                    {{--ADDRESS INFO--}}
                    @if(!empty($fieldSettings['address']['visible']))
                        <div class="block mb-2 px-1 col-span-1 md:col-span-2 lg:col-span-4 pl-3">
                            <label class="flex text-base font-medium text-gray-900">@lang('Address Information')
                                @if($addressRequired)<x-monoicon.required class="ml-6 h-5 w-5 text-red-600"/>@endif
                            </label>
                            <p class="text-sm leading-5 text-gray-500">@lang('Fill in address information of new site')</p>
                        </div>

                        <x-forms.grid-group class="" tile="1">
                            <x-form.label for="addressFields.address_value">{{ __('address') }}
                            </x-form.label>
                            <x-input.text wire:model.defer="addressFields.address_value" type="text" value="{{ $addressFields['address_value'] ?? '' }}"/>
                            @error('addressFields.address_value')
                            <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                            @enderror
                        </x-forms.grid-group>

                        <x-forms.grid-group class="" tile="1">
                            <x-form.label for="addressFields.location_postcode">
                                {{ __('postcode') }}
                            </x-form.label>
                            <x-input.text wire:model.defer="addressFields.location_postcode" type="text" value="{{ $addressFields['location_postcode'] ?? '' }}"/>
                            @error('addressFields.location_postcode')
                            <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                            @enderror
                        </x-forms.grid-group>

                        <x-forms.grid-group class="" tile="1">
                            <x-form.label for="addressFields.location_value">
                                {{ __('location') }}
                            </x-form.label>
                            <x-input.text wire:model.defer="addressFields.location_value" type="text" value="{{ $addressFields['location_value'] ?? '' }}"/>
                            @error('addressFields.location_value')
                            <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                            @enderror
                        </x-forms.grid-group>

                        <x-forms.grid-group class="" tile="1">
                            <x-forms.label for="addressFields.location_country_id" :fallback="''">
                                @lang('country')
                            </x-forms.label>
                            <x-input.select class="w-full" wire:model.defer="addressFields.location_country_id" value="{{ $addressFields['location_country_id'] ?? '' }}">
                                @foreach ($countries as $id => $label)
                                    <option @if(isset($location) && intval($location->location_country_id) == $id) selected="selected" @endif value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </x-input.select>
                            @error('addressFields.location_country_id')
                            <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                            @enderror
                        </x-forms.grid-group>
                    @endif

                    {{--NUMBERS--}}
                    @if(!empty($fieldSettings['numbers']['visible']))
                        <div class="block mb-2 px-1 col-span-1 md:col-span-2 lg:col-span-4 pl-3">
                            <label class="flex text-base font-medium text-gray-900">@lang('Numbers Information')
                                @if($numbersRequired)<x-monoicon.required class="ml-6 h-5 w-5 text-red-600"/>@endif
                            </label>
                            <p class="text-sm leading-5 text-gray-500">@lang('Fill in numbers information of new site')</p>
                        </div>

                        @foreach($numberTypes as $numberType)
                            @php $numberType = strtolower($numberType) @endphp
                            @continue(!in_array($numberType, ['pstn', 'sim', 'sip', 'pbx']))

                            <x-forms.grid-group class="mb-4">
                                <x-forms.label for="{{$numberType}}" :fallback="''">
                                    {{ $fieldTranslations[$numberType] }}
                                </x-forms.label>
                                @if($numberType === 'sip')
                                    <x-input.searchable-select-v4 class="w-full" wire:model.defer="sip">
                                        @foreach ($sipOptions as $numberId => $numberValue)
                                            <option value="{{ $numberValue }}">{{ $numberValue }}</option>
                                        @endforeach
                                    </x-input.searchable-select-v4>
                                @else
                                    <x-input.text wire:model.defer="{{$numberType}}" type="text" name="{{$numberType}}"/>
                                @endif
                                @error($numberType)
                                <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                                @enderror
                            </x-forms.grid-group>
                        @endforeach

                        <div class="flex w-full ml-2 justify-start" style="align-items: self-end;">
                            <input wire:model.defer="copyNumberToCli" class="uiswitch uiswitch-new" type="checkbox" />
                            <label class="text-sm ml-2">{{ __('Update CLI') }}</label>

                            <div class="f7-icons-wrapper tt" style="height: 1.6rem; cursor: help;">
                                <i class="f7-icons sm tts">question_circle</i>
                                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm">{{ __('use primary phone number as CLI settings (call.alarm.route1.cli.number & call.outbound.trunk.cli.number)') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- CUSTOMS - TO MODIFY FOR NEW CUSTOMS --}}
                    @if(!empty($fieldSettings['tech']['visible']) || !empty($fieldSettings['custom']['visible']))
                        <div class="block mb-2 px-1 col-span-1 md:col-span-2 lg:col-span-4 pl-3">
                            <label class="text-base font-medium text-gray-900">@lang('Custom fields')</label>
                            <p class="text-sm leading-5 text-gray-500">@lang('Fill in custom fields of new site')</p>
                        </div>

                        @foreach($customFields as $field => $value)
                            @continue(empty($fieldSettings[$field]['display']))
                            <x-forms.grid-group class="" tile="1">
                                <x-form.label for="customFields.{{$field}}">{{ __($field) }}
                                    @if($fieldSettings[$field]['required'])<x-monoicon.required/>@endif
                                </x-form.label>
                                <x-input.text wire:model.defer="customFields.{{$field}}" type="text" value="{{ $value ?: '' }}"/>
                                @error('customFields.'.$field)
                                <x-form.banner color="danger" slim="false" class="w-full error">{{ $message }}</x-form.banner>
                                @enderror
                            </x-forms.grid-group>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>

        <div class="flex pt-4 mt-4 justify-end">
            <x-button.secondary wire:click="cancel">@lang('Cancel')</x-button.secondary>
            <x-button.primary wire:click="create">@lang('Create')</x-button.primary>
        </div>

    </div>
</div>