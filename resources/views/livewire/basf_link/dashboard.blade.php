<div class="mx-auto w-full px-5 justify-start items-start ">
    <div class="pt-2">
{{--        <x-dashboard.stats--}}
{{--                :stats="$stats"--}}
{{--                --}}{{--                 :showAlertsFilter="$showAlertsFilter"--}}
{{--        />--}}
    </div>

    <div class="w-full">

{{--        <x-search.filters--}}
{{--                :searchTabs="$searchTabs"--}}
{{--                :listCount="[]"--}}
{{--                :filtersId="$filtersId"--}}
{{--                :filters="$filters"--}}
{{--                :groups="$groups"--}}
{{--                :sortOptions="$sortOptions"--}}
{{--                :alertTranslations="$alertTranslations"--}}
{{--                :alertsCountGrouped="$alertsCountGrouped"--}}
{{--        ></x-search.filters>--}}

{{--        <div wire:loading.class="opacity-40" class=" border-none overflow-hidden">--}}
{{--            <ul class="" role="list">--}}

{{--                @forelse($devices as $device)--}}
{{--                    <div wire:key="dashboard-{{$device['device_id']}}">--}}
{{--                        <x-basf_link.device-compact-new-layout--}}
{{--                                :device="$device"--}}
{{--                                :deviceSite="$device['device_site']"--}}
{{--                                :fieldTranslations="$fieldTranslations"--}}
{{--                                :alertTranslations="$alertTranslations"--}}
{{--                                :criticalAlerts="$alerts['critical']"--}}
{{--                                :customFields="$customFields"--}}
{{--                                :extLinkAccSlug="$extLinkAccSlug"--}}
{{--                        ></x-basf_link.device-compact-new-layout>--}}
{{--                    </div>--}}
{{--                @empty--}}
{{--                    <div class="mt-8 p-4 text-center text-white bg-color-new-400 border border-slate-400 shadow-lg">--}}
{{--                        @lang('No Telenot devices with active alarm.')--}}
{{--                    </div>--}}
{{--                @endforelse--}}

{{--                @if($devices->hasMorePages())--}}
{{--                    <div class="w-full justify-center relative">--}}
{{--                        <x-button.load-more wire:click.prevent="loadMore">--}}
{{--                            @lang('Load more')--}}
{{--                        </x-button.load-more>--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--            </ul>--}}
{{--        </div>--}}

        @php
            $linkDevice = route($extLinkAccSlug.'-external-link-device', '');
            $linkDevice = rtrim($linkDevice, '/') . '/';
        @endphp

        <div id="vue-dashboard-list" wire:ignore>
            <vue-dashboard-list
                    empty-message="'{{__('No devices with active alarm.')}}'"
                    :devices-url="'/dashboard/basf-devices'"
                    :actions-forbidden="true"
                    :go-to-device="true"
                    :go-to-url="'{{ $linkDevice }}'"
            ></vue-dashboard-list>
        </div>
        <script src="/vue/vue-dashboard-list.js"></script>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let filtersDispatched = false;
            
            function sendBasfFilters() {
                if (!filtersDispatched) {
                    filtersDispatched = true;
                    
                    window.dispatchEvent(new CustomEvent('filtersChanged', {
                        detail: {
                            filters: {
                                search: '',
                                search_selected: ['all'],
                                sortedby: 'device_equipment',
                                sortDirection: 'asc',
                                alerts: {},
                                groups: [],
                                search_tabs: []
                            },
                            searchTabs: {
                                'active alarm': true,
                                'overdue': false,
                                'alert': false
                            }
                        }
                    }));
                }
            }
            
            window.addEventListener('filtersPing', function() {
                sendBasfFilters();
            });
            
            setTimeout(function() {
                sendBasfFilters();
            }, 100);
        });
        </script>

    </div>
</div>