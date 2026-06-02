{{-- device-history-new-new.blade.php --}}
<div x-data="modalHandler()" x-init="init()" class="deviceinfos @if($leftGap) pl-9 @endif">
    <div class="">
        <x-devices.history-filter-ucp2
                :deviceSite="$deviceSite"
                :siteDevicesIds="$siteDevicesIds"
                :contextOptions="$contextOptions"
                :historyFilter="$historyFilter"
                :severityFilter="$severityFilter"
                :dateFilter="$dateFilter"
                :component-id="$exportComponentId" />
    </div>

{{--    <button wire:click="updateLatestSessionHistoryVisibility">updateLatestSessionHistoryVisibility</button>--}}

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
                    $relatedDir = null;

                    // Hide logic
                    // todo: optimize this approach
                    if ($sessionDir === 'OUTBOUND' && ($historyItem->session_ref_id !== null) && ($historyItem->session?->session_direction !== null)) {
                        $relatedDir = strtoupper($historyItem->session->session_direction->sd_type);
                    }

                    $dontDisplay = (in_array($sessionType, ['AGENT', 'PARROT']) && $sessionDir === 'OUTBOUND') || (!empty($relatedDir) && $relatedDir === 'INBOUND');
                @endphp
                @continue(!array_key_exists($historyItem->session_id, $historyVisibility) || empty($healthStates) || $dontDisplay)
                <li x-data="sessionItem({{ $historyItem->session_id }})" x-init="init()" wire:key="session-{{ $historyItem->session_id }}" class="border-t border-l border-r border-gray-400">
                    <div :class="{ 'bg-transparent': true }">
                        <div @click="toggleDetails()" class="toggle-session-details cursor-pointer items-center flex justify-between info-container text-medium px-4 py-4 ml-0 text-sm hover:text-gray-900 has-children">
                            <span class="flex items-center justify-evenly">
                                <div style="width: 1rem;" wire:click.stop="downloadRecord('{{$historyItem->downloadable_record}}')">
                                    @if(!empty($historyItem->downloadable_record))
                                        <span class="icon-wrapper tt" style="height: 1.15rem; display: block;">
                                            <i class="f7-icons icon icon-sm tts" style="font-size: 1rem;">{{ 'speaker_2_fill' }}</i>
                                            <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom:1.25; font-family: 'UCP Normal';">{{ __('Download Record') }}</span>
                                        </span>
                                    @endif
                                </div>

                                <template x-if="isOpen || loading">
                                    <span class="icon-wrapper tt">
                                        <i class="tts"><x-monoicon.caret-down class="flex" /></i>
                                        <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom:1.25; font-family: 'UCP Normal';">{{ __('Hide Session Details') }}</span>
                                    </span>
                                </template>
                                <template x-if="!isOpen && !loading">
                                    <span class="icon-wrapper tt">
                                        <i class="tts"><x-monoicon.caret-right class="flex" /></i>
                                        <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom:1.25; font-family: 'UCP Normal';">{{ __('Show Session Details') }}</span>
                                    </span>
                                </template>

                                <span class="flex items-center justify-end space-x-1 mr-4 icon-wrapper tt">
                                    <div class="tts">
                                        <x-devices.history.severity-signals :healthState="$healthStates" />
                                    </div>
                                    <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom:1.25; font-family: 'UCP Normal';">{{ __('Session Status') }}</span>
                                </span>

                                <div class="flex justify-between items-center">
                                    <div style="width: 8rem;">@lang(strtoupper($historyItem->session_type->st_type))</div>
                                    <div class="text-gray-400 elip" style="width: 8rem;">
                                       <span class="tt" style="width: 8rem; padding-right: 1rem;">
                                            <div class="tts elip flex items-center">
                                                <span class="text-gray-400 elip">{{$mapIdEquip[$historyItem->session_device_id] ?? ''}}</span>
                                            </div>
                                            <span class="ttt elip ttt-tt bg-white border border-slate-300 text-dark shadow-md text-sm">{{$mapIdEquip[$historyItem->session_device_id] ?? ''}}</span>
                                        </span>
                                    </div>
                                    <div class="text-gray-400" style="width: 5rem;">{{$historyItem->session_id}}</div>
                                    <div class="text-gray-400" style="width: 5.5rem;">{{$historyItem->session_host}}</div>
                                    @if(!empty($header))
                                        <span class="tt" style="width: 26rem;">
                                            <div class="tts elip"><span class="text-gray-400 elip">{{ $header }}</span></div>
                                            <span class="ttt elip ttt-tt bg-white border border-slate-300 text-dark shadow-md text-sm">{{ $header }}</span>
                                        </span>
                                    @endif
                                </div>
                            </span>
                            <span class="flex items-center justify-end">
                                <span class="hidden lg:flex items-center">
                                    @php
                                        $startTime = $historyItem->session_start;
                                        if (gettype($historyItem->session_end) == 'object') {
                                            $endTime = $historyItem->session_end;
                                            $duration = $endTime->diff($startTime);
                                        }
                                        $carbonStart = \Carbon\Carbon::parse($historyItem->session_start)->shiftTimezone('UTC');
                                    @endphp
                                    <span>{{ App\Helpers\Ucp::toUserTimezone($carbonStart->toAtomString())->format('d.m.Y H:i:s') }}</span>
                                    <x-monoicon.chevron-double-right class="flex w-4 h-4 mx-4" />
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


                        <div wire:ignore>
                            <div x-show="loading" class="transition-all duration-1000 px-0 ml-10 w-full h-14 pt-4 bg-transparent flex justify-center items-center border-l border-t border-gray-400">
                                <x-monoicon.loading-indicator></x-monoicon.loading-indicator>
                            </div>


                            <div x-show="isOpen && !loading" class="px-0 ml-10 text-sm transition-all duration-1000 text-medium">

                                <!-- Alerts section -->
                                <template x-if="sessionDetails?.alerts?.length > 0">
                                    <li class="bg-white bg-opacity-70 border-t border-l border-gray-400">
                                        <div class="block lg:flex info-container info text-medium block px-2 py-2 text-sm hover:text-gray-900">
                                            <div class="block lg:flex">
                                                <span class="w-40 inline-block text-left pb-8 lg:text-right pl-8 text-gray-600 tracking-normal text-sm border-none lg:border-r lg:border-solid lg:border-gray-400 pr-4">@lang('Alerts')</span>
                                            </div>
                                            <div class="w-full px-8">
                                                <div class="grid grid-flow-row sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 auto-rows-max gap-4">

                                                    <template x-for="alertItem in sessionDetails.alerts" :key="alertItem.alert_id">
                                                        <div class="eventbox items-start h-full" :class="getItemState('alert', alertItem)">
                                                            <div class="flex-1 min-w-0 overflow-hidden items-start">
                                                                <template x-if="alertItem?.alert_value?.length > 20">
                                                                    <span class="tt-adv">
                                                                        <div class="tts">
                                                                            <p class="label_type" x-text="alertItem?.alert_type?.at_type || 'Unknown'"></p>
                                                                        </div>
                                                                        <span class="ttt-adv ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm elip" x-text="alertItem?.alert_value || '&nbsp;'">
                                                                        </span>
                                                                    </span>
                                                                </template>
                                                                <template x-if="!alertItem?.alert_value || alertItem.alert_value.length <= 20">
                                                                    <p class="label_type" x-text="alertItem?.alert_type?.at_type || 'Unknown'"></p>
                                                                </template>
                                                                <p class="label_value" x-text="alertItem?.alert_value || '&nbsp;'"></p>
                                                                <p class="label_time justify-end float-right" x-text="alertItem?.alert_timestamp ? formatTime(alertItem.alert_timestamp) : ''"></p>
                                                            </div>
                                                        </div>
                                                    </template>

                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>

                                <!-- Sets section -->
                                <template x-if="sessionDetails?.sets?.length > 0">
                                    <li class="bg-white bg-opacity-70 border-t border-l border-gray-400">
                                        <div class="block lg:flex info-container info text-medium block px-2 py-2 text-sm hover:text-gray-900">
                                            <div class="block lg:flex">
                                                <span class="w-40 inline-block text-left pb-8 lg:text-right pl-8 text-gray-600 tracking-normal text-sm border-none lg:border-r lg:border-solid lg:border-gray-400 pr-4">@lang('Sets / Revivals')</span>
                                            </div>
                                            <div class="w-full px-8">
                                                <div class="grid grid-flow-row sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 auto-rows-max gap-4">

                                                    <template x-for="setItem in sessionDetails.sets" :key="setItem.set_id">
                                                        <div class="eventbox items-start h-full" :class="getItemState('set', setItem)">
                                                            <div class="flex-1 min-w-0 overflow-hidden items-start">
                                                                <template x-if="setItem?.set_value?.length > 20">
                                                                    <span class="tt-adv">
                                                                        <div class="tts">
                                                                            <p class="label_type" x-text="translateSetting(setItem.setting.setting_key) || 'Unknown'"></p>
                                                                        </div>
                                                                        <span class="ttt-adv ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm elip" x-text="setItem.set_value || '&nbsp;'">
                                                                        </span>
                                                                    </span>
                                                                </template>
                                                                <template x-if="!setItem?.set_value || setItem.set_value.length <= 20">
                                                                    <p class="label_type" x-text="translateSetting(setItem.setting.setting_key) || 'Unknown'"></p>
                                                                </template>
                                                                <p class="label_value" x-text="setItem.set_value || '&nbsp;'"></p>
                                                                <p class="label_time justify-end float-right" x-text="setItem.set_timestamp ? formatTime(setItem.set_timestamp) : ''"></p>
                                                            </div>
                                                        </div>
                                                    </template>

                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>

                                <!-- Events section -->
                                <template x-if="sessionDetails?.events?.length > 0">
                                    <li class="bg-white bg-opacity-70 border-t border-l border-gray-400">
                                        <div class="block lg:flex info-container info text-medium block px-2 py-2 text-sm hover:text-gray-900">
                                            <div class="block lg:flex">
                                                <span class="w-40 inline-block text-left pb-8 lg:text-right pl-8 text-gray-600 tracking-normal text-sm border-none lg:border-r lg:border-solid lg:border-gray-400 pr-4">@lang('Events')</span>
                                            </div>
                                            <div class="w-full px-8">
                                                <div class="grid grid-flow-row sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 auto-rows-max gap-4">

                                                    <template x-for="event in sessionDetails.events" :key="event.event_id">
                                                        <div class="eventbox items-start h-full"
                                                             :class="getItemState('event', event)"
                                                             @click="event?.event_type?.et_type?.toUpperCase() === 'CNUMBER' && showRelatedInfo(event.event_session_id, event.event_timestamp)">
                                                            <div class="flex-1 min-w-0 overflow-hidden items-start">
                                                                <template x-if="event?.event_value?.length > 40">
                                                                    <span class="">
                                                                        <div class="">
                                                                            <p class="label_type flex justify-between">
                                                                                <span x-text="event?.event_type?.et_type || 'Unknown'"></span>
                                                                                <template x-if="event?.event_type?.et_type?.toUpperCase() === 'CNUMBER'">
                                                                                    <x-monoicon.circle-information></x-monoicon.circle-information>
                                                                                </template>
                                                                            </p>
                                                                        </div>
                                                                        <div class="tt-adv">
                                                                            <div class="tts">
                                                                                <p class="label_value" x-text="event?.event_value || '&nbsp;'"></p>
                                                                            </div>
                                                                            <span class="ttt-adv ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" x-text="event?.event_value || '&nbsp;'"></span>
                                                                        </div>
                                                                    </span>
                                                                </template>
                                                                <template x-if="!event?.event_value || event.event_value.length <= 40">
                                                                    <p class="label_type flex justify-between">
                                                                        <span x-text="event?.event_type?.et_type || 'Unknown'"></span>
                                                                        <template x-if="event?.event_type?.et_type?.toUpperCase() === 'CNUMBER'">
                                                                            <x-monoicon.circle-information></x-monoicon.circle-information>
                                                                        </template>
                                                                    </p>
                                                                </template>
                                                                <p class="label_value" x-text="((event?.event_value?.length || 0) <= 40) ? (event?.event_value || '&nbsp;') : ''"></p>
                                                                <p class="label_time justify-end float-right" x-text="event?.event_timestamp ? formatTime(event.event_timestamp) : ''"></p>
                                                            </div>
                                                        </div>
                                                    </template>

                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>

                            </div>
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


    <!-- modal rendered with alpine -->
    <div x-show="relatedEventsVisible" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Overlay -->
        <div x-show="relatedEventsVisible" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>
        <!-- Panel -->
        <div x-show="relatedEventsVisible"
             x-transition
             class="relative max-w-2xl w-full bg-white rounded-lg shadow-lg p-12 overflow-y-auto">
            <!-- Title -->
            <h2 class="text-3xl font-bold mb-8">{{ __('Event Detail') }}</h2>
            <!-- Content -->
            <div class="space-y-4">
                <template x-for="([key, events], index) in Object.entries(globalRelatedEvents)" :key="key">
                    <div>
                        <template x-for="event in events" :key="event.event_id">
                            <div class="eventbox relative my-4" :class="getItemState('event', event)">
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <p class="label_type" x-text="event?.event_type?.et_type || 'Unknown'"></p>
                                    <p class="label_value" x-text="event?.event_value || '&nbsp;'"></p>
                                    <p class="label_time justify-end float-right" x-text="formatTime(event?.event_timestamp)"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Buttons -->
            <div class="mt-8">
                <button @click="relatedEventsVisible = false"
                        class="text-gray-700 text-sm leading-5 text-medium focus:outline-none focus:text-gray-800 focus:underline transition duration-150 ease-in-out secondary small">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>


    @push('scripts')
    <script>
        window.settingsTranslationsWeb = {};

        document.addEventListener("DOMContentLoaded", () => {
            fetch('/data/translations', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                window.settingsTranslationsWeb = data;
            })
            .catch(error => {
                console.error("Error loading translations:", error);
            });
        });
    </script>


    <script>
        function translateSetting(settingKey) {
            const normalized = settingKey.replace(/\./g, '_');
            return window.settingsTranslationsWeb[`label.${normalized}`] || settingKey;
        }

        function formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString();
        }

        function getItemState(type, item) {
            if (!item) return '';

            if (type === 'alert') {
                if (item?.alert_active) {
                    return item?.alert_type?.alert_severity?.as_type === 'MAJOR' ? 'error' : 'warning';
                }
                return 'success';
            }

            if (type === 'set' && item) {
                return item?.set_success ? 'success' : 'error';
            }

            if (type === 'event') {
                if (!item?.event_severity) return 'bg-white border border-gray-200 hover:border-gray-400';

                switch (item?.event_severity?.es_type) {
                    case 'CRITICAL': return 'critical';
                    case 'ERROR': return 'error';
                    case 'WARNING': return 'warning';
                    case 'NOTICE': return 'notice';
                    case 'INFO':
                        return ['START', 'END'].includes(item?.event_type?.et_type) ? 'startend' : 'info';
                    default:
                        return 'bg-white border border-gray-200 hover:border-gray-400';
                }
            }

            return '';
        }

        function modalHandler() {
            return {
                globalRelatedEvents: {},
                relatedEventsVisible: false,
                formatTime(timestamp) {
                    return new Date(timestamp).toLocaleTimeString();
                },
                init() {
                    document.addEventListener('show-related-events', event => {
                        this.globalRelatedEvents = event.detail.data;
                        this.relatedEventsVisible = true;
                    });
                }
            }
        }

        function sessionItem(sessionId) {
            return {
                isOpen: false,
                sessionDetails: null,
                loading: false,
                async toggleDetails() {
                    if (!this.isOpen && !this.sessionDetails) {
                        this.loading = true;
                        try {
                            const response = await fetch(`/api/sessions/${sessionId}/details`);
                            const result = await response.json();
                            if (result.success) {
                                this.sessionDetails = result.data;
                            }
                        } catch (error) {
                            console.error('Error loading session details:', error);
                        }
                        this.loading = false;
                    }
                    this.isOpen = !this.isOpen;
                },
                async refreshDetails() {
                    if (
                        this.sessionDetails &&
                        (
                            !this.sessionDetails.session_end ||
                            this.sessionDetails.session_end === 'pending'
                        )
                    ) {
                        // this.loading = true;
                        try {
                            const response = await fetch(`/api/sessions/${sessionId}/details`);
                            const result = await response.json();
                            if (result.success) {
                                this.sessionDetails = result.data;
                            }
                        } catch (error) {
                            console.error('Error refreshing session details:', error);
                        }
                        // this.loading = false;
                    }
                },
                async showRelatedInfo(sessionId, timestamp) {
                    try {
                        const response = await fetch('/api/sessions/related-events', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({
                                session_ref_id: sessionId,
                                timestamp: timestamp
                            })
                        });
                        const result = await response.json();
                        if (result.success) {
                            document.dispatchEvent(
                              new CustomEvent('show-related-events', {
                                detail: { data: result.data },
                                bubbles: true
                              })
                            );
                        }
                    } catch (error) {
                        console.error('Error loading related events:', error);
                    }
                },
                init() {
                    document.addEventListener('refresh-open-sessions', () => {
                        this.refreshDetails();
                    });

                    // document.addEventListener('open-session-details', (e) => {
                    //     if (parseInt(e.detail.sessionId) === sessionId && !this.isOpen) {
                    //         this.toggleDetails();
                    //     }
                    // });
                }
            }
        }
    </script>

    <script>
        function initAdvancedTooltips() {
          const HORIZONTAL_RATIO = 0.4;
          const VERTICAL_RATIO = 0.4;

          document.addEventListener('mouseover', function(e) {
            const trigger = e.target.closest('.tt-adv .tts');
            if (!trigger) return;

            const tooltip = trigger.nextElementSibling;
            if (!tooltip || !tooltip.classList.contains('ttt-adv')) return;

            const triggerRect = trigger.getBoundingClientRect();

            // Position with ratio-based corrections for both directions
            let horizontalOffset = triggerRect.left * HORIZONTAL_RATIO;
            let verticalOffset = triggerRect.top * VERTICAL_RATIO;

            let top = triggerRect.top + verticalOffset - 10;
            let left = triggerRect.left + horizontalOffset;

            // Apply position
            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
          });
        }

        document.addEventListener('DOMContentLoaded', initAdvancedTooltips);
        document.addEventListener('livewire:load', initAdvancedTooltips);
    </script>



    @endpush

    <script>
        document.addEventListener('livewire:load', function () {
            @this.requestedSessionId = '{{ $requestedSessionId }}';

            if (@this.requestedSessionId) {
                let sessionElement = document.querySelector(`[wire\\:key="session-${@this.requestedSessionId}"]`);
                if (sessionElement) {
                    scrollToSession(@this.requestedSessionId);
                    // setTimeout(() => {
                    //     document.dispatchEvent(new CustomEvent('open-session-details', {
                    //         detail: { sessionId: @this.requestedSessionId },
                    //         bubbles: true
                    //     }));
                    // }, 500);
                    setTimeout(() => {
                        const toggleEl = sessionElement.querySelector('.toggle-session-details');
                        if (toggleEl) {
                            toggleEl.click();
                        }
                    }, 3000);
                    @this.requestedSessionId = null;
                } else {
                    if (document.getElementById('load-more')) {
                        document.getElementById('load-more').click();
                    }
                }
            }

            function scrollToSession(sessionId) {
                let element = document.querySelector(`[wire\\:key="session-${sessionId}"]`);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            Livewire.hook('message.processed', (message, component) => {
                if (@this.requestedSessionId) {
                    let sessionElement = document.querySelector(`[wire\\:key="session-${@this.requestedSessionId}"]`);
                    if (sessionElement) {
                        scrollToSession(@this.requestedSessionId);
                        // setTimeout(() => {
                        //     document.dispatchEvent(new CustomEvent('open-session-details', {
                        //         detail: { sessionId: @this.requestedSessionId },
                        //         bubbles: true
                        //     }));
                        // }, 500);
                        setTimeout(() => {
                            const toggleEl = sessionElement.querySelector('.toggle-session-details');
                            if (toggleEl) {
                                toggleEl.click();
                            }
                        }, 3000);
                        @this.requestedSessionId = null;
                    } else {
                        if (document.getElementById('load-more')) {
                            document.getElementById('load-more').click();
                        }
                    }
                }

                document.dispatchEvent(new CustomEvent('refresh-open-sessions'));
            });
        });
    </script>
</div>
