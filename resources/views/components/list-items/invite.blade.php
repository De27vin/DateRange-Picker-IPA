@props([
    'invite' => []
])
<li wire:loading.class="opacity-20" :key="{{$invite['invite_id']}}" class="list-item">
    <div class="block my-4 bg-white border border-slate-300 bg-opacity-40">
            <div class="flex items-center pl-6 pr-2 md:pl-4 md:pr-1 py-4">
                <div class="min-w-0 flex-1 flex items-center _text-sm text-gray-800 text-medium">
                    <div class="min-w-0 flex-1 pr-4 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-4">
                        <div class="col-span-1 flex flex-col">
                            <p class="flex text-color-new-600 truncate justify-start font-bold">
                                {{$invite['invite_firstname']}} {{$invite['invite_lastname']}}
                            </p>
                            <p class="flex mt-2 text-gray-800 flex items-center justify-start">
                                {{$invite['invite_email']}}
                            </p>
                        </div>
                        <div class="col-span-1 md:col-span-2 flex flex-col justify-end">
                            <p class="items-center flex justify-between">
                                <span class="flex text-gray-800"></span>
                                <span class="flex text-gray-800"><i class="text-gray-400 pr-4">@lang('Expires at'):</i>{{ toUserDateTime($invite['invite_expire'], Auth::user())}}</span>
                            </p>
                            <p class="mt-2 flex items-center justify-between">
                                <span class="flex">
{{--                                    TODO: sub-tenant logic to remove after new permissions logic introduced--}}
{{--                                    @foreach($invite['roles'] as $role)--}}
{{--                                        <x-form.badge :size="'sm'" class="rounded-sm mr-4">{{ __($role['role_type']) }}</x-form.badge>--}}
{{--                                    @endforeach--}}
                                    @if($invite['allowSite'])
                                        <x-form.badge :size="'sm'" class="rounded-sm mr-4">{{ __('site') }}</x-form.badge>
                                    @elseif($invite['allowAdmin'])
                                        <x-form.badge :size="'sm'" class="rounded-sm mr-4">{{ __('admin') }}</x-form.badge>
                                    @elseif($invite['isSubtenant'])
                                        <x-form.badge :size="'sm'" class="rounded-sm mr-4">{{ __('sub-tenant') }}</x-form.badge>
                                    @else
                                        <x-form.badge :size="'sm'" class="rounded-sm mr-4">{{ __('user') }}</x-form.badge>
                                    @endif
                                    @foreach($invite['optionalRoles'] as $role)
                                        <x-form.badge :size="'sm'" class="rounded-sm mr-4">{{ __($role) }}</x-form.badge>
                                    @endforeach
                                </span>
                                <span class="text-gray-800 uppercase flex">
                                    <x-form.badge :size="'sm'" :color="$invite['badgecolor']">{{ __($invite['invite_state']) }}</x-form.badge>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                @if($invite['invite_state'] == 'successful' || $invite['invite_state'] == 'pending')
                    <x-form.button disabled="true" color="transparent" icon="arrow_counterclockwise"></x-form.button>
                    {{-- <div class="opacity-20 w-10"><x-monoicon.refresh /></div> --}}
                @else
                <x-form.button wire:click="refreshInvite({{$invite['invite_id']}})" color="transparent" icon="arrow_counterclockwise"></x-form.button>
                    {{-- <button type="button" wire:click="refreshInvite({{$invite['invite_id']}})" class="w-10"><x-monoicon.refresh /></button> --}}
                @endif

                <x-form.button color="transparent" wire:click="deleteInvite({{$invite['invite_id']}})" icon="trash"></x-form.button>
                {{-- <button type="button" wire:click="deleteInvite({{$invite['invite_id']}})"><x-monoicon.trash /></button> --}}
            </div>

    </div>
</li>
