<x-layouts.guest>
    <div class="relative justify-center mt-24">
        <x-auth.card>
            <x-slot name="title">{{__('Reset Password')}}</x-slot>


            <div class="mb-4 opacity-70">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </div>

            <!-- Validation Errors -->
            <x-auth.validation-errors class="mb-4" :errors="$errors" />

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div class="form-group">
                    <x-label class="default" for="password" :value="__('Password')" />

                    <input class="form-control" id="password"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                </div>

                <div class="flex justify-end mt-4">
                    <x-button type="submit" class="primary">
                        {{ __('Confirm') }}
                    </x-button>
                </div>
            </form>
        </x-auth.card>
    </div>
</x-layouts.guest>
