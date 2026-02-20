<div class="mx-auto pb-12 px-4 mt-8">

    <x-page.header class="h-24">
        <x-slot name="title">@lang('Change Password')</x-slot>
        <x-slot name="description">@lang('Change your access password to the service')</x-slot>
    </x-page.header>

    <div class="w-full pt-2">
        <fieldset class="-mx-1 mb-1">

            <div class="flex w-full">
                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                    <x-input.group for="oldPassword" label="{{__('Current Password')}}" required="required" :error="$errors->first('oldPassword')">
                        <x-input.password wire:model.defer="oldPassword" class="w-full" required="required" />
                    </x-input.group>
                </div>
                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                    <x-input.group for="newPassword" label="{{__('New Password')}}" required="required" :error="$errors->first('newPassword')">
                        <x-input.password wire:model.defer="newPassword" class="w-full" required="required" />
                    </x-input.group>
                </div>
                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                    <x-input.group for="newPasswordConfirm" label="{{__('Confirm New Password')}}" required="required" :error="$errors->first('newPasswordConfirm')">
                        <x-input.password wire:model.defer="newPasswordConfirm" class="w-full" required="required" />
                    </x-input.group>
                </div>

                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0 flex items-center justify-end">
                    <x-form.button :color="'primary'" type="button" wire:click="changePassword">{{__('Change Password')}}</x-form.button>
                </div>
            </div>

        </fieldset>

    </div>
</div>