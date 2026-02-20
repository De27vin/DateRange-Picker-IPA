@props(['deviceStates', 'deviceAlerts', 'deviceId', 'alertTranslations', 'canClearAlerts' => true, 'createdAt' => null])
<div class="min-w-0 flex-1 md:grid md:grid-cols-6 lg:grid-cols-12 md:gap-4">
    @php
        try{
            if( !is_object($deviceAlerts)){
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
        $deviceAlerts = $deviceAlerts->all();
        if(array_key_exists($deviceId,$deviceAlerts)){
            foreach ($deviceAlerts[$deviceId] as $deviceAlert) {
                foreach(array_keys($orderedAlerts) as $key){
                    if($key == 'count'){
                        continue;
                    }
                    if (in_array($deviceAlert['alert_type']['at_type'], $alerts[$key])) {

                        $deviceAlert['at_type'] = $deviceAlert['alert_type']['at_type'];
                        $orderedAlerts[$key][] = $deviceAlert;

                        $orderedAlerts['count'] = $orderedAlerts['count'] + 1;
                    }
                }
            }
        }
    @endphp

    {{-- CREATED--}}
    <div class="col-span-2">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none focus:outline-none focus:ring-0">
            <span class=" px-3 hover:bg-gray-300 uppercase">{{ strtoupper(__('Created')) }}</span>
        </button>
        @if($createdAt)
            <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-gray-600 bg-gray-200 text-xs text-medium rounded-none">
                {{toUserTimezone($createdAt)}}
            </div>
        @endif
    </div>

    {{-- PERIODICALS--}}
    <div class="col-span-2">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none">
            <span class=" px-3 hover:bg-gray-300 uppercase">@lang('Periodicals')</span>
            @if(empty($deviceStates['device_lastreported']))
                <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-gray-600 hover:bg-gray-600">
                    &nbsp;
                </span>
            @elseif(!$deviceStates['device_overdue'])
                <span class="h-full w-12 flex justify-center items-center text-medium text-white bg-green-600 hover:bg-green-600">
                    <x-monoicon.state-ok class="w-4 h-4" />
                </span>
            @else
                <span class="h-full w-12 flex justify-center items-center text-medium text-white bg-red-600 hover:bg-red-600">
                    <x-monoicon.state-failed class="w-4 h-4" />
                </span>
            @endif
        </button>
        @if($deviceStates['device_lastreported'])
            <div class="w-full h-6 flex justify-between items-center p-0 px-3 mb-2 border-none text-gray-600 bg-gray-200 text-xs text-medium rounded-none">
                {{toUserTimezone($deviceStates['device_lastreported'])}}
            </div>
        @endif
    </div>


    {{-- SET --}}
    <div class="col-span-2">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none focus:outline-none focus:ring-0">
            <span class=" px-3 hover:bg-gray-300 uppercase">{{ strtoupper(__('Set')) }}</span>
            @if(empty($deviceStates['device_lastset']))
                <span class="h-full w-12 flex justify-center items-center text-medium text-white bg-gray-600 hover:bg-gray-600">
                    &nbsp;
                </span>
            @elseif($deviceStates['device_setok'])
                <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-green-600 hover:bg-green-600">
                    <x-monoicon.state-ok class="w-4 h-4" />
                </span>
            @else
                <span class="h-full w-12 flex justify-center items-center text-medium text-white bg-red-600 hover:bg-red-600">
                    <x-monoicon.state-failed class="w-4 h-4" />
                </span>
            @endif
        </button>
        @if($deviceStates['device_lastset'])
            <div class="w-full h-6 flex justify-between items-center p-0 px-3 mb-2 border-none text-gray-600 bg-gray-200 text-xs text-medium rounded-none">
                {{toUserTimezone($deviceStates['device_lastset'])}}
            </div>
        @endif
    </div>

    {{-- REVIVAL--}}
    <div class="col-span-2">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none focus:outline-none focus:ring-0">
            <span class=" px-3 hover:bg-gray-300 uppercase">{{ strtoupper(__('Revival')) }}</span>
            @if(empty($deviceStates['device_lastrevival']))
                <span class="h-full w-12 flex  justify-center items-center text-medium text-white bg-gray-600 hover:bg-gray-600">
                    &nbsp;
                </span>
            @elseif($deviceStates['device_revivalok'])
                <span class="h-full w-12 flex justify-center items-center text-medium text-white bg-green-600 hover:bg-green-600">
                    <x-monoicon.state-ok class="w-4 h-4" />
                </span>
            @else
                <span class="h-full w-12 flex justify-center items-center text-medium text-white bg-red-600 hover:bg-red-600">
                    <x-monoicon.state-failed class="w-4 h-4" />
                </span>
            @endif
        </button>
        @if($deviceStates['device_lastrevival'])
            <div class="w-full h-6 flex justify-between items-center p-0 px-3 mb-2 border-none text-gray-600 bg-gray-200 text-xs text-medium rounded-none">
                {{toUserTimezone($deviceStates['device_lastrevival'])}}
            </div>
        @endif
    </div>

    {{-- ALERTS --}}
    <div class="col-span-4">
        <button type="button" class="mx-0 w-full h-6 flex justify-between items-center p-0 border-none text-gray-800 bg-gray-300 text-xs text-medium rounded-none">
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
        @foreach($orderedAlerts['active_alarm'] as $deviceAlert)
            <div class="w-full h-6 flex justify-between items-center p-0 pl-3 border-none text-red-800 bg-red-200 text-xs rounded-none mb-2">
                <div class="truncate">{{ toUserTimezone($deviceAlert['da_timestamp']) }} &nbsp;&nbsp;&nbsp; {{ $alerts['translations'][$deviceAlert['at_type']] ?? $deviceAlert['at_type'] }}</div>
                <div class="flex items-center">
                    @if($deviceAlert['da_value'])<div class="mr-2">({{ $deviceAlert['da_value'] }})</div>@endif
                    @if($canClearAlerts)
                        <button class="h-full w-12 flex m-0 p-0 justify-center items-center text-red-800 hover:text-white bg-red-400 hover:bg-red-600 rounded-none" wire:click.prevent="removeAlert({{ $deviceId }}, '{{$deviceAlert['at_type']}}', '{{$deviceAlert['da_value']}}')"><x-monoicon.trash class="w-6 h-6 py-1"></x-monoicon.trash></button>
                    @endif
                </div>
            </div>
        @endforeach
        @foreach($orderedAlerts['focused'] as $deviceAlert)
            <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-red-800 bg-red-200 text-xs rounded-none">
                <div class="truncate">{{ toUserTimezone($deviceAlert['da_timestamp']) }} &nbsp;&nbsp;&nbsp; {{ $alerts['translations'][$deviceAlert['at_type']] ?? $deviceAlert['at_type'] }}</div>
                <div class="flex items-center">
                    @if($deviceAlert['da_value'])<div class="mr-2">({{ $deviceAlert['da_value'] }})</div>@endif
                    @if($canClearAlerts)
                        <button class="h-full w-12 flex m-0 p-0 justify-center items-center text-red-800 hover:text-white bg-red-400 hover:bg-red-600 rounded-none" wire:click.prevent="removeAlert({{ $deviceId }}, '{{$deviceAlert['at_type']}}', '{{$deviceAlert['da_value']}}')"><x-monoicon.trash class="w-6 h-6 py-1"></x-monoicon.trash></button>
                    @endif
                </div>
            </div>
        @endforeach
        @foreach($orderedAlerts['critical'] as $deviceAlert)
            <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-red-800 bg-red-200 text-xs rounded-none">
                <div class="truncate">{{ toUserTimezone($deviceAlert['da_timestamp']) }} &nbsp;&nbsp;&nbsp; {{ $alerts['translations'][$deviceAlert['at_type']] ?? $deviceAlert['at_type'] }}</div>
                <div class="flex items-center">
                    @if($deviceAlert['da_value'])<div class="mr-2">({{ $deviceAlert['da_value'] }})</div>@endif
                    @if($canClearAlerts)
                        <button class="h-full w-12 flex m-0 p-0 justify-center items-center text-red-800 hover:text-white bg-red-400 hover:bg-red-600 rounded-none" wire:click.prevent="removeAlert({{ $deviceId }}, '{{$deviceAlert['at_type']}}', '{{$deviceAlert['da_value']}}')"><x-monoicon.trash class="w-6 h-6 py-1"></x-monoicon.trash></button>
                    @endif
                </div>
            </div>
        @endforeach
        @foreach($orderedAlerts['noncritical'] as $deviceAlert)
            <div class="w-full h-6 flex justify-between items-center p-0 pl-3 mb-2 border-none text-orange-800 bg-orange-200 text-xs rounded-none">
                <div class="truncate">{{ toUserTimezone($deviceAlert['da_timestamp']) }} &nbsp;&nbsp;&nbsp; {{ $alerts['translations'][$deviceAlert['at_type']] ?? $deviceAlert['at_type'] }}</div>
                <div class="flex items-center">
                    @if($deviceAlert['da_value'])<div class="mr-2">({{ $deviceAlert['da_value'] }})</div>@endif
                    @if($canClearAlerts)
                        <button class="h-full w-12 flex m-0 p-0 justify-center items-center text-orange-800 hover:text-white bg-orange-400 hover:bg-orange-600 rounded-none" wire:click.prevent="removeAlert({{ $deviceId }}, '{{$deviceAlert['at_type']}}', '{{$deviceAlert['da_value']}}')"><x-monoicon.trash class="w-6 h-6 py-1"></x-monoicon.trash></button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
