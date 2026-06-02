@if(!is_null($editUser))

@php
    $roleState = (intval($basicRoles['site']) * 100);
    $roleState = (intval($basicRoles['admin']) * 10 + intval($roleState));
    $roleState = (intval($basicRoles['user']) + intval($roleState));
    $sameUser = $editUser['user_id'] === Auth::user()->user_id;
@endphp

<div class="mt-12 mx-auto w-full px-12 pb-5 font-medium">

    <x-page.header class="px-4">
        <x-slot name="title">@lang('User Information')</x-slot>
        <x-slot name="description">@lang('Edit and view user information.')</x-slot>
    </x-page.header>

    <div class=" px-4 pb-5 w-full mb-8">
        <div x-transition class="pb-8 bottom-underline">
            <div>
                <fieldset class="-mx-1 md:flex mb-1">
                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                        <x-input.group  for="editUser.user_firstname" id="firstname" label="{{__('First Name')}}" required="required" :error="$errors->first('editUser.user_firstname')">
                            <x-input.text wire:model.defer="editUser.user_firstname" class="w-full" required="required" name="editUser.user_firstname" value="{{$editUser['user_firstname'] ?? ''}}" :disabled="(compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)) && !$sameUser" />
                        </x-input.group>
                    </div>
                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                        <x-input.group for="editUser.user_lastname" label="{{__('Last Name')}}" id="lastname" required="required" :error="$errors->first('editUser.user_lastname')">
                            <x-input.text wire:model.defer="editUser.user_lastname" class="w-full" required="required" name="editUser.user_lastname" value="{{$editUser['user_lastname'] ?? ''}}" :disabled="(compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)) && !$sameUser" />
                        </x-input.group>
                    </div>
                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                        <x-input.group  for="editUser.emails.0.email_address" label="{{__('Email')}}" id="email" required="required" :error="$errors->first('editUser.emails.0.email_address')">
                            <x-input.text wire:model.defer="editUser.emails.0.email_address" class="w-full" required="required" name="editUser.emails.0.email_address" value="{{$editUser['emails'][0]['email_address'] ?? ''}}" :disabled="(compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)) && !$sameUser" />
                        </x-input.group>
                    </div>
                    <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                        <x-input.group  for="editUser.user_ext" label="{{__('Phone')}}" id="ext" :error="$errors->first('editUser.user_ext')">
                            <x-input.text wire:model.defer="editUser.user_ext" class="w-full" name="editUser.user_ext" value="{{$editUser['user_ext'] ?? ''}}" :disabled="(compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)) && !$sameUser" />
                        </x-input.group>
                    </div>
                </fieldset>
            </div>

            <div class="mt-8 w-full grid grid-col-1 md:grid-cols-2 lg:grid-cols-3 gap-x-4">
                <div class="">
                    <fieldset>
                        <label class="text-base font-bold text-gray-900">{{__('User Type')}}</label>
                        <p class="text-sm text-gray-500 h-24">{{__('Admin User are allowed to configure settings related to your account')}}</p>
                        <legend class="sr-only">{{__('User Type')}}</legend>
                        <div class="space-y-4 md:flex md:items-center space-x-0 md:space-x-10 md:space-y-0 ">
                            <x-form.v2.radio-group>
                                    @if($authBasicRoles['site'])
                                    <x-form.v2.radio
                                        :active="isNumberInBetween($roleState, 100, 111)"
                                        :disabled="compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0"
                                        class="rounded-l-full"
                                        name="basicRoles"
                                        model="basicRoles.site"
                                        id="site"
                                        functionName="updateUserType"
                                        label="{{ __('site') }}"
                                    />
                                    @endif
                                    <x-form.v2.radio
                                        :active="isNumberInBetween($roleState, 10, 11)"
                                        :disabled="isNumberInBetween($authBinaryRoles, 1, 1)"
                                        :rl="isNumberInBetween($authBinaryRoles, 1, 11)"
                                        class="mr-0.5 ml-0.5"
                                        name="basicRoles"
                                        model="basicRoles.admin"
                                        id="admin"
                                        functionName="updateUserType"
                                        label="{{ __('admin') }}"
                                    />
                                    <x-form.v2.radio
                                        :active="isNumberInBetween($roleState,1,1)"
                                        :disabled="compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)"
                                        :class="$this->isSubtenantAccount() ? 'rounded-none mr-0.5 ml-0.5' : 'rounded-r-full'"
                                        name="basicRoles"
                                        model="basicRoles.user"
                                        id="user"
                                        functionName="updateUserType"
                                        label="{{ __('user') }}"
                                    />
                                    @if($this->isSubtenantAccount())
                                        <x-form.v2.radio
                                            :active="$this->isSubtenantUser()"
                                            class="rounded-r-full"
                                            name="basicRoles"
                                            model="basicRoles.subtenant"
                                            id="subtenant"
                                            functionName="updateUserType"
                                            label="Sub-tenant"
                                        />
                                    @endif
                                </x-form.v2.radio-group>
                        </div>
                    </fieldset>

                    @if($this->isSubtenantUser())
                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                        <x-input.group for="subtenantTag" label="{{__('Sub-tenant Company Name')}}" id="subtenantTag" required="required" :error="$errors->first('subtenantTag')">
                            <x-input.text wire:model="subtenantTag" class="w-full" required="required" name="subtenantTag" placeholder="{{__('Enter company name')}}" />
                        </x-input.group>
                        <p class="text-sm text-yellow-700 mt-1">@lang('This user will only have access to devices assigned to this company via mobile application.')</p>
                    </div>
                    @endif
                </div>
                <div class="px-0 md:px-4 py-4 lg:py-0">
                    <fieldset>
                        <label class="text-base font-bold">{{ __('Optional Permissions') }}</label>
                        <p class="text-sm text-gray-500 h-24">
                            {{__('Agents have access to callcenter features. Mobile users can use the Parrot app. ManDown users can use the ManDown safety monitoring app.')}}
                            @if($this->isSubtenantUser())
                                <span class="block mt-2 text-orange-600">{{__('Note: Mobile role is required for Sub-tenant users.')}}</span>
                            @endif
                        </p>
                        <legend class="sr-only">{{ __('Optional Permissions') }}</legend>
                        <div class="space-y-4 md:flex md:items-center space-x-0 md:space-x-10 md:space-y-0">
                            <x-form.v2.checkbox-group>
                                <x-form.v2.checkbox
                                        name="optionalRoles"
                                        :active="$optionalRoles['agent'] ?? false"
                                        :disabled="compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)"
                                        model="optionalRoles.agent"
                                        id="agent"
                                        functionName="updateOptionalRole"
                                        label="{{ __('agent') }}"
                                />
                                <x-form.v2.checkbox
                                        name="optionalRoles"
                                        :active="$optionalRoles['mobile'] ?? false"
                                        :disabled="compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)"
                                        model="optionalRoles.mobile"
                                        id="mobile"
                                        functionName="updateOptionalRole"
                                        label="{{ __('mobile') }}"
                                />
                                <x-form.v2.checkbox
                                        name="optionalRoles"
                                        :active="$optionalRoles['mandown'] ?? false"
                                        :disabled="compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)"
                                        model="optionalRoles.mandown"
                                        id="mandown"
                                        functionName="updateOptionalRole"
                                        label="{{ __('mandown') }}"
                                />
                            </x-form.v2.checkbox-group>
                        </div>
                        @php
                            $userHadMandown = false;
                            if(isset($editUser['roles'])) {
                                foreach($editUser['roles'] as $role) {
                                    if($role['role_type'] === 'mandown') {
                                        $userHadMandown = true;
                                        break;
                                    }
                                }
                            }
                            $mandownChecked = isset($optionalRoles['mandown']) && $optionalRoles['mandown'] == 1;
                            $isAddingMandown = $mandownChecked && !$userHadMandown;
                        @endphp
                        @if($isAddingMandown || ($mandownChecked && $errors->has('confirmPasswordForMandown')))
                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                                <x-input.group for="confirmPasswordForMandown" label="{{__('Confirm Password')}}" id="confirmPasswordForMandown" required="required" :error="$errors->first('confirmPasswordForMandown')">
                                    <x-input.text wire:model.defer="confirmPasswordForMandown" type="password" class="w-full" name="confirmPasswordForMandown" />
                                </x-input.group>
                                @if(!$errors->has('confirmPasswordForMandown'))
                                    @if($isAddingMandown)
                                        <p class="text-sm text-yellow-700 mt-1">{{__('Password required to create SIP endpoint for ManDown app.')}}</p>
                                    @else
                                        <p class="text-sm text-yellow-700 mt-1">{{__('Password required to update SIP endpoint (email changed or endpoint recreation needed).')}}</p>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </fieldset>
                </div>
                <div class="">
                    <label class="text-base font-bold">{{ __('Enable Login') }}</label>
                    <p class="text-sm text-gray-500 h-24">@lang('User allowed to login')</p>
                    <input wire:model.defer="editUser.hasLogin" id="hasLogin" name="hasLogin" type="checkbox" @if($editUser['hasLogin']) checked @endif @if(compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)) disabled="disabled" @endif class="uiswitch uiswitch-new" >
                </div>
            </div>

            <div class="mt-8 w-full pt-8 top-underline">
                <label class="text-base font-bold text-gray-900">{{__('Additional Email Addresses')}}</label>
                <p class="text-sm text-gray-500">{{__('You can add more email addresses for this user')}}</p>
            </div>

            @if(count($editUser['emails']) > 1)
                {{-- @for ($i = 1; $i < count($editUser['emails']); $i++) --}}
                @foreach($editUser['emails'] as $key => $email)
                    @if(!$loop->first)
                        <div class="mt-8 w-full grid grid-col-1 md:grid-cols-2 lg:grid-cols-4">
                            <div class="">
                                <fieldset>
                                    <legend class="sr-only">{{__('Additional Email Addresses')}}</legend>
                                    <div class="relative w-full px-1 mb-1 md:mb-0">
                                        <x-input.group  for="editUser.emails.{{$key}}.email_address" label="{{__('Email')}}" id="email" :error="$errors->first('editUser.emails.{{$key}}.email_address')">
                                            <x-input.text wire:model.defer="editUser.emails.{{$key}}.email_address" class="w-full" name="editUser.emails.{{$key}}.email_address" value="{{$editUser['emails'][$key]['email_address'] ?? ''}}" :disabled="(compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)) && !$sameUser" />
                                        </x-input.group>
                                    </div>
                                </fieldset>
                            </div>
                            @if((compare_binary_roles($editedBinaryRoles, $authBinaryRoles) <= 0 && !isNumberInBetween($authBinaryRoles,1,1)) || $sameUser)
                            <div class="px-0 md:px-4 py-4 lg:py-0 lg:col-span-3">
                                <fieldset>
                                    <div wire:click.prevent="removeExistingEmail({{$key}})">
                                        <x-form.button color="light" :size="'3xl'" :icon="'minus'"></x-form.button>
                                    </div>
                                </fieldset>
                            </div>
                            @endif
                        </div>
                    @endif
                {{-- @endfor --}}
                @endforeach
            @endif

            @foreach($emails as $key => $email)
                <div class="mt-8 w-full grid grid-col-1 md:grid-cols-2 lg:grid-cols-4">
                    <div class="">
                        <fieldset>
                            <legend class="sr-only">Additional Email Address</legend>
                            <div class="relative w-full px-1 mb-1 md:mb-0">
                                <x-input.group  for="emails.{{$key}}" label="{{__('Email')}}" id="email" :error="$errors->first('emails.{{$key}}')">
                                    <x-input.text type="email" wire:model.defer="emails.{{$key}}" class="w-full" name="emails.{{$key}}" value="{{$email ?? ''}}" :disabled="(compare_binary_roles($editedBinaryRoles, $authBinaryRoles) > 0 || isNumberInBetween($authBinaryRoles,1,1)) && !$sameUser" />
                                </x-input.group>
                            </div>
                        </fieldset>
                    </div>
                    @if((compare_binary_roles($editedBinaryRoles, $authBinaryRoles) <= 0 && !isNumberInBetween($authBinaryRoles,1,1)) || $sameUser)
                    <div class="px-0 md:px-4 py-4 lg:py-0 lg:col-span-3">
                        <fieldset>
                            @if(!$loop->last)
                                <div wire:click.prevent="removeEmail({{$key}})">
                                    <x-form.button class="mr-8" color="light" :size="'3xl'" :icon="'minus'"></x-form.button>
                                </div>
                            @endif
                            @if($loop->last)
                                <div wire:click.prevent="addFieldForEmail">
                                    <x-form.button color="light" :size="'3xl'" :icon="'plus'"></x-form.button>
                                </div>
                            @endif
                        </fieldset>
                    </div>
                    @endif
                </div>
            @endforeach

        </div>
        <div class=" mt-4 w-full flex justify-end">
            @if((compare_binary_roles($editedBinaryRoles, $authBinaryRoles) <= 0 && !isNumberInBetween($authBinaryRoles,1,1)) || $sameUser)<x-form.button color="primary" type="button" wire:click.prevent="updateUser" >{{__('Update')}}</x-form.button>@endif
            @if(compare_binary_roles($editedBinaryRoles, $authBinaryRoles) <= 0 && !isNumberInBetween($authBinaryRoles,1,1))<x-form.button color="danger" type="button" wire:click.prevent.self="$toggle('showDeleteModal')">{{__('Delete User')}} ...</x-form.button>@endif
            <x-form.button type="button" wire:click.prevent="cancelInvite">{{__('Cancel')}}</x-form.button>
        </div>
    </div>


    <!-- Delete Device Modal -->
    <x-modal.confirmation wire:model.defer="showDeleteModal">
        <x-slot name="title">{{__('Delete User')}}</x-slot>

        <x-slot name="content">
            <div class="py-8 text-cool-gray-700">@lang('Are you sure you, to delete user?', ['user' => $editUser['name']])</div>
        </x-slot>

        <x-slot name="footer">
            <x-button.secondary wire:click.prevent.self="$set('showDeleteModal', false)">{{__('Cancel')}}</x-button.secondary>

            <x-button.primary  wire:click.prevent.self="deleteSelectedUser">{{__('Delete')}}</x-button.primary>
        </x-slot>
    </x-modal.confirmation>

</div>
@endif