@props(['devicesStates', 'devicesAlerts', 'alertTranslations', 'devices'])

{{--<ul class="deviceinfos bottom-underline divide-y divide-gray-400 -ml-10 pl-10">--}}
{{--    --}}{{-- Device Site Settings --}}
{{--    <li x-data="{ openStats: false }">--}}
{{--        <div class="info-container text-medium block px-2 py-2 text-sm hover:text-gray-900 has-children">--}}

{{--            <span x-on:click="openStats = ! openStats" class="flex justify-start w-full text-base h-12 items-center">--}}
{{--                <i x-show="openStats"><x-monoicon.caret-down  class="flex mr-2" /></i>--}}
{{--                <i x-show="!openStats"><x-monoicon.caret-right  class="flex mr-2" /></i>--}}
{{--                @lang('Site Stats')--}}
{{--            </span>--}}

{{--            <div x-cloak x-show="openStats" class="my-8" style="margin-left: 13.7%;">--}}
{{--                <x-devices.box-item-statesinfo-multiple--}}
{{--                    :devicesStates="$devicesStates"--}}
{{--                    :devicesAlerts="$devicesAlerts"--}}
{{--                    :alertTranslations="$alertTranslations"--}}
{{--                    :devices="$devices">--}}
{{--                </x-devices.box-item-statesinfo-multiple>--}}
{{--            </div>--}}

{{--        </div>--}}
{{--    </li>--}}
{{--</ul>--}}

<div class="info-container pb-4 text-medium block pl-9 py-2 text-sm hover:text-gray-900 has-children bottom-underline">
    <div class="flex w-full justify-between items-center ml-0">
        <span class="flex justify-start w-full text-base h-12 items-center">
            {{ __('Site Stats') }}
        </span>
    </div>

    <div style="margin-left: 14.4%;">
        <x-devices.box-item-statesinfo-multiple
            :devicesStates="$devicesStates"
            :devicesAlerts="$devicesAlerts"
            :alertTranslations="$alertTranslations"
            :devices="$devices">
        </x-devices.box-item-statesinfo-multiple>
    </div>

</div>