<div wire:poll.visible.30s="updateDashboardStats" class="bottom-underline py-4">
    <div class="grid h-72 sm:h-56 md:h-40 lg:h-28 sm:grid-flow-row gap-4 sm:gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">

        @foreach($stats as $statitem)
            <div class="flex relative" style="background-color: {{ $statitem['color'] }};">
                <div class="relative h-full w-full leading-tight">

                    <div class="h-full flex flex-col justify-between pb-1">
                        <div class="py-2 px-2 text-xl" style="color: {{ $statitem['text-color'] }};" >@lang($statitem['label'])</div>

                        <div>
                            @foreach($statitem['values'] as $title => $value)
                                <div class="flex pt-1 w-full text-base md:text-base justify-between">
                                    <div class="px-2">{{ __($title) }}</div>
                                    <div class="px-2">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</div>
