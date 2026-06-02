<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
    <div class="sm:flex sm:items-start">
        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-red-600" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg">@lang('Update CLI Setting')</h3>

            <div class="mt-2">
                <div class="py-8 text-cool-gray-700">
                    <p>{{ __('The primary number has changed. Would you like to update the CLI settings with the new number?') }}</p>
                    <div class="mt-4">
                        <div class="flex items-center gap-2">
                            <span>{{ __('Settings') }}:</span>
                            <span class="font-medium">call.alarm.route1.cli.number & call.outbound.trunk.cli.number</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span>{{ __('Current CLI') }}:</span>
                            <span id="old-number-display" class="font-medium"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span>{{ __('New number') }}:</span>
                            <span id="new-number-display" class="font-medium"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="px-6 py-4 bg-gray-100 text-right">
    <x-button.secondary onclick="this.closest('.popup').querySelector('.popup-close').click(); window.dispatchEvent(new CustomEvent('cliModalResponse', {detail: {updateCli: false}}))">
        {{__('Cancel')}}
    </x-button.secondary>
    <x-button.primary onclick="this.closest('.popup').querySelector('.popup-close').click(); window.dispatchEvent(new CustomEvent('cliModalResponse', {detail: {updateCli: true} }))">
        {{__('Update CLI')}}
    </x-button.primary>
</div>