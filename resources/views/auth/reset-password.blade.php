<x-layouts.guest>
    <div class="relative justify-center" style="margin-top: 16rem;">
        <x-auth.card class="max-w-4xl mx-auto">
            <x-slot name="title">{{ __('Reset Password') }}</x-slot>

            <div class="mb-4 opacity-70">
                {{ __('Enter your new password below to reset your account.') }}
            </div>

            <!-- Validation Errors -->
            <x-auth.validation-errors class="mb-4" :errors="$errors" />

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Password -->
                <div class="form-group mt-4">
                    <x-label class="default" for="password" :value="__('Password')" />

                    <input class="form-control" id="password" type="password" name="password" required />
                </div>

                <!-- Confirm Password -->
                <div class="form-group mt-4">
                    <x-label class="default" for="password_confirmation" :value="__('Confirm Password')" />

                    <input class="form-control" id="password_confirmation"
                                        type="password"
                                        name="password_confirmation" required />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-button type="submit" class="bg-color-new-400 text-white hover:bg-color-new-600 text-sm">
                        {{ __('Reset Password') }}
                    </x-button>
                </div>
            </form>
        </x-auth.card>
    </div>
</x-layouts.guest>
