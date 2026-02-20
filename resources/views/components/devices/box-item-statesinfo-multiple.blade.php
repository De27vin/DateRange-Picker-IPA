@props(['devicesStates', 'devicesAlerts', 'alertTranslations', 'canClearAlerts' => true, 'devices' => null])

    @php
        try{
            if( !is_object($devicesAlerts)){
                return back();
            }
        } catch(\Throwable $e){
            // redirect('/sites');
        }

        $criticalAlerts        = array_keys(array_filter($this->getAlertCriticalityStates()));
        $nonCriticalAlerts     = array_keys(Arr::except($this->getAlertCriticalityStates(), $criticalAlerts));
        $criticalAlerts        = array_values(array_diff($criticalAlerts,['ALARM','PERIODICAL']));
        $nonCriticalAlerts     = array_diff($nonCriticalAlerts,['ALARM','PERIODICAL']);

        $criticalAlertsSpecial = ['ALARM','PERIODICAL'];
        $alerts = [
            'active_alarm'     => ['ALARM'],
            'focused'     => ['PERIODICAL'],
            'critical'    => $criticalAlerts,
            'noncritical' => $nonCriticalAlerts,
            'translations'=> $this->getAlertTranslations($this->locale)
        ];
        $orderedAlerts = [
            'active_alarm' => [],
            'focused' => [],
            'critical' => [],
            'noncritical' => [],
            'count' => 0
        ];
    $devicesAlerts = $devicesAlerts->all();
    foreach ($devices->pluck('device_id')->toArray() as $deviceId) {
        if (array_key_exists($deviceId, $devicesAlerts)) {
            foreach ($devicesAlerts[$deviceId] as $deviceAlert) {
                foreach (array_keys($orderedAlerts) as $key) {
                    if ($key == 'count') {
                        continue;
                    }
                    if (in_array($deviceAlert['alert_type']['at_type'], $alerts[$key])) {

                        $deviceAlert['at_type'] = $deviceAlert['alert_type']['at_type'];
                        $orderedAlerts[$key][$deviceId][] = $deviceAlert;

                        $orderedAlerts['count'] = $orderedAlerts['count'] + 1;
                    }
                }
            }
        }
    }
    @endphp

{{--HEADERS--}}
<div class="min-w-0 flex-1 px-0 pb-0 md:grid md:grid-cols-2 lg:grid-cols-6 md:gap-4" >

    <div class="col-span-1">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none focus:outline-none focus:ring-0" style="cursor: default;">
            <span class=" px-3 hover:bg-gray-300 uppercase">@lang('Created')</span>
        </button>
    </div>

    <div class="col-span-1">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none" style="cursor: default;">
            <span class=" px-3 hover:bg-gray-300 uppercase">@lang('Periodicals')</span>
        </button>
    </div>

    <div class="col-span-1">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none focus:outline-none focus:ring-0" style="cursor: default;">
            <span class=" px-3 hover:bg-gray-300 uppercase">@lang('Set')</span>
        </button>
    </div>

    <div class="col-span-1">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none focus:outline-none focus:ring-0" style="cursor: default;">
            <span class=" px-3 hover:bg-gray-300 uppercase">@lang('Revival')</span>
        </button>
    </div>

    <div class="col-span-2">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none" style="cursor: default;">
            <span class=" px-3 hover:bg-gray-300 uppercase">@lang('Alerts')</span>
            @if($orderedAlerts['count'] > 0)
                <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-orange-600 hover:bg-orange-600">
                    <span>{{$orderedAlerts['count']}}</span>
                </span>
            @else
                <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-green-600 hover:bg-green-600">
                    <x-monoicon.state-ok class="w-4 h-4" />
                </span>
            @endif
        </button>
    </div>

</div>

{{--DEVICES--}}
@foreach($devices as $device)

    @php
        $emptyRow = empty($orderedAlerts['active_alarm'][$device->device_id]) &&
            empty($orderedAlerts['focused'][$device->device_id]) &&
            empty($orderedAlerts['critical'][$device->device_id]) &&
            empty($orderedAlerts['noncritical'][$device->device_id]) &&
            empty($devicesStates[$device->device_id]['device_lastreported']) &&
            empty($devicesStates[$device->device_id]['device_lastset']) &&
            empty($devicesStates[$device->device_id]['device_lastrevival']) &&
            empty($device->device_created);
    @endphp

    <div class="bottom-underline-light flex" style="margin-bottom: @if($emptyRow) 2.3rem @else 0.5rem @endif; margin-top: -0.1rem; color: #8a9097; font-size: 0.9rem; padding-left: 0.4rem; height: 0; margin-left: -17%;">
{{--        <div class="" style="padding-top: 0.6rem;">--}}
{{--            <i class="f7-icons icon default icon-sm tts cursor-pointer mr-1" style="margin-left: 0; color: #8a9097;">tag</i>--}}
{{--        </div>--}}
        <span class="icon-wrapper tt" style="padding-top: 0.6rem;">
            <i class="f7-icons icon default icon-sm tts cursor-pointer mr-1" style="margin-left: 0; color: #8a9097;">tag</i>
            <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ __('Equipment').': '.$device->device_equipment }}</span>
        </span>
        <div class="" style="letter-spacing: 0.05rem; font-size: 0.8rem; padding-top: 0.6rem;">
            {{ $device->device_equipment }}
        </div>
    </div>
    <div class="min-w-0 flex-1 px-0 pb-0 md:grid md:grid-cols-2 lg:grid-cols-6 md:gap-4">

        {{-- CREATED --}}
        <div class="col-span-1">
            @if($device->device_created)
                <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-gray-600 bg-gray-200 text-xs text-medium rounded-none">
                    {{toUserTimezone($device->device_created)}}
                </div>
            @endif
        </div>

        {{-- PERIODICALS--}}
        <div class="col-span-1">
            @if($devicesStates[$device->device_id]['device_lastreported'])
                <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-gray-600 bg-gray-200 text-xs text-medium rounded-none">
                    {{toUserTimezone($devicesStates[$device->device_id]['device_lastreported'])}}
                    @if(!$devicesStates[$device->device_id]['device_overdue'])
                        <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-green-600 hover:bg-green-600">
                            <x-monoicon.state-ok class="w-4 h-4" />
                        </span>
                    @else
                        <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-red-600 hover:bg-red-600">
                            <x-monoicon.state-failed class="w-4 h-4" />
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- SET --}}
        <div class="col-span-1">
            @if($devicesStates[$device->device_id]['device_lastset'])
                <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-gray-600 bg-gray-200 text-xs text-medium rounded-none">
                    {{toUserTimezone($devicesStates[$device->device_id]['device_lastset'])}}
                    @if($devicesStates[$device->device_id]['device_setok'])
                        <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-green-600 hover:bg-green-600">
                            <x-monoicon.state-ok class="w-4 h-4" />
                        </span>
                    @else
                        <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-red-600 hover:bg-red-600">
                            <x-monoicon.state-failed class="w-4 h-4" />
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- REVIVAL--}}
        <div class="col-span-1">
            @if($devicesStates[$device->device_id]['device_lastrevival'])
                <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-gray-600 bg-gray-200 text-xs text-medium rounded-none">
                    {{toUserTimezone($devicesStates[$device->device_id]['device_lastrevival'])}}
                    @if($devicesStates[$device->device_id]['device_revivalok'])
                        <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-green-600 hover:bg-green-600">
                            <x-monoicon.state-ok class="w-4 h-4" />
                        </span>
                    @else
                        <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-red-600 hover:bg-red-600">
                            <x-monoicon.state-failed class="w-4 h-4" />
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ALERTS --}}
        <div class="col-span-2 text-normal">
            @foreach($orderedAlerts['active_alarm'][$device->device_id] ?? [] as $deviceAlert)
                <div class="w-full h-6 flex justify-between items-center p-0 pl-3 border-none text-red-800 bg-red-200 text-xs rounded-none mb-2">
                    <div class="truncate">{{ toUserTimezone($deviceAlert['da_timestamp']) }} &nbsp;&nbsp;&nbsp; {{ $alerts['translations'][$deviceAlert['at_type']] ?? $deviceAlert['at_type'] }}</div>
                    <div class="flex items-center">
                        @if($deviceAlert['da_value'])<div class="mr-2">({{ $deviceAlert['da_value'] }})</div>@endif
                        @if($canClearAlerts)
                            <button class="h-full w-12 flex m-0 p-0 justify-center items-center text-red-800 hover:text-white bg-red-400 hover:bg-red-600 rounded-none" wire:click.prevent="removeAlert({{ $device->device_id }}, '{{$deviceAlert['at_type']}}', '{{$deviceAlert['da_value']}}')"><x-monoicon.trash class="w-6 h-6 py-1"></x-monoicon.trash></button>
                        @endif
                    </div>
                </div>
            @endforeach
            @foreach($orderedAlerts['focused'][$device->device_id] ?? [] as $deviceAlert)
                <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-red-800 bg-red-200 text-xs rounded-none">
                    <div class="truncate">{{ toUserTimezone($deviceAlert['da_timestamp']) }} &nbsp;&nbsp;&nbsp; {{ $alerts['translations'][$deviceAlert['at_type']] ?? $deviceAlert['at_type'] }}</div>
                    <div class="flex items-center">
                        @if($deviceAlert['da_value'])<div class="mr-2">({{ $deviceAlert['da_value'] }})</div>@endif
                        @if($canClearAlerts)
                            <button class="h-full w-12 flex m-0 p-0 justify-center items-center text-red-800 hover:text-white bg-red-400 hover:bg-red-600 rounded-none" wire:click.prevent="removeAlert({{ $device->device_id }}, '{{$deviceAlert['at_type']}}', '{{$deviceAlert['da_value']}}')"><x-monoicon.trash class="w-6 h-6 py-1"></x-monoicon.trash></button>
                        @endif
                    </div>
                </div>
            @endforeach
            @foreach($orderedAlerts['critical'][$device->device_id] ?? [] as $deviceAlert)
                <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-red-800 bg-red-200 text-xs rounded-none">
                    <div class="truncate">{{ toUserTimezone($deviceAlert['da_timestamp']) }} &nbsp;&nbsp;&nbsp; {{ $alerts['translations'][$deviceAlert['at_type']] ?? $deviceAlert['at_type'] }}</div>
                    <div class="flex items-center">
                        @if($deviceAlert['da_value'])<div class="mr-2">({{ $deviceAlert['da_value'] }})</div>@endif
                        @if($canClearAlerts)
                            <button class="h-full w-12 flex m-0 p-0 justify-center items-center text-red-800 hover:text-white bg-red-400 hover:bg-red-600 rounded-none" wire:click.prevent="removeAlert({{ $device->device_id }}, '{{$deviceAlert['at_type']}}', '{{$deviceAlert['da_value']}}')"><x-monoicon.trash class="w-6 h-6 py-1"></x-monoicon.trash></button>
                        @endif
                    </div>
                </div>
            @endforeach
            @foreach($orderedAlerts['noncritical'][$device->device_id] ?? [] as $deviceAlert)
                <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-orange-800 bg-orange-200 text-xs rounded-none">
                    <div class="truncate">{{ toUserTimezone($deviceAlert['da_timestamp']) }} &nbsp;&nbsp;&nbsp; {{ $alerts['translations'][$deviceAlert['at_type']] ?? $deviceAlert['at_type'] }}</div>
                    <div class="flex items-center">
                        @if($deviceAlert['da_value'])<div class="mr-2">({{ $deviceAlert['da_value'] }})</div>@endif
                        @if($canClearAlerts)
                            <button class="h-full w-12 flex m-0 p-0 justify-center items-center text-orange-800 hover:text-white bg-orange-400 hover:bg-orange-600 rounded-none" wire:click.prevent="removeAlert({{ $device->device_id }}, '{{$deviceAlert['at_type']}}', '{{$deviceAlert['da_value']}}')"><x-monoicon.trash class="w-6 h-6 py-1"></x-monoicon.trash></button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>

@endforeach

