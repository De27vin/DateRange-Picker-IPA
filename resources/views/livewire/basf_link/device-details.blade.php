<div class="mt-8 pb-12 mx-auto px-4">
{{--    <x-page.header class="">--}}
{{--        <x-slot name="title">@lang('Device Detail')</x-slot>--}}
{{--        <x-slot name="description">@lang('Description of device detail page')<br/>@if(!$hasCarcall){{__('To trigger a callback, you must be logged in as an agent and a phone number must be stored in your profile.')}}@endif</x-slot>--}}
{{--    </x-page.header>--}}
{{--    <div class="search-group">--}}
{{--        <x-basf_link.device-action-buttons--}}
{{--                :actionButtons="$actionButtons" />--}}
{{--    </div>--}}

    {{-- NEW TILE --}}
    <div id="vue-equipment-site" wire:ignore>
        <vue-equipment-site
                :site-id="{{$device->device_site->ds_id}}"
                :big-btns="true"
                :basf-link="true"
                :actions-forbidden="['gatewayLink', 'rightSiteIcons', 'goToSite', 'goToDevice', 'set', 'carcall', 'revival', 'openModal']"
        ></vue-equipment-site>
    </div>
    <script src="/vue/vue-equipment-site.js"></script>

{{--    <div class="devicebox relative bg-white bg-opacity-20 shadow-lg pl-4 pr-12 my-8 border border-slate-300">--}}
{{--        <x-devices.infobox-site--}}
{{--                :deviceSite="$device->device_site"--}}
{{--                :fieldTranslations="$fieldTranslations"--}}
{{--                :showSiteLink="false"--}}
{{--        ><x-devices.infobox-device--}}
{{--                    :device="$device"--}}
{{--                    :deviceStates="$deviceStates"--}}
{{--                    :deviceAlerts="$deviceAlerts"--}}
{{--                    :alertTranslations="$alertTranslations"--}}
{{--                    :fieldTranslations="$fieldTranslations"--}}
{{--                    :latestComment="$latestComment"--}}
{{--                    :canClearAlerts="false"--}}
{{--            ></x-devices.infobox-device>--}}
{{--        </x-devices.infobox-site>--}}
{{--    </div>--}}

{{--    <div class="overflow-hidden" >--}}
{{--        --}}{{--        @if($device->device_site->module != 'SYSTEM')--}}
{{--        @if(!empty($device->device_site->module))--}}
{{--            <livewire:ucp.device-infos--}}
{{--                    :device="$device"/>--}}
{{--        @endif--}}
{{--    </div>--}}

{{--    <div class="overflow-hidden">--}}
{{--        --}}{{--        @if($device->device_type->dt_type != 'SYSTEM')--}}
{{--        @if(!empty($device->device_site->module))--}}
{{--            <livewire:ucp.device-settings--}}
{{--                    :device="$device"/>--}}
{{--        @endif--}}
{{--    </div>--}}

{{--    <div class="overflow-hidden">--}}
{{--        <livewire:ucp.device-comments--}}
{{--                :device="$device" />--}}
{{--    </div>--}}

{{--    <livewire:ucp.device-history-new--}}
{{--            :deviceId="$device->device_id"--}}
{{--            :deviceSiteId="null"--}}
{{--    />--}}
</div>


