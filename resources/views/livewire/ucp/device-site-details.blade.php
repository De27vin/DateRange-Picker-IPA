<div x-data="{showAddDevice: @entangle('showAddDevice') }" class="mt-12 pb-12 mx-auto px-8">

    <div wire:ignore>
        <x-modal.new-popup :popupId="'deviceSettingsCustomFields'">
            <livewire:ucp.device-settings-and-custom-fields :deviceId="'none'" wire:key="{{ rand() }}" />
        </x-modal.new-popup>

        <x-modal.new-popup :popupId="'siteSettingsCustomFields'">
            <livewire:ucp.site-settings-and-custom-fields :deviceSiteId="'none'" wire:key="{{ rand() }}" />
        </x-modal.new-popup>

        <x-modal.new-popup :popupId="'cliConfirmationModal'">
            <x-modal-content.cli-confirmation-for-new-modal></x-modal-content.cli-confirmation-for-new-modal>
        </x-modal.new-popup>
    </div>

    <div class="relative pb-5 w-full flex justify-between items-end text-sm mb-8">
        <div class="w-full ml-4 mr-16">
            <x-page.header class="">
                <x-slot name="title">@lang('Device Site Details')</x-slot>
                <x-slot name="description">@lang('Site data with their related devices. Here you can edit site details and -settings. Further you can add or remove devices. To edit the data of a device, please got to the appropriate device-detail page.')</x-slot>
            </x-page.header>
        </div>
        @if(!empty($deviceSite->module))
            <div class="flex items-center space-x-4 whitespace-nowrap">

                <x-form.button :color="'primary'" x-on:click="showAddDevice=true">{{__('Add Device')}}</x-form.button>

                <x-form.button-confirm
                        color="danger"
                        x-ref="modal{{$deviceSite->ds_id}}_button"
                        x-on:click.stop="open = true">{{__('Delete Device Site')}} ...

                    <x-slot name="title">@lang('Delete Device Site')</x-slot>
                    @if(empty($deviceSite->device_gateway) && !count($deviceSite->devices))
                        <x-slot name="content">
                            <div class="py-8 text-cool-gray-700">{{__('Are you sure you, to delete this site?')}}</div>
                        </x-slot>

                        <x-slot name="footer">
                            <x-button.secondary x-on:click.stop="open=false">{{__('Cancel')}}</x-button.secondary>

                            <x-button.primary  wire:click.prevent.self="deleteSite">{{__('Delete')}}</x-button.primary>
                        </x-slot>
                    @else
                        <x-slot name="content">
                            <div class="py-8 text-cool-gray-700">{{__('If you want to delete this installation, you must disconnect any connected gateway and delete all attached devices.')}}</div>
                        </x-slot>
                        <x-slot name="footer">
                            <x-button.secondary x-on:click.stop="open=false">{{__('Ok')}}</x-button.secondary>
                        </x-slot>
                    @endif
                </x-form.button-confirm>

                <div wire:loading wire:target="updateDevice" class="absolute z-50 inset-0 bg-white shadow-lg mx-auto "><div class="flex w-full h-full items-center justify-center">{{ __('proceeding') }} ...</div></div>
                <div wire:loading wire:target="deleteDevice" class="absolute z-50 inset-0 bg-white shadow-lg mx-auto "><div class="flex w-full h-full items-center justify-center">{{ __('proceeding') }} ...</div></div>
            </div>
        @endif
    </div>

    {{--    @if(!$deviceSite->is_system)--}}
    @if(!empty($deviceSite->module))
        <div x-cloak
             x-show="showAddDevice"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-10"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-10"
             class="mt-0">
            <div class="w-full border-b border-gray-400">
                <livewire:create.create-device :deviceSite="$deviceSite"/>
            </div>
        </div>
    @endif

{{--    <div class="info-container text-medium block pl-7 text-sm hover:text-gray-900">--}}
{{--        <div class="flex w-full justify-between items-center ml-0">--}}
{{--            <span class="flex justify-start w-full text-base items-center">--}}
{{--                {{ __('Configuration') }}--}}
{{--            </span>--}}
{{--        </div>--}}
{{--    </div>--}}

    {{-- VUE EQUIPMENT SITE --}}
    <div class="mx-auto w-full px-5 justify-start items-start">
        <div id="vue-equipment-site" wire:ignore>
            <vue-equipment-site :site-id="{{$deviceSite['ds_id']}}"></vue-equipment-site>
        </div>
        <script src="{{ mix('/vue/vue-equipment-site.js') }}"></script>
    </div>


{{--    TODO: REMEMEBER ABOUT SYSTEM DEVICE RESTRICTIONS--}}
{{--    @if(!$deviceSite->is_system)--}}
{{--    @if(!empty($deviceSite->module))--}}
{{--        <x-devices.setting-section-site--}}
{{--                :deviceSite="$deviceSite"--}}
{{--                :deviceSiteSettingsProgrammable="$deviceSiteSettingsProgrammable"--}}
{{--                :deviceSiteSettingsNonProgrammable="$deviceSiteSettingsNonProgrammable"--}}
{{--        ></x-devices.setting-section-site>--}}
{{--        <livewire:ucp.site-settings :deviceSiteId="$deviceSite->ds_id"/>--}}
{{--    @endif--}}
{{--    @if(!$deviceSite->is_system)--}}
{{--    @if(!empty($deviceSite->module))--}}
{{--        <x-devices.site-section-custom-fields--}}
{{--                :deviceSiteCustomFields="$deviceSiteCustomFields"--}}
{{--                :canWriteSettings="Auth::user()->is_user"--}}
{{--        ></x-devices.site-section-custom-fields>--}}
{{--    @endif--}}

    <x-devices.site-section-stats
        :devicesStates="$devicesStates"
        :devicesAlerts="$devicesAlerts"
        :alertTranslations="$alertTranslations"
        :devices="$deviceSite->devices"
    ></x-devices.site-section-stats>

    <div class="h-2"></div>

    <livewire:ucp.device-history-new
            :deviceId="null"
            :deviceSiteId="$deviceSite->ds_id"
    />
</div>