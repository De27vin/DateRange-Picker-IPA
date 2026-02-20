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
        {{ $attributes->merge(['href' => '#', 'class' => "whitespace-nowrap block w-full bg-white hover:bg-color-new-600 hover:text-white block px-4 py-2" ]) }}
        role="menuitem" 
        tabindex="-1" 
        x-state:off="Not Active" 
        x-state:on="Active">
        {{ $slot }}
    </a>
@endif
