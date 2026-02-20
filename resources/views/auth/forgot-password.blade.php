<x-layouts.guest>
    <div class="relative justify-center" style="margin-top: 16rem;">
        <x-auth.card class="max-w-4xl mx-auto">
            <x-slot name="title">{{__('Forgot Password')}}</x-slot>
            <div class="mb-4  opacity-70">
                {{ __('Let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </div>

            <!-- Session Status -->
            <x-auth.session-status class="mb-4" :status="session('status')" />

            <!-- Validation Errors -->
            <x-auth.validation-errors class="mb-4" :errors="$errors" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-group">
                    <x-label class="default" for="email" :value="__('Email')" />

                    <input class="form-control" id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-button type="submit" class="bg-color-new-400 text-white hover:bg-color-new-600 text-sm">
                        {{ __('Send Reset Link') }}
                    </x-button>
                </div>
            </form>
        </x-auth.card>
    </div>
</x-layouts.guest>
