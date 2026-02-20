<div class="mt-12 mx-auto w-full px-12 pb-5 pt-2 font-medium">

    <x-page.header class="px-4">
        <x-slot name="title">@lang('User Invitation')</x-slot>
            <x-slot name="description">@lang('Configure invitee account.')</x-slot>
    </x-page.header>

    <div class="px-4 pb-5 w-full mb-8">
        <div x-transition class="pb-8 bottom-underline">
            <div>
                <fieldset class="-mx-1 md:flex mb-1">
                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                        <x-input.group  for="newUser.firstname" id="firstname" label="{{__('First Name')}}" required="required" :error="$errors->first('newUser.firstname')">
                            <x-input.text wire:model="newUser.firstname" class="w-full" required="required" name="newUser.firstname" value="{{$newUser['firstname'] ?? ''}}" />
                        </x-input.group>
                    </div>
                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                        <x-input.group for="newUser.lastname" label="{{__('Last Name')}}" id="lastname" required="required" :error="$errors->first('newUser.lastname')">
                            <x-input.text wire:model="newUser.lastname" class="w-full" required="required" name="newUser.lastname" value="{{$newUser['lastname'] ?? ''}}" />
                        </x-input.group>
                    </div>
                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                        <x-input.group  for="newUser.email" label="{{__('Email')}}" id="email" required="required" :error="$errors->first('newUser.email')">
                            <x-input.text wire:model="newUser.email" class="w-full" required="required" name="newUser.email" value="{{$newUser['email'] ?? ''}}" />
                        </x-input.group>
                    </div>
                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                        <x-input.group  for="newUser.ext" label="{{__('Phone')}}" id="ext" :error="$errors->first('newUser.ext')">
                            <x-input.text wire:model="newUser.ext" class="w-full" name="newUser.ext" value="{{$newUser['ext'] ?? ''}}" />
                        </x-input.group>
                    </div>
                </fieldset>
            </div>

{{--        NEW UPDATED VERSION --}}
            <div class="mt-8 w-full grid grid-col-1 md:grid-cols-2 lg:grid-cols-3 gap-x-4">
                <div class="">
                    <fieldset>
                        <label class="text-base font-bold text-gray-900">{{__('User Type')}}</label>
                        <p class="text-sm text-gray-500 h-24">{{__('Admin User are allowed to configure settings related to your account')}}</p>
                        <legend class="sr-only">{{__('User Type')}}</legend>
                        <div class="space-y-4 md:flex md:items-center space-x-0 md:space-x-10 md:space-y-0 ">
                            <x-form.v2.radio-group>
                                @if(Auth::user()->is_site)
                                    <x-form.v2.radio
                                            :active="$basicRoles['site']"
                                            class="rounded-l-full"
                                            name="basicRoles"
                                            model="basicRoles.site"
                                            id="site"
                                            functionName="updateUserType"
                                            label="Site" />
                                    <x-form.v2.radio
                                            :active="$basicRoles['admin']"
                                            class="rounded-none mr-0.5 ml-0.5"
                                            name="basicRoles"
                                            model="basicRoles.admin"
                                            id="admin"
                                            functionName="updateUserType"
                                            label="Admin" />
                                @else
                                    <x-form.v2.radio
                                            :active="$basicRoles['admin']"
                                            class="rounded-l-full mr-0.5"
                                            name="basicRoles"
                                            model="basicRoles.admin"
                                            id="admin"
                                            functionName="updateUserType"
                                            label="Admin" />
                                @endif
                                <x-form.v2.radio
                                        :active="$basicRoles['user']"
                                        class="rounded-r-full"
                                        name="userTypes"
                                        model="userTypes.user"
                                        id="user"
                                        functionName="updateUserType"
                                        label="User" />
                            </x-form.v2.radio-group>
                        </div>
                    </fieldset>
                </div>
                <div class="px-0 md:px-4 py-4 lg:py-0">
                    <fieldset>
                        <label class="text-base font-bold">{{ __('Optional Permissions') }}</label>
                        <p class="text-sm text-gray-500 h-24">{{__('Agents have access to callcenter features. User with mobile permission are allowed to use the parrot mobile app.')}}</p>
                        <legend class="sr-only">{{ __('Optional Permissions') }}</legend>
                        <div class="space-y-4 md:flex md:items-center space-x-0 md:space-x-10 md:space-y-0">
                            <x-form.v2.checkbox-group>
                                <x-form.v2.checkbox name="optionalRoles" :active="$optionalRoles['agent'] ?? false" model="optionalRoles.agent" id="agent" functionName="updateOptionalRole" label="Agent" />
                                <x-form.v2.checkbox name="optionalRoles" :active="$optionalRoles['mobile'] ?? false" model="optionalRoles.mobile" id="mobile" functionName="updateOptionalRole" label="Mobile" />
                            </x-form.v2.checkbox-group>
                        </div>
                    </fieldset>
                </div>
                <div class="">
                    <label class="text-base font-bold">{{ __('Enable Login') }}</label>
                    <p class="text-sm text-gray-500 h-24">@lang('User allowed to login')</p>
                    <input wire:model="accessAllowed" id="accessAllowed" name="accessAllowed" type="checkbox" class="uiswitch uiswitch-new" >
                </div>
            </div>

        </div>
        <div class=" mt-4 w-full flex justify-end">
            <x-button.primary wire:click.prevent="saveInvite" type="button">@lang('Save')</x-button.primary>
            <x-button.secondary wire:click.prevent="cancelInvite">@lang('Cancel')</x-button.secondary>
        </div>
    </div>
</div>