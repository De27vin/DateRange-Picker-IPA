<div class="w-full">
    <x-page.loading-indicator />
    <div wire:loading.delay.longest.class="blur-lg" class="mt-16 mx-auto pb-12 px-12">
            <x-page.header class="px-4">
                <x-slot name="title">@lang('Module Settings')</x-slot>
                <x-slot name="description">@lang('On this page you can configure settings for each device module. First, choose a module. Then the appropriate settings data are shown.')</x-slot>
            </x-page.header>

        @php natsort($moduleOptions); @endphp

        {{-- choose device type --}}
        <div class="px-4 bottom-underline pb-4 flex justify-end sm:items-center">
            <div class="search-group flex items-center sm:justify-end">
                <div class="relative w-full md:w-96 lg:w-128 pl-1 pr-0 mb-1 md:mb-0">
                    <x-forms.label for="module_name" :fallback="''">
                        @lang('Module')
                    </x-forms.label>
                    <x-input.select class="w-full" wire:model="option" name="option">
                        @foreach ($moduleOptions as $option => $label)
                            <option value="{{ $option }}">{{ $label }}</option>
                        @endforeach
                    </x-input.select>
                </div>
            </div>
        </div>

        @if($selectedModule != null)
        <div x-cloak x-data="{showFormFields: @this.entangle('showFormFields')}">
        <x-page.tabs-secondary
            :target="$selectedModule"
            defaultTab="fieldsVisibility"
            :tabs="[
                'fieldsVisibility' => __('Fields visibility'),
                'programmableSettings' => __('Programmable settings'),
                'advancedSettings' => __('Advanced settings'),
            ]"
            :verticalSpace="true"
            :buttonsOnTop="true"
        >
            <x-slot name="fieldsVisibilityButtons">
                @if($canWriteSettings && count($moduleFieldOptions))
                    <div class="flex w-full justify-end gap-2">
                        <x-button.primary wire:click="updateFieldOptions">@lang('update')</x-button.primary>
                        <x-button.secondary wire:click="cancelSettings('fieldsVisibility')">@lang('cancel')</x-button.secondary>
                    </div>
                @endif
            </x-slot>

            <x-slot name="programmableSettingsButtons">
                @if(count($accModProgrammableSettings))
                    <div class="flex w-full justify-end gap-2">
                        <x-button.primary wire:click.prevent="updateProgrammableSettings">@lang('update')</x-button.primary>
                        <x-button.secondary wire:click.prevent="cancelSettings('programmableSettings')">@lang('cancel')</x-button.secondary>
                    </div>
                @endif
            </x-slot>

            <x-slot name="advancedSettingsButtons">
                @if(count($accModAdvancedSettings))
                    <div class="flex w-full justify-end gap-2">
                        <x-button.primary wire:click.prevent="updateAdvancedSettings">@lang('update')</x-button.primary>
                        <x-button.secondary wire:click.prevent="cancelSettings('advancedSettings')">@lang('cancel')</x-button.secondary>
                    </div>
                @endif
            </x-slot>

            <x-slot name="fieldsVisibilitySlot">
                <p class="text-base py-2 px-4 mb-4 mx-1 text-white bg-color-new-400">@lang('Customize visibility for device form fields available for selected module.')</p>
                <ul>
                    @foreach($moduleFieldOptions as $settingKey => $settingItem)
                        @continue(!empty($settingItem['locked']))
                        <li wire:key="{{ $settingKey }}" class="">
                            <div class="flex w-full my-4 bg-white bg-opacity-30 border border-slate-300">
                                <div class="flex flex-col px-4 py-4 w-full">
                                    <div class="flex justify-between w-full pb-1 items-center">
                                        <span class="text-normal mb-0 text-gray-900">{{ $fieldTranslations[Str::replace('device_field_', '', strtolower($settingKey))] }}</span>

                                        <div class="flex flex-row lg:flex-row justify-end py-2 gap-4 mt-1">

                                            <div class="text-sm mr-2 flex">@lang('Active')</div>
                                            <div>
                                                @if($settingItem['locked'] == "true")
                                                    <div><x-monoicon.locked class="relative text-red-600" /></div>
                                                @else
                                                    <div>
                                                        <div wire:model="moduleFieldOptions.{{$settingKey}}.display.value" class="btn switch @if($settingItem['display']['value'] == 'true') active  bg-color-new-600 @else bg-gray-400 @endif " wire:click='toggleDisplay("{{$settingKey}}")' aria-checked="true" aria-describedby="privacy-option-1-description" aria-labelledby="privacy-option-1-label" role="switch">
                                                            <span class="sr-only">
                                                                @lang('Active')
                                                            </span>
                                                            <span class="@if($settingItem['display']['value'] == 'true') translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"  aria-hidden="true"></span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="text-sm">@lang('Mark as required')</div>
                                            <div>
                                                @if($settingItem['locked'] == "true")
                                                    <div><x-monoicon.locked class="relative text-red-600" /></div>
                                                @else
                                                    <div>
                                                        <div wire:model="moduleFieldOptions.{{$settingKey}}.required.value" class="btn switch @if($settingItem['required']['value'] == 'true') active  bg-color-new-600 @else bg-gray-400 @endif " wire:click='toggleRequired("{{$settingKey}}")' aria-checked="true" aria-describedby="privacy-option-1-description" aria-labelledby="privacy-option-1-label" role="switch">
                                                            <span class="sr-only">
                                                                @lang('Mark as required')
                                                            </span>
                                                            <span class="@if($settingItem['required']['value'] == 'true') translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"  aria-hidden="true"></span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-slot>

            <x-slot name="programmableSettingsSlot">
                <x-settings.programmable-settings
                    :programmableSettings="$accModProgrammableSettings"
                    :updateMethodName="'updateProgrammableSetting'"
                    :model="'accModProgrammableSettings'"
                    :target="$selectedModule['module_id']"
                ></x-settings.programmable-settings>
            </x-slot>

            <x-slot name="advancedSettingsSlot">
                <x-settings.advanced-settings
                    :advancedSettings="$accModAdvancedSettings"
                    :updateMethodName="'updateAdvancedSettings'"
                    :model="'accModAdvancedSettings'"
                ></x-settings.advanced-settings>
            </x-slot>
        </x-page.tabs-secondary>

        @endif
    </div>
    </div>
</div>