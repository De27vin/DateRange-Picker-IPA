{{-- device-history-new.blade.php --}}
<div class="deviceinfos @if($leftGap) pl-9 @endif">

    <div class="">
        <x-devices.history-filter-ucp2
                :deviceSite="$deviceSite"
                :siteDevicesIds="$siteDevicesIds"
                :contextOptions="$contextOptions"
                :historyFilter="$historyFilter"
                :severityFilter="$severityFilter"
                :dateFilter="$dateFilter" />
    </div>

    <div>
        <div class="w-full h-14 pt-4 bg-transparent flex justify-center items-center">
             <div wire:loading wire:target="updateHistory" class="ml-10"><x-monoicon.loading-indicator></x-monoicon.loading-indicator></div>
        </div>

        <ul wire:poll.10s="updateHistory" class="px-0 text-sm transition-all duration-1000 text-medium">

            <li class="flex w-full uppercase flex items-center py-2 px-4 bg-gray-300 mb-2 h-14">
                <div class="px-2 border-r border-gray-500" style="width: 8rem;">{{__('Severity')}}</div>
                <div class="px-4 border-r border-gray-500" style="width: 8rem;">{{__('Event')}}</div>
                <div class="px-4 border-r border-gray-500" style="width: 8rem;">{{__('Equipment')}}</div>
                <div class="px-4 border-r border-gray-500" style="width: 5rem;">{{__('ID')}}</div>
                <div class="px-4 border-r border-gray-500" style="width: 5.5rem;">{{__('Host')}}</div>
                <div class="px-4 border-r border-gray-500 grow">{{__('Message')}}</div>
                <div class="px-4 border-r border-gray-500" style="width: 9.8rem;">{{__('Time')}}</div>
                <div class="pl-4 border-gray-500" style="width: 5rem;">{{__('Duration')}}</div>
            </li>

            @php $mapIdEquip = array_flip($siteDevicesIds) @endphp
            @foreach($history as $historyItem)
                @php
                    $healthStates = $this->getHealthStates($historyItem);

                    $classification = $historyItem->events->filter(fn ($event) => $event->event_type->et_type == 'CLASS')->first()?->toArray()['event_value'];
                    $comment = $historyItem->comments->first()?->dc_text;
                    $header = $comment ?: $classification ?: null;

                    $sessionType = strtoupper($historyItem->session_type->st_type);
                    $sessionDir = strtoupper($historyItem->session_direction->sd_type);
                    $dontDisplay = (in_array($sessionType, ['AGENT', 'PARROT']) && $sessionDir === 'OUTBOUND');
                @endphp
                @continue(!array_key_exists($historyItem->session_id, $historyVisibility) || empty($healthStates) || $dontDisplay)
                <li wire:model="historyVisibility" wire:key="history-header-{{$historyItem->session_id}}" class="border-t border-l border-gray-400">
                    <div :class="[ open === true ? ' bg-gray-400 bg-opacity-10 ' : ' bg-transparent ']" class="">
                        <div wire:click.prevent="toggleHistoryVisibility({{$historyItem->session_id}})" class="cursor-pointer items-center flex justify-between info-container text-medium  px-4 py-4 ml-0 text-sm hover:text-gray-900 has-children" >
                                <span class="flex items-center justify-evenly">

                                    {{-- download record --}}
                                    <div style="width: 1rem;" wire:click.stop="downloadRecord('{{$historyItem->downloadable_record}}')">
                                        @if(!empty($historyItem->downloadable_record))
                                            <span class="icon-wrapper tt" style="height: 1.15rem; display: block;">
                                                <i class="f7-icons icon icon-sm tts" style="font-size: 1rem;">{{ 'speaker_2_fill' }}</i>
                                                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom:1.25; font-family: 'UCP Normal';">{{ __('Download Record') }}</span>
                                            </span>
                                        @endif
                                    </div>

                                    {{-- toggle events details --}}
                                    @if($historyVisibility[$historyItem->session_id] == true)
                                        <span class="icon-wrapper tt">
                                            <i class="tts"><x-monoicon.caret-down class="flex" /></i>
                                            <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom:1.25; font-family: 'UCP Normal';">{{ __('Hide Session Details') }}</span>
                                        </span>
                                    @endif
                                    @if($historyVisibility[$historyItem->session_id] == false)
                                        <span class="icon-wrapper tt">
                                            <i class="tts"><x-monoicon.caret-right class="flex" /></i>
                                            <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom:1.25; font-family: 'UCP Normal';">{{ __('Show Session Details') }}</span>
                                        </span>
                                    @endif

                                    {{-- traffic lights --}}
                                    <span class="flex items-center justify-end space-x-1 mr-4 icon-wrapper tt">
                                        <div class="tts">
                                            <x-devices.history.severity-signals :healthState="$healthStates" />
                                        </div>
                                        <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom:1.25; font-family: 'UCP Normal';">{{ __('Session Status') }}</span>
                                    </span>

                                    <div class="flex justify-between items-center">
                                        {{-- session type --}}
                                        <div style="width: 8rem;">@lang(strtoupper($historyItem->session_type->st_type))</div>

                                        {{-- equipment - remove div to see the tooltip, remember about centering element --}}
                                        <div class="text-gray-400 elip" style="width: 8rem;">
                                           <span class="tt" style="width: 8rem; padding-right: 1rem;">
                                                <div class="tts elip flex items-center">
                                                    <span class="text-gray-400 elip">{{$mapIdEquip[$historyItem->session_device_id] ?? ''}}</span>
                                                </div>
                                                <span class="ttt elip ttt-tt bg-white border border-slate-300 text-dark shadow-md text-sm">{{$mapIdEquip[$historyItem->session_device_id] ?? ''}}</span>
                                            </span>
                                        </div>

                                        {{-- session id --}}
                                        <div class="text-gray-400" style="width: 5rem;">{{$historyItem->session_id}}</div>

                                        {{-- session host --}}
                                        <div class="text-gray-400" style="width: 5.5rem;">{{$historyItem->session_host}}</div>

                                        @if(!empty($header))
                                            {{-- message --}}
                                            <span class="tt" style="width: 26rem;">
                                                <div class="tts elip"><span class="text-gray-400 elip">{{ $header }}</span></div>
                                                <span class="ttt elip ttt-tt bg-white border border-slate-300 text-dark shadow-md text-sm">{{ $header }}</span>
                                            </span>
                                        @endif

                                    </div>
                                </span>
                            <span class="flex items-center justify-end">
                                    <span class="hidden lg:flex  items-center">
                                        @php
                                        $startTime = $historyItem->session_start;
                                        if (gettype($historyItem->session_end) == 'object') {
                                            $endTime = $historyItem->session_end;
                                            $duration = $endTime->diff($startTime);
                                        }
                                        $carbonStart = \Carbon\Carbon::parse($historyItem->session_start)->shiftTimezone('UTC');
                                        @endphp

                                        {{-- session start --}}
                                        <span>{{ App\Helpers\Ucp::toUserTimezone($carbonStart->toAtomString())->format('d.m.Y H:i:s') }}</span>
                                         <x-monoicon.chevron-double-right class="flex w-4 h-4 mx-4" />

                                        {{-- session duration or badge --}}
                                        @if(!empty($duration))
                                            <span>{{ $duration->format('%H:%I:%S') }}</span>
                                        @else
                                            @if($historyItem->session_end == 'pending')
                                                <span class="badge fill pending items-center">{{ $historyItem->session_end }}&nbsp;
                                                    <svg class="animate-spin ml-2 mr-0 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </span>
                                            @elseif($historyItem->session_end == 'failed')
                                                <span class="badge fill error">{{ __('Call') }} {{ $historyItem->session_end }}</span>
                                            @elseif(empty($historyItem->session_end))
                                                <span class="badge fill error">{{ __('empty') }}</span>
                                            @else
                                                {{$historyItem->session_end}}
                                            @endif
                                        @endif
                                    </span>
                                </span>
                        </div>

                        <div wire:key="history-detail-{{$historyItem->session_id}}">

                            {{-- session events details --}}
                            @if($historyVisibility[$historyItem->session_id] == true)
                                @php
                                    $historyDetail = $openedSessionsDetails[$historyItem->session_id] ?? [];
                                    $historyDetail = is_array($historyDetail) ? $historyDetail : $historyDetail->toArray();
                                @endphp
                                <ul class="px-0 ml-10 text-sm transition-all duration-1000 text-medium opacity-0 opacity-100">
                                    @if(array_key_exists('alerts', $historyDetail) && array_key_exists('session_type', $historyDetail))
                                        @if(count($historyDetail['alerts']) > 0)
                                            <li class="bg-white bg-opacity-70 border-t border-l border-gray-400 border-opacitiy-100">
                                                <div class="block lg:flex info-container info text-medium block px-2 py-2 text-sm hover:text-gray-900">
                                                    <div class="block lg:flex">
                                                        <span class=" w-40 inline-block text-left pb-8 lg:text-right pl-8 text-gray-600 tracking-normal text-sm border-none lg:border-r lg:border-solid lg:border-gray-400 pr-4 ">@lang('Alerts')</span>
                                                    </div>

                                                    <div class=" w-full px-8">
                                                        <div class=" grid grid-flow-row sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 auto-rows-max gap-4">
                                                            @foreach($historyDetail['alerts'] as $alert)
                                                                <x-devices.history.session-item :sessionItem="$alert" :type="'alert'" ></x-devices.history.session-item>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                    @endif
                                    @if(array_key_exists('sets', $historyDetail))
                                        @if(count($historyDetail['sets']) > 0)
                                            <li class="bg-white bg-opacity-70 border-t border-l border-gray-400 border-opacitiy-100">
                                                <div class="block lg:flex info-container info text-medium block px-2 py-2 text-sm hover:text-gray-900">
                                                    <div class="block lg:flex">
                                                        <span class="w-40 inline-block text-left pb-8 lg:text-right pl-8 text-gray-600 tracking-normal text-sm border-none lg:border-r lg:border-solid lg:border-gray-400 pr-4 ">@lang('Sets / Revivals')</span>
                                                    </div>

                                                    <div class=" w-full px-8">
                                                        <div class=" grid grid-flow-row sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 auto-rows-max gap-4">
                                                            @foreach($historyDetail['sets'] as $set)
                                                                <x-devices.history.session-item :sessionItem="$set" :type="'set'"  ></x-devices.history.session-item>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                    @endif
                                    @if(array_key_exists('events', $historyDetail))
                                        @if(count($historyDetail['events']) > 0)
                                            <li class="bg-white bg-opacity-70 border-t border-l border-gray-400 border-opacitiy-100">
                                                <div class="block lg:flex info-container info text-medium block px-2 py-2 text-sm hover:text-gray-900">
                                                    <div class="block lg:flex">
                                                        <span class="w-40 inline-block text-left pb-8 lg:text-right pl-8 text-gray-600 tracking-normal text-sm border-none lg:border-r lg:border-solid lg:border-gray-400 pr-4 ">@lang('Events')</span>
                                                    </div>

                                                    <div class=" w-full px-8">
                                                        <div class=" grid grid-flow-row sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 auto-rows-max gap-4">
                                                            @foreach($historyDetail['events'] as $event)
                                                                <x-devices.history.session-item :sessionItem="$event" :type="'event'" :relatedEvents="$relatedEvents" ></x-devices.history.session-item>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                    @endif
                                </ul>
                            @endif
                        </div>

                    </div>
                </li>
            @endforeach

            @if($history->hasMorePages())
                <div class="w-full justify-center relative">
                    <x-button.load-more wire:click.prevent="loadMore" id="load-more">
                        @lang('Load more')
                    </x-button.load-more>
                </div>
            @endif
        </ul>
    </div>

@if(count($relatedEvents) > 0)
    @foreach($relatedEvents as $key => $relatedEvent)
        <div x-data="{ open: true }" class="flex justify-center">

            <!-- Modal -->
            <div
                    x-show="open"
                    style="display: none"
                    x-on:keydown.escape.prevent.stop
                    role="dialog"
                    aria-modal="true"
                    x-id="{{$key}}"
                    :aria-labelledby="{{$key}}"
                    class="fixed inset-0 overflow-y-auto">
                <!-- Overlay -->
                <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>

                <!-- Panel -->
                <div
                        x-show="open" x-transition
                        x-on:click.stop
                        class="relative min-h-screen flex items-center justify-center p-4">
                    <div
                            x-on:click.stop
                            x-trap.noscroll.inert="open"
                            class="relative max-w-2xl w-full bg-white border border-black rounded-lg shadow-lg p-12 overflow-y-auto">
                        <!-- Title -->
                        <h2 class="text-3xl font-bold" :id="{{$key}}">{{ __('Event Detail') }}</h2>
                        <!-- Content -->
                        @foreach($relatedEvent as $related)
                            @php
                                $eventState = 'bg-white border border-gray-200 hover:border-gray-400';
                                if($related['event_severity'] != null) {
                                    switch ($related['event_severity']['es_type']) {
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
                                            if(in_array($related['event_type']['et_type'], ['START','END'])){
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
                            @endphp

                            <div class="eventbox {{ $eventState }} hover:cursor-pointer my-4 relative">
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <p class="label_type">
                                        {{$related['event_type']['et_type']}}
                                    </p>
                                    <p class="label_value">
                                        @if($related['event_value']) {{$related['event_value']}} @else &nbsp; &nbsp; @endif
                                    </p>
                                    <p class="label_time justify-end float-right">
                                        {{ App\Helpers\Ucp::toUserTimezone(\Carbon\Carbon::parse($related['event_timestamp'])->toAtomString())->format('H:i:s') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                        <!-- Buttons -->
                        <div class="mt-8 flex space-x-2">
                            <button type="button" wire:click.prevent="hideRelatedInfo()" class="text-gray-700 text-sm leading-5 text-medium focus:outline-none focus:text-gray-800 focus:underline transition duration-150 ease-in-out secondary small">
                                @lang('Close')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

    <script>
    document.addEventListener('livewire:load', function () {
        @this.requestedSessionId = '{{ $requestedSessionId }}';

        if (@this.requestedSessionId) {
            let sessionElement = document.querySelector(`[wire\\:key="history-header-${@this.requestedSessionId}"]`);
            if (sessionElement) {
                scrollToSession(@this.requestedSessionId);
                @this.requestedSessionId = null;
            } else {
                if (document.getElementById('load-more')) {
                    document.getElementById('load-more').click();
                }
            }
        }

        function scrollToSession(sessionId) {
            let element = document.querySelector(`[wire\\:key="history-header-${sessionId}"]`);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        Livewire.hook('message.processed', (message, component) => {
            if (@this.requestedSessionId) {
                let sessionElement = document.querySelector(`[wire\\:key="history-header-${@this.requestedSessionId}"]`);
                if (sessionElement) {
                    scrollToSession(@this.requestedSessionId);
                    @this.requestedSessionId = null;
                } else {
                    if (document.getElementById('load-more')) {
                        document.getElementById('load-more').click();
                    }
                }
            }
        });
    });
    </script>

</div>