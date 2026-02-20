<x-layouts.guest>
    <x-auth.card-join>
        <form action="/accept" method="POST">
            @csrf
            <!-- Password -->
            <div class="relative w-full mx-auto mt-4">
                <x-label :required="true" :value="__('Password')" class="default" for="password"></x-label>
                <x-input autocomplete="new-password" class="" id="password" name="password" required="" type="password"></x-input>
                @error('password')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="relative w-full mx-auto mt-4">
                <x-label :required="false" :value="__('Confirm Password')" class="default" for="password_confirmation"></x-label>
                <x-input class="" id="password_confirmation" name="password_confirmation" required="" type="password"></x-input>
            </div>

            <div class="flex w-full mt-4 justify-end">
                <div class="flex w-1/2 items-center mr-4">
                    <x-button class="bg-color-new-400 text-white hover:bg-color-new-600 text-sm">
                        {{ __('Set Password') }}
                    </x-button>
                    <input type="hidden" name="token" id="token" value="{{$token}}"/>
                </div>
            </div>
        </form>
    </x-auth.card-join>
</x-layouts.guest>