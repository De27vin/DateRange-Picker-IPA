    <div class="relative justify-center" style="margin-top: 16rem;">
        @if($hasUpdate)
            <div class="absolute shadow-lg inset-x-0 p-16" style="background-color: #a05050; z-index:1000;">
                <div class="w-full h-20 pt-6 bg-transparent flex justify-center">
                    <div wire:loading class="ml-10"><x-monoicon.loading-indicator></x-monoicon.loading-indicator></div>
                </div>
                <div class="w-full my-8 text-center text-2xl text-white">
                    {{ __('There are updates pending for ucp.') }}
                </div>
                <div class="w-full my-8 text-center">
                    <x-form.button color="danger" class="ml-4" wire:click.prevent="startUpdate">
                        @lang('Start Update')
                    </x-form.button>
                </div>
            </div>
        @endif
        <div class=" @if($hasUpdate)relative blur-lg cursor-not-allowed pointer-events-none opacity-20  @endif">
            <x-auth.card :hasUpdate="$hasUpdate" class="max-w-4xl mx-auto">
                <x-slot name="title">@lang('Account Login')</x-slot>
                <!-- Session Status -->
                <x-auth.session-status class="mb-4 text-color-new-600" :status="session('status')" />
                    <!-- Validation Errors -->
                    <x-auth.validation-errors class="mb-4 text-danger-600" :errors="$errors" />
                    <div>
                        <div class="row">
                            <div class="col-md-12">
                                @if (session()->has('message'))
                                    <div class="alert alert-success">
                                        {{ session('message') }}
                                    </div>
                                @endif
                                @if (session()->has('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <form wire:submit.prevent="login">
                            <div class="row">
                                <div class="col-md-12 my-4">
                                    <div class="relative w-full mx-auto mt-4">
{{--                                        <x-label class="default" for="email" :required="true" :value="__('Email address')" />--}}
{{--                                        <x-input wire:model.defer="email" id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required></x-input>--}}
                                        <x-label class="default" for="email" :required="true" :value="__('Email address')" />
                                        <input id="email" class="block mt-1 w-full" type="email" wire:model.defer="email"></input>
                                    </div>
                                </div>
                                <div class="col-md-12 my-4">
                                    <div class="relative w-full mx-auto mt-4">
                                        <x-label class="default" for="password" :required="true" :value="__('Password')" />
                                        <input class="block mt-1 w-full" type="password" wire:model.defer="password"></input>
                                    </div>
                                </div>

                                <div class="flex w-full mt-4 items-center justify-end">
                                    <input wire:model.defer="remember_me" class="uiswitch uiswitch-new" name="remember_me" id="remember_me" type="checkbox" checked="checked"/>
                                    <label class="text-sm ml-2" for="remember_me">
                                        {{ __('Remember me') }}
                                    </label>
                                </div>
                                <div class="flex w-full mt-4">
                                    <div class="flex w-1/2  items-center justify-start">
                                        <x-button type="submit" class="bg-color-new-400 text-white hover:bg-color-new-600 text-sm">
                                            {{ __('Log in') }}
                                        </x-button>
                                    </div>
                                    <div class="flex w-1/2 items-center justify-end">
                                        @if (Route::has('password.request'))
                                            <a type="button" class="text-sm bg-gray-400 text-white hover:bg-gray-600" href="{{ route('password.request') }}">
                                                {{ __('Forgot password') }} ...
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
            </x-auth.card>
        </div>
    </div>
