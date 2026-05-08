@php
    $charts = [
        'equipment' => __('Equipment'),
        'alarms' => __('Alarms'),
        'alerts' => __('Alerts'),
        'serviceLevel' => __('Service Level'),
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
        <x-slot name="title">@lang('Charts Defaults')</x-slot>
        <x-slot name="description">@lang('Your personal Charts page rolling defaults. Reset returns these values to the account-wide defaults.')</x-slot>
        <x-slot name="actionButtons">
            <div class="flex justify-end mb-2 gap-2">
                <x-button.primary wire:click="updateChartsSettings">@lang('Update')</x-button.primary>
                <x-button.secondary wire:click="cancelChartsSettings">@lang('Cancel')</x-button.secondary>
                <x-button.secondary wire:click="resetChartsSettings">@lang('Reset')</x-button.secondary>
            </div>
        </x-slot>
    </x-page.header>

    <div class="w-full pt-4">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">@lang('Chart Date Ranges')</h3>

            <div class="grid gap-4">
                @foreach($charts as $chart => $label)
                    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_8rem_10rem] md:items-end">
                        <label class="font-semibold text-gray-700">{{ $label }}</label>

                        <label class="grid gap-1 text-sm text-gray-600">
                            <span>@lang('Amount')</span>
                            <input
                                type="number"
                                min="1"
                                max="365"
                                class="rounded border-gray-300"
                                wire:model.defer="chartsSettings.ranges.{{ $chart }}.amount"
                            >
                        </label>

                        <label class="grid gap-1 text-sm text-gray-600">
                            <span>@lang('Unit')</span>
                            <select
                                class="rounded border-gray-300"
                                wire:model.defer="chartsSettings.ranges.{{ $chart }}.unit"
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
    </div>
</div>
