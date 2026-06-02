@php
    // Fail-fast: Ensure user is authenticated
    if (!auth()->check()) {
        \Log::critical('Alarm notification component rendered for unauthenticated user');
        throw new \Exception('Alarm notification component requires authentication');
    }

    // Fail-fast: Ensure account session exists
    if (!session('account.id')) {
        \Log::critical('Alarm notification: Missing account session', [
            'user_id' => auth()->user()->user_id
        ]);
        throw new \Exception('Invalid session state: Missing account ID');
    }

    // Fail-fast: Ensure user ID exists
    if (!auth()->user()->user_id) {
        \Log::critical('Alarm notification: Authenticated user missing user_id', [
            'user' => auth()->user()
        ]);
        throw new \Exception('Invalid user state: Missing user ID');
    }
@endphp

<div x-data="{
        open: false,
        alarms: [],

        init() {
            // Initialize global store if not exists
            if (!window.alarmNotificationsStore) {
                window.alarmNotificationsStore = {
                    alarms: [],
                    initialized: false,
                    listeners: []
                };
            }

            // Subscribe to store changes
            const updateAlarms = () => {
                this.alarms = window.alarmNotificationsStore.alarms;
            };
            window.alarmNotificationsStore.listeners.push(updateAlarms);

            // Only fetch if not already initialized (first component wins)
            if (!window.alarmNotificationsStore.initialized) {
                window.alarmNotificationsStore.initialized = true;
                this.fetchCurrentAlarms();

                // Listen for realtime updates
                document.addEventListener('alarm-data-updated', (event) => {
                    if (event.detail.accountId === {{ session('account.id') }}) {
                        this.updateStore(event.detail.alarmCalls || []);
                    }
                });
            } else {
                // Already initialized - sync from store
                this.alarms = window.alarmNotificationsStore.alarms;
            }
        },

        updateStore(newAlarms) {
            window.alarmNotificationsStore.alarms = newAlarms;
            // Notify all listeners
            window.alarmNotificationsStore.listeners.forEach(fn => fn());
        },

        async fetchCurrentAlarms() {
            try {
                const response = await fetch('/api/alarm-notifications/current');
                const data = await response.json();
                if (data.success) {
                    this.updateStore(data.alarms || []);
                }
            } catch (error) {
                console.error('Failed to fetch alarms:', error);
            }
        },

        formatEquipmentLine(alarm) {
            const address = alarm.device_site?.address?.in_one_line || null;
            const equipment = alarm.device_equipment || null;
            if (equipment && address) {
                return `${equipment}, ${address}`;
            }
            return equipment || address || '';
        },

        formatPhoneType(alarm) {
            const phoneType = alarm.device_site?.single_number?.type || null;
            return phoneType ? `${phoneType.charAt(0).toUpperCase() + phoneType.slice(1)}:` : '';
        }
     }"
     x-init="init()"
     class="flex justify-end px-8 text-gray-600"
     :class="{ 'text-opacity-20': alarms.length === 0 }"
     style="z-index: 99999;">

    <template x-if="alarms.length === 0">
        <x-form.icon :icon="'bell_slash'" :color="'disabled'" :size="'xl'"/>
    </template>

    <template x-if="alarms.length > 0">
        <div class="relative">
            <div class="relative px-0 dropdown cursor-pointer"
                 x-on:keydown.window.escape="open = false"
                 x-on:click.away="open = false">
                <div x-on:click="open = !open" class="with_text" aria-haspopup="true" :aria-expanded="open">
                    <div x-show="alarms.length > 0" class="absolute bottom-auto left-auto right-0 top-0 z-10 mt-2 rounded-full bg-red-700 text-white p-0.5 w-5 h-5 flex items-center justify-center text-xs" x-text="alarms.length"></div>
                    <x-form.icon :icon="'bell_fill'" :color="'blue'" :size="'xl'"></x-form.icon>
                    <div class="origin-top-right absolute right-0 mt-1 shadow-md" x-show="open" style="display: none; width: 59rem;" x-on:click="open = false">
                        <div class="bg-white shadow-md border border-gray-300 absolute top-auto left-0 min-w-full z-40" x-show="open" style="display: none; width: 68rem;">
                            <div class="bg-white w-full relative z-40 py-1">
                                <ul class="list-reset divide-y divide-gray-200">
                                    <template x-for="(alarm, index) in alarms" :key="index">
                                        <li class="relative">
                                            <a :href="`/callcenter/${alarm.device_id}`" class="group px-4 py-2 flex justify-between items-center hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                <span class="flex">
                                                    <span class="text-gray-400 group-hover:text-white">{{ __('Equipment') }}:&nbsp;</span>
                                                    <span class="text-gray-500 group-hover:text-white" x-text="formatEquipmentLine(alarm)"></span>
                                                </span>
                                                <span class="flex">
                                                    <span class="text-gray-400 group-hover:text-white w-11" x-text="formatPhoneType(alarm)"></span>
                                                    <span class="flex text-gray-500 group-hover:text-white w-32" x-text="alarm.device_site?.single_number?.value || ''"></span>
                                                    <x-monoicon.chevron-right class="ml-2 text-gray-400 group-hover:text-white"/>
                                                </span>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
