@props(['deviceStates', 'devicesAlerts', 'alertTranslations', 'device', 'leftPadding' => true])

{{--<ul class="deviceinfos bottom-underline divide-y divide-gray-400 -ml-10 pl-10">--}}
{{--    --}}{{-- Device Site Settings --}}
{{--    <li x-data="{ openStats: false }">--}}
{{--        <div class="info-container text-medium block px-2 py-2 text-sm hover:text-gray-900 has-children">--}}

{{--            <div class="flex">--}}
{{--                <span x-on:click="openStats = ! openStats" class="flex justify-start w-full text-base h-12 items-center">--}}
{{--                    <i x-show="openStats"><x-monoicon.caret-down  class="flex mr-2" /></i>--}}
{{--                    <i x-show="!openStats"><x-monoicon.caret-right  class="flex mr-2" /></i>--}}
{{--                    @lang('Device Stats')--}}
{{--                </span>--}}

{{--                @if(Auth::user()->isAdmin)--}}
{{--                    <div class="flex w-64 justify-end items-center">--}}
{{--                        <span class="mr-3">--}}
{{--                            <span class="text-sm text-medium text-gray-800">@lang('Enabled')</span>--}}
{{--                        </span>--}}
{{--                        <div class="btn switch @if($device->device_enabled) active  bg-color-new @else bg-gray-400 @endif " wire:click="toggleDeviceState" aria-checked="true" aria-describedby="privacy-option-1-description" aria-labelledby="privacy-option-1-label" role="switch">--}}
{{--                            <span class="@if($device->device_enabled) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"  aria-hidden="true"></span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--            </div>--}}

{{--            <div x-cloak x-show="openStats" class="mb-2">--}}
{{--                <x-devices.box-item-statesinfo--}}
{{--                    :deviceStates="$deviceStates"--}}
{{--                    :deviceAlerts="$devicesAlerts"--}}
{{--                    :alertTranslations="$alertTranslations"--}}
{{--                    :deviceId="$device->device_id">--}}
{{--                </x-devices.box-item-statesinfo>--}}
{{--            </div>--}}

{{--        </div>--}}
{{--    </li>--}}
{{--</ul>--}}

<div class="info-container text-medium block pb-4 py-2 text-sm hover:text-gray-900 has-children bottom-underline @if($leftPadding) pl-9 @endif">
    <div class="flex w-full justify-between items-center ml-0">
        <span class="flex justify-start w-full text-base h-12 items-center">
            {{ __('Device Stats') }}
        </span>
    </div>

    <div class="mb-2">
        <x-devices.box-item-statesinfo
            :deviceStates="$deviceStates"
            :deviceAlerts="$devicesAlerts"
            :alertTranslations="$alertTranslations"
            :deviceId="$device->device_id"
            :createdAt="$device->device_created"
        >
        </x-devices.box-item-statesinfo>
    </div>

</div>
