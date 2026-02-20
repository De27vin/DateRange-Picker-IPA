@php
    $languages = array_filter($this->languages, function($lang) {
        return $lang !== 'default';
    });
    $tile = min(4, count($languages));
@endphp
<div class="pb-12 mx-auto px-4">

    <x-page.header class="mt-8 mb-2 h-20">
        <x-slot name="title">@lang('Translations of form fields, settings labels and alert types')</x-slot>
        <x-slot name="description">@lang('Configure label translations of form fields for device, settings fields and global alert type configuration.')</x-slot>
    </x-page.header>

    <div x-data="{ openTab: 'form' }">
        <div class="w-full block lg:flex items-center mb-4">
            <div class="bottom-underline flex flex-grow lg:mr-8 my-4 justify-between bg-white bg-opacity-60">
                <div class="flex max-w-2xl space-x-4 px-2 py-1 pr-4">
                    <button x-on:click="openTab = 'form'" :class="{ 'bg-color-new text-white': openTab === 'form' }" class="flex-1 py-2 focus:outline-none focus:shadow-outline-blue transition-all duration-300 hover:bg-color-new hover:text-white">@lang('Form Fields')</button>
                    <button x-on:click="openTab = 'settings'" :class="{ 'bg-color-new text-white': openTab === 'settings' }" class="flex-1 py-2 focus:outline-none focus:shadow-outline-blue transition-all duration-300 hover:bg-color-new hover:text-white">@lang('Setting Fields')</button>
                    <button x-on:click="openTab = 'alerts'" :class="{ 'bg-color-new text-white': openTab === 'alerts' }" class="flex-1 py-2 focus:outline-none focus:shadow-outline-blue transition-all duration-300 hover:bg-color-new hover:text-white">@lang('Alert Types')</button>
                </div>
            </div>
            <div x-show="openTab === 'form'" class="flex space-x-4 justify-end items-center" style="margin-bottom: -2rem;">
                <x-button.primary wire:click="updateFieldTranslations" type="submit">@lang('update')</x-button.primary>
                <x-button.secondary wire:click="cancelSettings">@lang('cancel')</x-button.secondary>
            </div>

            <div x-show="openTab === 'settings'" class="flex space-x-4 justify-end items-center" style="margin-bottom: -2rem;">
                <x-button.primary wire:click.prevent="updateSettingTranslations">@lang('update')</x-button.primary>
                <x-button.secondary wire:click.prevent="cancelSettings">@lang('cancel')</x-button.secondary>
            </div>

            <div x-show="openTab === 'alerts'" class="flex space-x-4 justify-end items-center" style="margin-bottom: -2rem;">
                <x-button.primary wire:click.prevent="updateAlertTypes">@lang('update')</x-button.primary>
                <x-button.secondary wire:click.prevent="cancelSettings">@lang('cancel')</x-button.secondary>
            </div>
        </div>

        <div x-show="openTab === 'form'">
            <ul>
                @foreach($deviceFieldsTranslations as $settingKey => $settingItem)
                    <li class="">
                        <div class="flex w-full my-4 bg-white bg-opacity-20 border border-slate-300">
                            <div class="flex flex-col px-4 py-4 w-full">
                                <div class="flex justify-between w-full border-b border-gray-400 pb-4">
                                    <h2 class="text-base uppercase text-medium mb-0 text-gray-900">{{ __('settings.label.'.$settingKey) }}</h2>
                                </div>

                                <div class="flex flex-col md:flex-row justify-between w-full py-2">
                                    <div class="flex flex-col w-full md:flex-row flex-wrap">
{{--                                        <x-form.group :tile="$tile" class="mb-4 w-full">--}}
{{--                                            <x-form.label for="{{$settingKey}}.default" >--}}
{{--                                                {{ __('Default') }}--}}
{{--                                            </x-form.label>--}}
{{--                                            <x-input.text wire:model.defer="deviceFieldsTranslations.{{$settingKey}}.default"  type="text" name="{{$settingKey}}.default" value='{{ $settingItem["default"] }}' />--}}
{{--                                        </x-form.group>--}}
                                        @foreach($languages as $language)
                                            <x-form.group :tile="$tile" class="mb-4 w-full">
                                                <x-form.label for="{{$settingKey}}.{{$language}}" >
                                                    {{$language}}
                                                </x-form.label>
                                                <x-input.text wire:model.defer="deviceFieldsTranslations.{{$settingKey}}.{{$language}}"  type="text" value='{{ $settingItem["$language"] }}' />
                                            </x-form.group>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div x-show="openTab === 'settings'">
            <ul>
                @foreach($deviceSettingsTranslations as $setting => $settingTranslations)
                    <li class="">
                        <div class="flex w-full my-4 bg-white bg-opacity-20 border border-slate-300">
                            <div class="flex flex-col px-4 py-4 w-full">
                                <div class="flex justify-between w-full border-b border-gray-400 pb-4">
                                    <h2 class="text-base uppercase text-medium mb-0 text-gray-900">
                                        {{ __('settings.label.device_'.$setting) }}
                                    </h2>
                                </div>
                                <div class="flex flex-col md:flex-row justify-between w-full py-2">
                                    <div class="flex flex-col w-full md:flex-row flex-wrap">
{{--                                        @foreach($settingTranslations as $langCode => $langTranslation)--}}
{{--                                            @if(in_array($langCode, $languages) || $langCode == 'default')--}}
{{--                                            @if(in_array($langCode, $languages))--}}
{{--                                                <x-form.group :tile="$tile" class="mb-4 w-full">--}}
{{--                                                    <x-form.label for="{{$setting}}.{{$langCode}}" >--}}
{{--                                                        {{$langCode}}--}}
{{--                                                    </x-form.label>--}}
{{--                                                    <x-input.text wire:model.defer="deviceSettingsTranslations.{{$setting}}.{{$langCode}}"  type="text" value='{{$langTranslation}}' />--}}
{{--                                                </x-form.group>--}}
{{--                                            @endif--}}
{{--                                        @endforeach--}}

                                        @foreach($languages as $language)
                                            <x-form.group :tile="$tile" class="mb-4 w-full">
                                                <x-form.label for="{{$setting}}.{{$language}}" >
                                                    {{$language}}
                                                </x-form.label>
                                                <x-input.text wire:model.defer="deviceSettingsTranslations.{{$setting}}.{{$language}}"  type="text" value='{{ $settingTranslations["$language"] }}' />
                                            </x-form.group>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div x-show="openTab === 'alerts'">
            <x-alerts.alert-types></x-alerts.alert-types>
        </div>
    </div>
</div>


