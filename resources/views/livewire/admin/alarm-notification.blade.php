<div wire:poll.8s="updateAlarmCalls"
     class="flex justify-end px-8 text-gray-600 @if(count($alarmCalls) == 0) text-opacity-20 @endif"
     style="z-index: 99999;">
    @if(count($alarmCalls) == 0)
        <x-form.icon :icon="'bell_slash'" :color="'disabled'" :size="'xl'"/>
        {{-- <i class="mi-notification text-4xl"></i> --}}
    @else
        <div class="relative">
            <div class="relative px-0 dropdown cursor-pointer" x-data="{ open: false }"
                 x-on:keydown.window.escape="open = false" x-on:click.away="open = false" class="dropdown">
                <div x-on:click="open = !open" class="with_text" id="options-menu" aria-haspopup="true"
                     x-bind:aria-expanded="open" aria-expanded="true">
                    <div wire:model="alarmCalls"
                         class="absolute bottom-auto left-auto right-0 top-0 z-10 mt-2 rotate-0 skew-x-0 skew-y-0 scale-x-100 scale-y-100 rounded-full bg-red-700 text-white p-0.5 w-5 h-5 flex items-center justify-center text-xs">@if(count($alarmCalls) > 0)
                            {{ count($alarmCalls)}}
                        @endif</div>
                    <x-form.icon :icon="'bell_fill'" :color="'blue'" :size="'xl'"></x-form.icon>
                    <div
                            class="origin-top-right absolute right-0 mt-1 shadow-md"
                            x-show="open"
                            style="display: none; width: 59rem;"
                            x-on:click="open = false">
                        <div class="bg-white shadow-md border border-gray-300 absolute top-auto left-0 min-w-full z-40 "
                             x-show="open" style="display: none; width: 68rem;">
                            <div class="bg-white w-full relative z-40 py-1">
                                <ul class="list-reset divide-y divide-gray-200">
                                    @foreach($alarmCalls as $alarmCallDevice)
                                        <li class="relative">

                                            @php
                                                $address = $alarmCallDevice['device_site']['address']['in_one_line'] ?? null;
                                                $equipment = $alarmCallDevice['device_equipment'] ?? null;
                                                $line = ($equipment && $address) ? ($equipment.', '.$address) : ($equipment ?? $address ?? '');

                                                $phoneType = $alarmCallDevice['device_site']['single_number']['type'] ?? null;
                                                $phoneLine = $phoneType ? ucfirst($phoneType).':' : '';
                                            @endphp

                                            {{-- THIS TO PATH TO CALLCENTER - THIS IS STUBBED--}}
                                            <a href="/callcenter/{{$alarmCallDevice['device_id']}}" class="group px-4 py-2 flex justify-between items-center hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                <span class="flex"><span class="text-gray-400 group-hover:text-white">{{ __('Equipment') }}:&nbsp;</span><span class="text-gray-500 group-hover:text-white">{{ $line }}</span></span>
                                                <span class="flex">
                                                    <span class="text-gray-400 group-hover:text-white w-11">{{ $phoneLine }}</span>
                                                    <span class="flex text-gray-500 group-hover:text-white w-32">
                                                        {{ $alarmCallDevice['device_site']['single_number']['value'] ?? '' }}
                                                    </span>
                                                    <x-monoicon.chevron-right class="ml-2 text-gray-400 group-hover:text-white"/>
                                                </span>
                                            </a>

                                            {{-- THIS TO PATH TO STUB--}}
{{--                                            <a href="/devices/{{$alarmCall['device_id']}}" class="group px-4 py-2 flex justify-between items-center hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">--}}
{{--                                                <span class="flex">{{__('Installation')}}: {{$alarmCall['device_site']['ds_name']}}</span><span class="flex text-gray-400 group-hover:text-white">{{$alarmCall['device_site']['pstn']['number_value'] ?? 'no pstn'}} <x-monoicon.chevron-right class="ml-2 text-gray-400 group-hover:text-white" /></span>--}}
{{--                                            </a>--}}

                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
{{-- <x-dropdown.item type="button" wire:click.prevent="takeAlarmCall({{$alarmCall['devices'][0]['device_id']}})" >{{$alarmCall['pstn']['number_value']}}...</x-dropdown.item> --}}
