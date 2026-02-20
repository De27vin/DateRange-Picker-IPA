<div>
    @foreach($languages as $lang => $isActive)
        @if($isActive)
            <x-nav.item 
                class="cursor-pointer block" 
                :active="$lang == App::getLocale()" 
                :href="route('lang.switch', $lang)" 
                type="languages">
                @lang($lang)
            </x-nav.item>
        @endif
    @endforeach
</div>