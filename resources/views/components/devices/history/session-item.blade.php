@props(['sessionItem','type', 'relatedEvents'])
<div>

    @if($type == 'alert')
        @php
            $eventState = 'success';
            if($sessionItem['alert_active']) {
                $eventState = ( $sessionItem['alert_type']['alert_severity']['as_type'] == 'MAJOR' ? 'error' : 'warning' );
            }

            $hasInfo = false;

        @endphp
        <div class="eventbox items-start h-full {{$eventState}} relative">
            <div class="flex-1 min-w-0 overflow-hidden items-start">
                @if(Str::length($sessionItem['alert_value']) > 20)
                    <p
                            class="label_type "
                            x-data="{ tooltip: false }"
                            x-on:mouseover="tooltip = true"
                            x-on:mouseleave="tooltip = false"
                            x-tooltip="@if($sessionItem['alert_value']) {{$sessionItem['alert_value']}} @else &nbsp; &nbsp; @endif">
                        <span
                                x-show="tooltip"
                                class="z-50 text-sm flex flex-wrap w-48 text-white h-auto absolute bg-color-new-400 rounded-lg p-2 transform -translate-y-8 translate-x-8">
                            @if( Str::contains($sessionItem['alert_value'],','))
                                @if($sessionItem['alert_value']) {{Str::replace(',','<br/>',$sessionItem['alert_value'])}} @else &nbsp; &nbsp; @endif
                            @else
                                @if($sessionItem['alert_value']) {{Str::replace(' ','<br/>',$sessionItem['alert_value'])}} @else &nbsp; &nbsp; @endif
                            @endif
                        </span>
                        {{$sessionItem['alert_type']['at_type']}}
                    </p>
                @else
                    <p class="label_type ">{{$sessionItem['alert_type']['at_type']}}</p>
                @endif
                <p class="label_value" >
                    @if( strval($sessionItem['alert_value']) == '') &nbsp; @else {{strval($sessionItem['alert_value'])}} @endif

                </p>
                <p class="label_time justify-end float-right">
                    {{ App\Helpers\Ucp::toUserTimezone($sessionItem['alert_timestamp'])->format('H:i:s') }}
                </p>
            </div>
        </div>
    @endif

    @if($type == 'set')
        @php
            $eventState = 'success';
            if(!$sessionItem['set_success']) {
                $eventState = 'error';
            }

        @endphp
        <div class="eventbox items-start h-full {{$eventState}} relative"  >
            <div class="flex-1 min-w-0 overflow-hidden items-start">
                @if(Str::length($sessionItem['set_value']) > 20)
                    <p
                            class="label_type "
                            x-data="{ tooltip: false }"
                            x-on:mouseover="tooltip = true"
                            x-on:mouseleave="tooltip = false"
                            x-tooltip="@if($sessionItem['set_value']) {{$sessionItem['set_value']}} @else &nbsp; &nbsp; @endif">
                        <span
                                x-show="tooltip"
                                class="z-50 text-sm flex flex-wrap w-48 text-white h-auto absolute bg-color-new-400 rounded-lg p-2 transform -translate-y-8 translate-x-8">

                            @if( Str::contains($sessionItem['set_value'],','))
                                @if($sessionItem['set_value']) {!! Str::replace(',','<br/>',$sessionItem['set_value']) !!} @else &nbsp; &nbsp; @endif
                            @else
                                @if($sessionItem['set_value']){!! Str::replace(' ','<br/>',$sessionItem['set_value']) !!} @else &nbsp; &nbsp; @endif
                            @endif
                        </span>

                        {{ __('settings.label.' .\Str::replace('.', '_', $sessionItem['setting']['setting_key']) ) }}
                    </p>
                @else
                    <p class="label_type ">{{ __('settings.label.' .\Str::replace('.', '_', $sessionItem['setting']['setting_key']) ) }}</p>
                @endif
                <p class="label_value">
                    @if( strval($sessionItem['set_value']) == '') &nbsp; @else {{strval($sessionItem['set_value'])}} @endif
                </p>
                <p class="label_time justify-end float-right">
                    {{ App\Helpers\Ucp::toUserTimezone($sessionItem['set_timestamp'])->format('H:i:s') }}
                </p>
            </div>
        </div>
    @endif

    @if($type == 'event')
        @php
            $eventState = 'bg-white border border-gray-200 hover:border-gray-400';
            if($sessionItem['event_severity'] != null) {
                switch ($sessionItem['event_severity']['es_type']) {
                    case 'CRITICAL':
                        $eventState = 'critical';
                        break;

                    case 'ERROR':
                        $eventState = 'error';
                        break;

                    case 'WARNING':
                        $eventState = 'warning';
                        break;

                    case 'NOTICE':
                        $eventState = 'notice';
                        break;

                    case 'INFO':
                        if(in_array($sessionItem['event_type']['et_type'], ['START','END'])){
                            $eventState = 'startend';
                        } else {
                            $eventState = 'info';
                        }
                        break;

                    default:
                        // code...
                        break;
                }
            }

            $hasInfo = false;
            if(strtoupper($sessionItem['event_type']['et_type']) == 'CNUMBER'){
                // ray($sessionItem);
                $hasInfo = true;
                $timestamp = App\Helpers\Ucp::toUserTimezone($sessionItem['event_timestamp'])->format('Y-m-d H:i:s');
                $sessionId = $sessionItem['event_session_id'];
                $seconds = strtotime($sessionItem['event_timestamp']);
                if (!array_key_exists($seconds, $relatedEvents)) {
                    $relatedEvents[$seconds] = [];
                }
            }

        @endphp
        <div class="eventbox items-start h-full  @if($hasInfo) cursor-pointer @endif {{$eventState}} relative" @if($hasInfo) wire:click.prevent="showRelatedInfo( {{$sessionId}}, '{{$sessionItem['event_timestamp']}}')" @endif >
            <div class="flex-1 min-w-0 overflow-hidden items-start">
                @if(Str::length($sessionItem['event_value']) > 20)
                    <p
                            class="label_type "
                            x-data="{ tooltip: false }"
                            x-on:mouseover="tooltip = true"
                            x-on:mouseleave="tooltip = false"
                            x-tooltip="@if($sessionItem['event_value']) {{$sessionItem['event_value']}} @else &nbsp; &nbsp; @endif">
                        <span
                                x-show="tooltip"
                                class="z-50 text-sm flex flex-wrap w-64 text-white h-auto absolute bg-color-new-400 rounded-lg p-4 transform -translate-y-8 translate-x-8">

                                @if( Str::contains($sessionItem['event_value'],','))
                                @if($sessionItem['event_value']) {!! Str::replace(',','<br/>',$sessionItem['event_value']) !!} @else &nbsp; &nbsp; @endif
                            @else
                                @if($sessionItem['event_value']) {!! Str::replace(' ','<br/>',$sessionItem['event_value']) !!} @else &nbsp; &nbsp; @endif
                            @endif
                        </span>
                        {{$sessionItem['event_type']['et_type']}}
                    </p>
                @else
                    <p class="label_type flex justify-between">{{$sessionItem['event_type']['et_type']}}  @if($hasInfo) <x-monoicon.circle-information></x-monoicon.circle-information> @endif </p>
                @endif
                <p class="label_value " >
                    @if( strval($sessionItem['event_value']) == '') &nbsp; @else {{strval($sessionItem['event_value'])}} @endif
                </p>
                <p class="label_time justify-end float-right">
                    {{ App\Helpers\Ucp::toUserTimezone($sessionItem['event_timestamp'])->format('H:i:s') }}
                </p>
            </div>

        </div>


    @endif
</div>
