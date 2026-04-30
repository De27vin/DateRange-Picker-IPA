@props(['dashboardWidgetSettings' => []])

@php
    $dateWidgets = [
        'equipment' => __('Equipment'),
        'overdues' => __('Overdues'),
        'alerts' => __('Alerts'),
    ];

    $rangeUnits = [
        'days' => __('Days'),
        'weeks' => __('Weeks'),
        'months' => __('Months'),
        'years' => __('Years'),
    ];
@endphp

<div class="pb-12 mx-auto px-4">
    <x-page.header class="mt-8 mb-2 h-20">
        <x-slot name="title">@lang('Dashboard Defaults')</x-slot>
        <x-slot name="description">@lang('Account-wide defaults for the dashboard cards. Users can still change their own dashboard view and reset it back to these defaults.')</x-slot>
        <x-slot name="actionButtons">
            <div class="flex justify-end mb-2 gap-2">
                <x-button.primary wire:click="updateDashboardWidgetSettings">@lang('Update')</x-button.primary>
                <x-button.secondary wire:click="cancelDashboardWidgetSettings">@lang('Cancel')</x-button.secondary>
            </div>
        </x-slot>
    </x-page.header>

    <div class="w-full pt-4">
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">@lang('Card Date Ranges')</h3>

                <div class="grid gap-4">
                    @foreach($dateWidgets as $widget => $label)
                        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_8rem_10rem] md:items-end">
                            <label class="font-semibold text-gray-700">{{ $label }}</label>

                            <label class="grid gap-1 text-sm text-gray-600">
                                <span>@lang('Amount')</span>
                                <input
                                    type="number"
                                    min="1"
                                    max="365"
                                    class="rounded border-gray-300"
                                    wire:model.defer="dashboardWidgetSettings.ranges.{{ $widget }}.amount"
                                >
                            </label>

                            <label class="grid gap-1 text-sm text-gray-600">
                                <span>@lang('Unit')</span>
                                <select
                                    class="rounded border-gray-300"
                                    wire:model.defer="dashboardWidgetSettings.ranges.{{ $widget }}.unit"
                                >
                                    @foreach($rangeUnits as $unit => $unitLabel)
                                        <option value="{{ $unit }}">{{ $unitLabel }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">@lang('Service Level Tachometers')</h3>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-1 text-sm text-gray-600">
                        <span>@lang('Red max')</span>
                        <input
                            type="number"
                            min="0"
                            max="100"
                            class="rounded border-gray-300"
                            wire:model.defer="dashboardWidgetSettings.serviceThresholds.redMax"
                        >
                    </label>

                    <label class="grid gap-1 text-sm text-gray-600">
                        <span>@lang('Orange max')</span>
                        <input
                            type="number"
                            min="0"
                            max="100"
                            class="rounded border-gray-300"
                            wire:model.defer="dashboardWidgetSettings.serviceThresholds.orangeMax"
                        >
                    </label>
                </div>
            </section>
        </div>
    </div>
</div>
