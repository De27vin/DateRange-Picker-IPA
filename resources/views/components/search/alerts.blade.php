{{-- @deprecated --}}
@props(['filters', 'alertsCountGrouped', 'alertTranslations'])

<div class="grid grid-flow-col alert-grids gap-x-4 w-full pb-12" >

    @php
        $visibleAlertsCount = $alertsCountGrouped['visible'];
        ksort($visibleAlertsCount);
    @endphp

    @foreach($visibleAlertsCount as $type => $typeCount)
        <div wire:key="filters.alert[{{$type}}]" class="@if($typeCount == 0) appearance-none disabled opacity-30 @endif  py-2 px-0 flex items-center justify-between" >
            <div class="flex flex-row ">
                <div class="btn switch
                    @if($filters['alerts'][$type] && $typeCount > 0) active
                        @if(isset($alertsCountGrouped['critical'][$type])) bg-red-400
                        @else bg-warning-400 @endif
                    @else bg-opacity-40
                         @if(isset($alertsCountGrouped['critical'][$type])) bg-red-400
                         @else bg-warning-400 @endif
                    @endif"

                    @if($typeCount > 0) wire:click.prevent.stop="toggleFilterAlert('{{$type}}')" @endif
                    aria-checked="true"
                    aria-describedby="privacy-option-1-description"
                    aria-labelledby="privacy-option-1-label"
                    role="switch">
                        <span class="
                            @if($filters['alerts'][$type] && $typeCount > 0) translate-x-5
                            @else translate-x-0
                            @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                aria-hidden="true">
                        </span>
                </div>
                <p class=" ml-4 text-sm text-secondary-600 text-left" id="privacy-option-1-description">
                    {{ $alertTranslations[$type] }}
                </p>
            </div>
            <div class="flex flex-row  justify-end">
                <p class="text-sm text-medium text-secondary-600 text-right w-10" id="privacy-option-1-label">
                    {{ $typeCount }}
                </p>
            </div>
        </div>
    @endforeach
</div>