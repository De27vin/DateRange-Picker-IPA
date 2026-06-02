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
            <vue-equipment-filters :can-import="{{ Auth::user()->canImport() ? 'true' : 'false' }}"></vue-equipment-filters>
        </div>
        <script src="{{ mix('/vue/vue-equipment-filters.js') }}"></script>

        <!-- Export/Import Components (conditionally loaded) -->
        <div x-data="{
                 showImport: false,
                 showExport: false,
                 showExportComments: false,

                 openPanel(panel) {
                     this.showImport = (panel === 'import');
                     this.showExport = (panel === 'export');
                     this.showExportComments = (panel === 'export-comments');
                 },

                 closeAll() {
                     this.showImport = false;
                     this.showExport = false;
                     this.showExportComments = false;
                 }
             }"
             x-on:toggle-import.window="openPanel('import')"
             x-on:toggle-export.window="openPanel('export')"
             x-on:toggle-export-comments.window="openPanel('export-comments')"
             x-on:dropdown-select.window="if ($event.detail.element === '') { closeAll() }">

            @if(Auth::user()->canImport())
                <div x-cloak x-show="showImport" style="display:none; position: relative;" class="bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                    <button @click="closeAll()" style="position: absolute; top: 0.5rem; right: 0.5rem;" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <livewire:ucp.import-devices
                            wire:key="import-devices-component"
                    ></livewire:ucp.import-devices>
                </div>
            @endif

            <div x-cloak x-show="showExport" style="display:none; position: relative;" class="bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <button @click="closeAll()" style="position: absolute; top: 0.5rem; right: 0.5rem;" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <livewire:ucp.export-devices-new
                        :filtersId="'Equipment'"
                        :exportSites="true"
                        wire:key="export-devices-component"
                ></livewire:ucp.export-devices-new>
            </div>

            <div x-cloak x-show="showExportComments" style="display:none; position: relative;" class="bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <button @click="closeAll()" style="position: absolute; top: 0.5rem; right: 0.5rem;" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
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

