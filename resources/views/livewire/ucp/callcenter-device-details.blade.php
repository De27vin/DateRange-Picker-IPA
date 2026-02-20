<div class="mt-16 pb-12 mx-auto">

    {{-- VUE EQUIPMENT SITE - DEVICE --}}
    <div class="mx-auto w-full px-2 justify-start items-start">
        <div id="vue-equipment-site" wire:ignore>
            <vue-equipment-site
                    :device-mode-id="{{$device->device_id}}"
                    :site-id="{{$device->device_site->ds_id}}"
                    :actions-forbidden="[]"
                    :initially-open-comments="true"
            ></vue-equipment-site>
        </div>
        <script src="/vue/vue-equipment-site.js"></script>
    </div>


    <x-devices.device-section-stats
        :deviceStates="$deviceStates"
        :devicesAlerts="$deviceAlerts"
        :alertTranslations="$alertTranslations"
        :device="$device"
        :left-padding="false"
    ></x-devices.device-section-stats>

    <livewire:ucp.device-history-new
        :deviceId="$device->device_id"
        :deviceSiteId="$device->device_site->ds_id"
        :leftGap="false"
        :monitorClassification="true"
    />

</div>


