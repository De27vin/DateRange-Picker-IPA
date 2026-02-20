<div class="mx-auto pb-12 px-4 mt-8">

    <x-page.header class="mt-8 mb-2 h-20">
        <x-slot name="title">@lang('Languages')</x-slot>
        <x-slot name="description">@lang('Specify which languages are available for selection via the navigation and which language is the default for your account if the user has not yet defined their own language.')</x-slot>
    </x-page.header>

    <div class="w-full pt-8">
        <ul wire:model="languages" class="w-full">
            <li class="flex w-full justify-end">
                <div class="w-full"></div>
                <div class="flex w-56 justify-between">
                    <span class="flex justify-start text-sm uppercase font-medium">{{__('Available')}}</span>
                    <span class="flex justify-end text-sm uppercase font-medium">{{__('Default')}}</span>
                </div>
            </li>
            @foreach($languagesfromDB as $key => $language)
                @continue(empty($language['language_enabled']))
                @php $state = $profile['languages'][$language['language_code'] ?? null] ?? false; @endphp
                <li :key="{{$key}}" x-data="{open: false}" class="group flex w-full py-2 justify-between items-center top-underline-light">

                    <div class=" @if(!$state) opacity-20 @endif w-full flex items-center text-gray-500">
                        <span>@flag("{$language['flag']}")</span> <span class="px-4">{{ __($languageComments[$language['language_code']] ?? '') }}</span>
                    </div>

                    <div class="flex items-center space-x-8 cursor-pointer w-56 justify-between">
                        <input type="checkbox" wire:click="toggleLanguageState('{{$language['language_code']}}')" class="uiswitch uiswitch-new" @if($profile['languages'][$language['language_code']]) checked @endif />
                        <input type="radio" name="currentDefaultLocale" wire:click="toggleLanguageDefaultState('{{$language['language_code']}}')" class="uiswitch uiswitch-new" @if($currentDefaultLocale == $availableLocales[$language['language_code']]['id'] ) checked @endif value="{{$profile['languages'][$language['language_code']]}}" />
                    </div>

                </li>
            @endforeach
        </ul>
    </div>
</div>