@extends ('layouts.app')

@section ('content')

    <div class="mx-auto w-full px-5 justify-start items-start">
        <livewire:dashboard.stats></livewire:dashboard.stats>
{{--        <livewire:filters.dashboard-filters></livewire:filters.dashboard-filters>--}}

        <div id="vue-dashboard-filters">
            <vue-dashboard-filters></vue-dashboard-filters>
        </div>
        <script src="{{ mix('/vue/vue-dashboard-filters.js') }}"></script>

        <!-- Export Components (conditionally loaded) -->
        <div x-data="{ showExport: false, showExportComments: false }" 
             x-on:toggle-export.window="showExport = true"
             x-on:toggle-export-comments.window="showExportComments = true"
             x-on:dropdown-select.window="if ($event.detail.element === '') { showExport = false; showExportComments = false }">

            <div x-cloak x-show="showExport" style="display:none" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <livewire:ucp.export-devices-new
                    :filtersId="'Dashboard'"
                    :exportSites="false"
                    wire:key="export-devices-component"
                ></livewire:ucp.export-devices-new>
            </div>

            <div x-cloak x-show="showExportComments" style="display:none" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <livewire:ucp.export-comments-new
                    :filtersId="'Dashboard'"
                    :exportSites="false"
                    wire:key="export-comments-component"
                ></livewire:ucp.export-comments-new>
            </div>
        </div>

        <div id="vue-dashboard-list">
            <vue-dashboard-list></vue-dashboard-list>
        </div>
        <script src="{{ mix('/vue/vue-dashboard-list.js') }}"></script>
    </div>

@endsection

