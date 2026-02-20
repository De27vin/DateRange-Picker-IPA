@extends ('layouts.app')

@section ('content')

    <x-modal.new-popup :popupId="'deviceSettingsCustomFields'">
        <livewire:ucp.device-settings-and-custom-fields :deviceId="'none'" wire:key="{{ rand() }}" />
    </x-modal.new-popup>

    <x-modal.new-popup :popupId="'siteSettingsCustomFields'">
        <livewire:ucp.site-settings-and-custom-fields :deviceSiteId="'none'" wire:key="{{ rand() }}" />
    </x-modal.new-popup>

    <x-modal.new-popup :popupId="'cliConfirmationModal'">
        <x-modal-content.cli-confirmation-for-new-modal></x-modal-content.cli-confirmation-for-new-modal>
    </x-modal.new-popup>

    <div class="mx-auto w-full px-5 justify-start items-start">
{{--        <livewire:filters.equipment-filters></livewire:filters.equipment-filters>--}}

        <!-- NEW VUE FILTERS (experimental) -->
        <div id="vue-equipment-filters">
            <vue-equipment-filters></vue-equipment-filters>
        </div>
        <script src="{{ mix('/vue/vue-equipment-filters.js') }}"></script>

        <!-- Export Components (conditionally loaded) -->
        <div x-data="{ showExport: false, showExportComments: false }"
             x-on:toggle-export.window="showExport = true"
             x-on:toggle-export-comments.window="showExportComments = true"
             x-on:dropdown-select.window="if ($event.detail.element === '') { showExport = false; showExportComments = false }">

            <div x-cloak x-show="showExport" style="display:none" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <livewire:ucp.export-devices-new
                        :filtersId="'Equipment'"
                        :exportSites="true"
                        wire:key="export-devices-component"
                ></livewire:ucp.export-devices-new>
            </div>

            <div x-cloak x-show="showExportComments" style="display:none" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <livewire:ucp.export-comments-new
                        :filtersId="'Equipment'"
                        :exportSites="true"
                        wire:key="export-comments-component"
                ></livewire:ucp.export-comments-new>
            </div>
        </div>

        <div id="vue-equipment-list" class="px-2">
            <vue-equipment-list></vue-equipment-list>
        </div>
        <script src="{{ mix('/vue/vue-equipment-list.js') }}"></script>
    </div>

@endsection

