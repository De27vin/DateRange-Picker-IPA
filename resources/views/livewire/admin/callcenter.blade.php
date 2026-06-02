<div class="mx-auto w-full px-4 mt-12 pb-12 px-9">

    {{-- VUE EQUIPMENT SITE - CALLCENTER MODE --}}
    <div class="mx-auto w-full px-2 justify-start items-start">
        <div id="vue-equipment-site" wire:ignore>
            <vue-equipment-site
                    :device-mode-id="{{$deviceId}}"
                    :site-id="{{optional(optional(\App\Models\Device::find($deviceId))->device_site)->ds_id ?? 0}}"
                    :actions-forbidden="[]"
                    :callcenter-mode="true"
                    :initially-open-comments="true"
            ></vue-equipment-site>
        </div>
        <script src="/vue/vue-equipment-site.js"></script>
    </div>

</div>