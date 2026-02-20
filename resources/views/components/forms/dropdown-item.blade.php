@props(['type' => 'link'])
@if($type === 'button')
    <button
        {{ $attributes->merge(['type' => 'button', 'class' => "dropdown-item" ]) }}
        x-on:click="open = false"
        role="menuitem" 
        tabindex="-1" 
        x-state:off="Not Active" 
        x-state:on="Active">
        {{ $slot }}
    </button>
@elseif($type === 'link')
    <a 
        {{ $attributes->merge(['href' => '#', 'class' => "block w-full bg-transparent text-secondary-200 hover:bg-color-new-600 hover:text-white block px-4 py-2 text-sm" ]) }}
        role="menuitem" 
        tabindex="-1" 
        x-state:off="Not Active" 
        x-state:on="Active">
        {{ $slot }}
    </a>
@endif
