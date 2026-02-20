{{--site-dettinigs-cutoms-fields.blade.php--}}
<div>

    @php uasort($deviceSiteSettingsProgrammable, fn ($a, $b) => strcmp(strtolower($a['translation']), strtolower($b['translation']))); @endphp
    @php uasort($deviceSiteSettingsNonProgrammable, fn ($a, $b) => strcmp(strtolower($a['translation']), strtolower($b['translation']))); @endphp

    <x-page.tabs-secondary
        :target="$deviceSite"
        defaultTab="customFields"
        :tabs="[
            'customFields' => __('Custom Fields'),
            'programmableSettings' => __('Programmable settings'),
            'nonProgrammableSettings' => __('Advanced settings'),
        ]"
        :verticalSpace="true"
        :buttonsOnTop="true"
    >x
        <x-slot name="customFieldsButtons">
            @if($canWriteSettings && count($deviceSiteCustomFields))
                <div class="flex w-full justify-end gap-2">
                    <x-button.primary wire:click="updateDeviceSiteCustomFields">@lang('Update')</x-button.primary>
                    <x-button.secondary type="button" onclick="window.dispatchEvent(new CustomEvent('closeModal_siteSettingsCustomFields'))">@lang('Cancel')</x-button.secondary>
                </div>
            @endif
        </x-slot>

        <x-slot name="programmableSettingsButtons">
            @if(count($deviceSiteSettingsProgrammable))
                <div class="flex w-full justify-end gap-2">
                    <x-button.primary wire:click="updateProgrammableSettings">@lang('update')</x-button.primary>
                    <x-button.secondary type="button" onclick="window.dispatchEvent(new CustomEvent('closeModal_siteSettingsCustomFields'))">@lang('cancel')</x-button.secondary>
                </div>
            @endif
        </x-slot>

        <x-slot name="nonProgrammableSettingsButtons">
            @if(count($deviceSiteSettingsNonProgrammable))
                <div class="flex w-full justify-end gap-2">
                    <x-button.primary wire:click="updateNonProgrammableSettings">@lang('update')</x-button.primary>
                    <x-button.secondary type="button" onclick="window.dispatchEvent(new CustomEvent('closeModal_siteSettingsCustomFields'))">@lang('cancel')</x-button.secondary>
                </div>
            @endif
        </x-slot>

        <x-slot name="customFieldsSlot">
            <form wire:submit.prevent="updateDeviceSiteCustomFields">

                <div class="md:flex flex-wrap mb-0">
                    <div class="block w-full">
                        <div class="ml-8 justify-between pb-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">

                            @forelse($deviceSiteCustomFields as $customKey => $customField)
                                <x-forms.grid-group class="mb-4">
                                    <x-forms.label for="{{$customKey}}" :fallback="''">
                                        {{ !empty($customField['translations'][session('locale', 'default')]) ? $customField['translations'][session('locale', 'default')] : $customField['name'] }}
                                        @if(!empty($customField['required']))
                                            <x-monoicon.required />
                                        @endif
                                    </x-forms.label>
                                    <x-input.text wire:model.defer="deviceSiteCustomFields.{{$customKey}}.value" canWriteSettings="{{$canWriteSettings}}" type="text" name="{{$customKey}}"  />
                                </x-forms.grid-group>
                            @empty
                                <p class="col-span-4 text-sm py-2 px-4 mb-8 mx-1 text-white bg-color-new-400">@lang('No custom fields are configured for Sites.')</p>
                            @endforelse

                        </div>
                    </div>
                </div>

            </form>
        </x-slot>

        <x-slot name="programmableSettingsSlot">
            <x-settings.programmable-settings
                :programmableSettings="$deviceSiteSettingsProgrammable"
                :updateMethodName="'updateProgrammableSetting'"
                :model="'deviceSiteSettingsProgrammable'"
                :target="$deviceSite"
            ></x-settings.programmable-settings>
        </x-slot>

        <x-slot name="nonProgrammableSettingsSlot">
            <x-settings.advanced-settings
                :advancedSettings="$deviceSiteSettingsNonProgrammable"
                :updateMethodName="'updateNonProgrammableSettings'"
                :model="'deviceSiteSettingsNonProgrammable'"
            ></x-settings.advanced-settings>
        </x-slot>

    </x-page.tabs-secondary>

    <script>
        window.siteSettingsLivewireId = '{{$this->id}}';
    </script>
</div>