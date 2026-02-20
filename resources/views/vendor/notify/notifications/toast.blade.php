@if (session()->get('notify.model') === 'toast')
    <div class="notify fixed mt-24 inset-0 flex items-end justify-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end">
        <div
            x-data="{ show: false }"
            x-init="setTimeout(() => { show = true }, 500); @if(config('notify.timeout') != null) setTimeout(() => { show = false }, {{config('notify.timeout')}}) @endif "
            x-show="show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @class([
                'pointer-events-auto w-full max-w-sm bg-white overflow-hidden shadow-lg rounded-none border-l-4',
                'border-green-500' => session()->get('notify.type') === 'success',
                'border-yellow-500' => session()->get('notify.type') === 'warning',
                'border-blue-500' => session()->get('notify.type') === 'info',
                'border-red-500' => session()->get('notify.type') === 'error',
            ])>
            <div class="relative rounded-lg shadow-xs overflow-hidden">
                <div class="p-4">
                    @if(session()->get('notify.type') == 'success')
                        <p class="text-sm leading-5 text-medium uppercase text-green-500 ">{{ session()->get('notify.type') }}</p>
                    @endif
                    @if(session()->get('notify.type') == 'warning')
                        <p class="text-sm leading-5 text-medium uppercase text-orange-500 ">{{ session()->get('notify.type') }}</p>
                    @endif
                    @if(session()->get('notify.type') == 'info')
                        <p class="text-sm leading-5 text-medium uppercase text-blue-500 ">{{ session()->get('notify.type') }}</p>
                    @endif
                    @if(session()->get('notify.type') == 'error')
                        <p class="text-sm leading-5 text-medium uppercase text-red-500 ">{{ session()->get('notify.type') }}</p>
                    @endif
                    <x-notify::notify-content :content="session()->get('notify.message')" />
                </div>
            </div>
        </div>
    </div>
@endif
