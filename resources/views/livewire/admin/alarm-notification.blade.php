{{-- DEPRECATED --}}
<div x-data="{
        init() {
            // Listen for realtime updates (WebSocket auto-initialized in realtime.js)
            document.addEventListener('alarm-data-updated', (event) => {
                if (event.detail.accountId === {{ session('account.id') }}) {
                    Livewire.emit('alarm-data-updated', event.detail);
                }
            });
        }
     }"
     x-init="init()">

    {{-- Desktop view (hidden on mobile) --}}
    <div class="hidden lg:flex justify-end px-8 text-gray-600 @if(count($alarmCalls) == 0) text-opacity-20 @endif" style="z-index: 99999;">
        @include('livewire.admin.partials.alarm-notification-bell')
    </div>

    {{-- Mobile view (hidden on desktop) --}}
    <div class="flex lg:hidden justify-end px-8 text-gray-600 @if(count($alarmCalls) == 0) text-opacity-20 @endif" style="z-index: 99999;">
        @include('livewire.admin.partials.alarm-notification-bell')
    </div>
</div>

{{-- <x-dropdown.item type="button" wire:click.prevent="takeAlarmCall({{$alarmCall['devices'][0]['device_id']}})" >{{$alarmCall['pstn']['number_value']}}...</x-dropdown.item> --}}