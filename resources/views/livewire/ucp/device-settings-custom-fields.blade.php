<div>
        <x-page.tabs-secondary
            :target="$device"
            defaultTab="customFields"
            :tabs="[
                'customFields' => __('Custom fields'),
                'programmableSettings' => __('Programmable settings'),
                'nonProgrammableSettings' => __('Advanced settings'),
            ]"
            :verticalSpace="true"
            :buttonsOnTop="true"
        >

            <x-slot name="customFieldsButtons">
                @if($canWriteSettings && count($deviceCustomFields))
                    <div class="flex w-full justify-end gap-2">
                        <x-button.primary wire:click="updateDeviceCustomFields">@lang('Update')</x-button.primary>
                        <x-button.secondary type="button" onclick="window.dispatchEvent(new CustomEvent('closeModal_deviceSettingsCustomFields'))">@lang('Cancel')</x-button.secondary>
                    </div>
                @endif
            </x-slot>

            <x-slot name="programmableSettingsButtons">
                @if(count($deviceSettingsProgrammable))
                    <div class="flex w-full justify-end gap-2">
                        <x-button.primary wire:click="updateProgrammableSettings">@lang('update')</x-button.primary>
                        <x-button.secondary type="button" onclick="window.dispatchEvent(new CustomEvent('closeModal_deviceSettingsCustomFields'))">@lang('cancel')</x-button.secondary>
                    </div>
                @endif
            </x-slot>

            <x-slot name="nonProgrammableSettingsButtons">
                @if(count($deviceSettingsNonProgrammable))
                    <div class="flex w-full justify-end gap-2">
                        <x-button.primary wire:click="updateNonProgrammableSettings">@lang('update')</x-button.primary>
                        <x-button.secondary type="button" onclick="window.dispatchEvent(new CustomEvent('closeModal_deviceSettingsCustomFields'))">@lang('cancel')</x-button.secondary>
                    </div>
                @endif
            </x-slot>


            <x-slot name="customFieldsSlot">
                <form wire:submit.prevent="updateDeviceCustomFields">

                    <div class="md:flex flex-wrap mb-0">
                        <div class="block w-full">
                            <div class="ml-8 justify-between pb-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">

                                @forelse($deviceCustomFields as $customKey => $customField)
                                    <x-forms.grid-group class="mb-4">
                                        <x-forms.label for="{{$customKey}}" :fallback="''">
                                            {{ !empty($customField['translations'][$locale]) ? $customField['translations'][$locale] : $customField['name'] }}
                                            @if(!empty($customField['required']))
                                                <x-monoicon.required />
                                            @endif
                                        </x-forms.label>
                                        <x-input.text wire:model.defer="deviceCustomFields.{{$customKey}}.value" canWriteSettings="{{$canWriteSettings}}" type="text" name="{{$customKey}}"  />
                                    </x-forms.grid-group>
                                @empty
                                    <p class="col-span-4 text-sm py-2 px-4 mb-8 mx-1 text-white bg-color-new-400">@lang('No custom fields are configured for Devices.')</p>
                                @endforelse

                            </div>
                        </div>
                    </div>

                </form>
            </x-slot>

            <x-slot name="programmableSettingsSlot">
                <x-settings.programmable-settings
                    :programmableSettings="$deviceSettingsProgrammable"
                    :updateMethodName="'updateProgrammableSetting'"
                    :model="'deviceSettingsProgrammable'"
                    :target="$device"
                ></x-settings.programmable-settings>
            </x-slot>

            <x-slot name="nonProgrammableSettingsSlot">
                <x-settings.advanced-settings
                    :advancedSettings="$deviceSettingsNonProgrammable"
                    :updateMethodName="'updateNonProgrammableSettings'"
                    :model="'deviceSettingsNonProgrammable'"
                ></x-settings.advanced-settings>
            </x-slot>

        </x-page.tabs-secondary>


    <script>
        window.deviceSettingsLivewireId = '{{$this->id}}';
    </script>

</div>