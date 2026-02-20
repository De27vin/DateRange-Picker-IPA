<x-layouts.guest>
    <div class="relative justify-center mt-24">
        <x-auth.card>
            <x-slot name="title">{{__('E-Mail Verification')}}</x-slot>

            <div class="mb-4 text-sm text-gray-600">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="mt-4 flex items-center justify-between">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <div>
                        <x-button type="submit" class="primary">
                            {{ __('Resend Verification Email') }}
                        </x-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <div>
                        <x-button type="submit" class="primary">
                            {{ __('Log Out') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </x-auth.card>
    </div>
</x-layouts.guest>
