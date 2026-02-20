@props(['icon' => ''])
@php $idPostfix = \Str::random('10'); @endphp
<span x-cloak
    x-on:keydown.window.escape="open = false" 
    x-on:click.away="open = false"
    class="h-6" 
    x-data="{ open: false }">
    <button 
        x-on:click="open = !open" 
        x-on:keydown.arrow-down.prevent="onArrowDown()" 
        x-on:keydown.arrow-up.prevent="onArrowUp()" 
        x-on:keydown.enter.prevent="onButtonEnter()" 
        @keyup.space.prevent="onButtonEnter()" 
        aria-expanded="true" 
        aria-haspopup="true" 
        class="text-color-new-100 hover:text-white border-r border-solid border-gray-200"
            id="option-menu-button-{{$idPostfix}}" 
            type="button" 
            x-bind:aria-expanded="open.toString()" 
            x-ref="button">
        <span class="sr-only">
            Open options
        </span>
        @if($icon === 'sort')
        <span class="block h-6 w-6">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.293 4.293a1 1 0 0 1 1.414 0l4 4a1 1 0 0 1-1.414 1.414L8 7.414V19a1 1 0 1 1-2 0V7.414L3.707 9.707a1 1 0 0 1-1.414-1.414l4-4zM16 16.586V5a1 1 0 1 1 2 0v11.586l2.293-2.293a1 1 0 0 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L16 16.586z" fill="currentColor"/></svg>
        </span>
        @else
            <span class="block w-6 h-6 ml-4">
                <svg class="block h-full h-full" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0zm0-6a2 2 0 1 0 4 0 2 2 0 0 0-4 0zm0 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0z" fill="currentColor"/></svg>
            </span>

        {{-- <span class="block h-6 w-6"> --}}
            {{-- <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm-6 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm12 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor"/></svg> --}}
        {{-- </span> --}}
        @endif
    </button>
    <div 
        x-on:keydown.arrow-down.prevent="onArrowDown()" 
        x-on:keydown.arrow-up.prevent="onArrowUp()" 
        x-on:keydown.enter.prevent="open = false; 
        x-on:keydown.tab="open = false" 
        @keyup.space.prevent="open = false; 
        aria-labelledby="option-menu-button-{{$idPostfix}}" 
        aria-orientation="vertical" 
        class="z-50 origin-top-right absolute right-0 h-full-mr-1 w-56 shadow-lg focus:outline-none" 
        role="menu" 
        tabindex="-1" 
        x-description="Dropdown menu, show/hide based on menu state." 
        x-ref="menu-items" 
        x-show="open" 
        x-transition:enter="transition ease-out duration-100" 
        x-transition:enter-end="transform opacity-100 scale-100" 
        x-transition:enter-start="transform opacity-0 scale-95" 
        x-transition:leave="transition ease-in duration-75" 
        x-transition:leave-end="transform opacity-0 scale-95" 
        x-transition:leave-start="transform opacity-100 scale-100">
        <div class="py-1 bg-secondary-600 text-secondary-400 overflow-hidden dropdown" role="none">
            {{ $slot }}
        </div>
    </div>
</span>
