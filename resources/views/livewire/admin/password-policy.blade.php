<div class="mx-auto pb-12 px-4 mt-8">

    <x-page.header class="mt-8 mb-2 h-20">
        <x-slot name="title">@lang('Password Rules')</x-slot>
        <x-slot name="description">@lang('password_policy_description')</x-slot>
    </x-page.header>

    <ul class="w-full pt-6">
        @if($passwordPolicies['on'])
            @foreach($passwordPolicies as $key => $item)
                <li class="text-normal w-full py-2 flex flex-col bottom-underline-light" id="{{$key}}" wire:key="{{$key}}">
                    <div class="flex flex-row justify-between items-center">
                        <p>
                            @lang($comments[$key])
                        </p>
                        @if($key == 'length')
                            <x-forms.group class="lg:w-1/2">
                                <x-forms.label :fallback="''" for="{{$item}}">
                                    @lang($key)
                                </x-forms.label>
                                <x-input.text class="w-full" name="{{$key}}" wire:model.defer="passwordPolicies.{{$key}}">
                                </x-input.text>
                            </x-forms.group>
                        @else
                            <div aria-checked="true" aria-describedby="privacy-option-1-description" aria-labelledby="privacy-option-1-label" class="btn switch @if($item) active bg-color-new @else bg-gray-400 @endif " role="switch" wire:click="togglePasswordPolicy('{{$key}}')">
                                <span class="sr-only">
                                    @lang('Use setting')
                                </span>
                                <span aria-hidden="true" class=" @if($item) translate-x-5 @else translate-x-0 @endif inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach

        @else
            @foreach($settingsDefault as $key => $item)
                <li class="w-full py-2 flex flex-col" id="{{$key}}" wire:key="{{$key}}">
                    <div class="flex flex-row justify-between items-center">
                        <p>
                            @lang($comments[$key])
                        </p>
                        @if($key == 'length')
                            <x-forms.group class="lg:w-1/2">
                                <x-forms.label :fallback="''" for="{{$item}}">
                                    @lang($key)
                                </x-forms.label>
                                <x-input.text class="w-full" name="{{$key}}" readonly="readonly" wire:model.defer="settingsDefault.{{$key}}">
                                </x-input.text>
                            </x-forms.group>
                        @else
                            <div aria-checked="true" aria-describedby="privacy-option-1-description" aria-labelledby="privacy-option-1-label" class="btn switch @if($item) active bg-color-new @else bg-gray-400 @endif " role="switch" wire:click="togglePasswordPolicy('{{$key}}')">
                                <span class="sr-only">
                                    @lang('Use setting')
                                </span>
                                <span aria-hidden="true" class=" @if($item) translate-x-5 @else translate-x-0 @endif inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach

        @endif
    </ul>

    <div class="flex justify-end my-4">
        <x-button.primary type="submit" wire:click.prevent="updatePasswordPolicy">
            @lang('Update')
        </x-button.primary>
        <x-button.secondary wire:click="cancelPasswordPolicy">
            @lang('Cancel')
        </x-button.secondary>
    </div>

</div>