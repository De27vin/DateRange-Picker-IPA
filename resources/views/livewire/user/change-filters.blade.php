<div class="mx-auto pb-12 px-4 mt-8">

    <x-page.header class="h-24">
        <x-slot name="title">@lang('Default filters')</x-slot>
        <x-slot name="description">@lang('Choose default active filter tab for device lists')</x-slot>
    </x-page.header>

    <div class="ml-9 w-full pt-2">
        <fieldset class="-mx-1 mb-1">
            <div class="flex w-full items-center">
                <span class="text-medium w-48">@lang('Dashboard default:')</span>
                @foreach($defaultDashboardFilters as $filter => $active)
                    <div class="flex justify-center w-36">
                        <x-nav.button-horizontal-new :uppercase="true" :active="$active" click="updateDashboardFilter('{{ $filter }}')">
                            @lang($filter)
                        </x-nav.button-horizontal-new>
                    </div>
                @endforeach
            </div>

            <div class="flex w-full items-center">
                <span class="text-medium w-48">@lang('Equipment default:')</span>
                @foreach($defaultEquipmentFilters as $filter => $active)
                    <div class="flex justify-center w-36">
                        <x-nav.button-horizontal-new :uppercase="true" :active="$active" click="updateEquipmentFilter('{{ $filter }}')">
                            @lang($filter)
                        </x-nav.button-horizontal-new>
                    </div>
                @endforeach
            </div>

        </fieldset>

    </div>
</div>