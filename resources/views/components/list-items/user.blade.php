@props([
    'user' => null,
    'allowLoginChange' => false,
    ])
<div class="relative flex flex-col text-sm bg-white border border-slate-300 bg-opacity-40 mx-auto my-4 pl-8 pr-4 pb-4 pt-2">
    <div class="min-w-0 flex items-center">
        <div class="w-full sm:grid grid sm:grid-rows-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 md:gap-4">
            <div class="sm:col-span-1 md:col-span-2 lg:col-span-2 text-base font-bold ">{{$user['name']}}</div>
            <div class="items-center flex space-x-4 flex-wrap sm:col-span-2 md:col-span-2 lg:col-span-4">
                @if($user['allowSite'])
                    <x-form.badge :size="'sm'" class="rounded-sm">{{ __('site') }}</x-form.badge>
                @else
                    @if($user['allowAdmin'])
                        <x-form.badge :size="'sm'" class="rounded-sm">{{ __('admin') }}</x-form.badge>
                    @else
                        <x-form.badge :size="'sm'" class="rounded-sm">{{ __('user') }}</x-form.badge>
                    @endif
                @endif
                @foreach($user['optionalRoles'] as $role)
                    <x-form.badge :size="'sm'" class="rounded-sm">{{ __($role) }}</x-form.badge>
                @endforeach
            </div>
            <div class="sm:col-span-1 md:col-span-2 lg:col-span-2 text-base ">{{$user['phone']}}</div>
            <div class="items-center flex space-x-4 flex-wrap sm:col-span-2 md:col-span-2 lg:col-span-4">
                @foreach($user['emails'] as $email)
                    <div class="mr-4" >{{ $email['email_address'] }}</div>
                @endforeach
            </div>
        </div>
        <span class="px-4 whitespace-nowrap">{{ __('Enable Login') }}</span>
        <input wire:click='toggleActiveState({{$user["user_id"]}})' type="checkbox" class="uiswitch uiswitch-new" @if($user['allowLogin'] == 1) checked @endif @if(!$allowLoginChange) disabled="disabled" @endif>
        <x-form.button wire:click.prevent="editUser({{$user['user_id']}})" class="ml-8" color="transparent" :size="'3xl'" :icon="'ellipsis'"></x-form.button>
    </div>
</div>
