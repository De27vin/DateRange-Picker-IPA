<div class="mx-auto pb-12 px-4 mt-8">

    <x-page.header class="h-24">
        <x-slot name="title">@lang('Choose Language')</x-slot>
        <x-slot name="description">@lang('Choose language that will be applied for your user')</x-slot>
    </x-page.header>

    <div class="w-full pt-2">
        <fieldset class="-mx-1 mb-1">

            <div class="flex w-full">
                @foreach($languagesFromDB as $languageFromDB)

                    @continue(empty($languageFromDB['language_enabled']))
                    @continue(empty($languagesFromJson[$languageFromDB['language_code']]))

                    <div class="flex justify-center">
                        <x-nav.button-horizontal-new
                            :active="$languageFromDB['language_code'] == App::getLocale()"
                            :href="route('lang.switch', $languageFromDB['language_code'])"
                        >
                            <span>@flag("{$languageFromDB['flag']}")</span>
                            {{ $languageNames[$languageFromDB['language_code']] }}
                        </x-nav.button-horizontal-new>
                    </div>
                @endforeach
            </div>

        </fieldset>

    </div>
</div>