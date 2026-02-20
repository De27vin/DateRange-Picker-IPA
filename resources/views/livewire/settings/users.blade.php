<div class="mt-16 mx-auto pb-12 px-12">

    <x-page.header class="px-4">
        <x-slot name="title">@lang('User Management')</x-slot>
        <x-slot name="description">
            @lang('Users can be added to accounts by sending an invite email after storing user data. Expired invites can be refreshed.')<br/>
            @lang('Users can be blocked from login by disable their state.')
        </x-slot>
    </x-page.header>

    <div class="w-full px-4">
        <div x-cloak x-data="{ openTab: @this.entangle('openTab') }" wire:key="users-tabs" class="py-8">
            <div class="w-full">
                <div class="bottom-underline mb-4 flex justify-between bg-white bg-opacity-60">
                    <div class="flex max-w-2xl space-x-4 p-2 pr-4">
                        <button x-on:click="openTab = 'users'" :class="{ 'bg-color-new-400 text-white': openTab === 'users' }" class="flex-1 py-2 focus:outline-none focus:shadow-outline-blue transition-all duration-300 hover:bg-color-new hover:text-white">{{ __('Users') }}</button>
                        <button x-on:click="openTab = 'site-users'" :class="{ 'bg-color-new-400 text-white': openTab === 'site-users' }" @if(isNumberInBetween($authBinaryRoles, 1, 11)) disabled @endif class="@if(isNumberInBetween($authBinaryRoles, 1, 11)) disabled @endif flex-1 py-2 focus:outline-none focus:shadow-outline-blue transition-all duration-300 hover:bg-color-new hover:text-white">{{ __('Site-Users') }}</button>
                        <button x-on:click="openTab = 'invitations'" :class="{ 'bg-color-new-400 text-white': openTab === 'invitations' }" @if(isNumberInBetween($authBinaryRoles, 1, 1)) disabled @endif class="@if(isNumberInBetween($authBinaryRoles, 1, 1)) disabled @endif lex-1 py-2 focus:outline-none focus:shadow-outline-blue transition-all duration-300 hover:bg-color-new hover:text-white">{{ __('Invites') }}</button>
                    </div>
                    <div class="flex items-center px-4">
                        <button wire:click.prevent="addUser()" @if(isNumberInBetween($authBinaryRoles, 1, 1)) disabled @endif class="@if(isNumberInBetween($authBinaryRoles, 1, 1)) disabled @endif flex-1 py-2 transition-all duration-300 bg-color-new-400 hover:bg-color-new text-white cursor-pointer"><x-monoicon.add /></button>
                    </div>
                </div>
            </div>
            <div class="w-full mx-auto mt-12">
                <div x-show="openTab === 'users'" class="transition-all duration-300">
                    @foreach($users as $user)
                        <x-list-items.user :user="$user" wire:key="users-{{$user['user_id']}}" :allowLoginChange="!isNumberInBetween($authBinaryRoles,1,1)"/>
                    @endforeach
                </div>
                <div x-show="openTab === 'site-users'" class="w-full transition-all duration-300">
                    @if(!empty($authBasicRoles['site']))
                        @foreach($siteUsers as $user)
                            <x-list-items.user :user="$user" wire:key="site-users-{{$user['user_id']}}" :allowLoginChange="!isNumberInBetween($authBinaryRoles,1,1)"/>
                        @endforeach
                    @endif
                </div>
                <div x-show="openTab === 'invitations'" class="w-full transition-all duration-300">
                    <ul class="bg-transparent">
                        @forelse($invites as $invite)
                            <x-list-items.invite wire:key="invitations-{{$user['user_id']}}" :invite="$invite" />
                                @empty
                            <li class="list-item">
                                <x-form.banner color="secondary" fullWidth="true" size="large">{{ __('Currently no invites found') }}</x-form.banner>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>