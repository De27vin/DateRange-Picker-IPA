@props(['accountSettings'])
<div class="pb-12 mx-auto px-4">

    <x-page.header class="mt-8 mb-2 h-20">
        <x-slot name="title">@lang('Account Settings')</x-slot>
        <x-slot name="description">@lang('Settings defined here serves as fallback values for higher order hierarchy levels.')</x-slot>
        <x-slot name="actionButtons">
            @if(count($accountSettings))
                <div class="flex justify-end mb-2 gap-2">
                    <x-button.primary wire:click="updateSettings">@lang('Update')</x-button.primary>
                    <x-button.secondary wire:click="cancelSettings">@lang('Cancel')</x-button.secondary>
                </div>
            @endif
        </x-slot>
    </x-page.header>

    <ul class="-ml-10 pl-10">
        <li>
            <div class="w-full pt-4">

                <x-settings.advanced-settings
                    :advancedSettings="$accountSettings"
                    :updateMethodName="'updateSettings'"
                    :model="'accountSettings'"
                ></x-settings.advanced-settings>

            </div>
        </li>

    </ul>
</div>
