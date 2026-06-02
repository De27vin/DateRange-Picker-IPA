{{-- @deprecated --}}
<div>
    <x-search.filters-new
            :searchTabs="$searchTabs"
            :listCount="[]"
            :filtersId="$filtersId"
            :filters="$filters"
            :groups="$groups"
            :sortOptions="$sortOptions"
            :alertTranslations="$alertTranslations"
            :alertsCountGrouped="$alertsCountGrouped"
            :showMenu="true"
            :showCreateSite="true"
            :exportSites="true"
            :countIcon="'building'"
    ></x-search.filters-new>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.Livewire.on('equipment_filters_updated', (data) => {
            if (typeof window.dispatchEvent === 'function') {
                window.dispatchEvent(new CustomEvent('updatedFilters', { detail: data }));
            }
        });
    });
</script>
@endpush
