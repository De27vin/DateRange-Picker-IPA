@props(['active', 'href' => '#', 'click' => '', 'border' => false, 'uppercase' => false])

@php
    $classes = ($active ?? false)
        ? 'rounded-full cursor-pointer px-1 flex items-center py-2 leading-5 text-white bg-color-new hover:text-white hover:bg-color-new'
        : 'rounded-full cursor-pointer px-1 flex items-center py-2 leading-5 text-secondary-600 hover:text-gray-200 hover:bg-color-new focus:text-gray-200 focus:bg-color-new' . ($border ? ' border border-slate-300' : '');
@endphp

<div class="px-0 flex flex-row space-x-4">
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }} role="menuitem">
        @if($click)
            <span class="rounded-none justify-start text-left px-4 py-1 mx-0 u @if($uppercase) uppercase @else normal-case @endif" wire:click="{{ $click }}" style="display: flex; align-items: center; column-gap: 0.3rem;">
                {{ $slot }}
            </span>
        @else
            <a class="rounded-none justify-start text-left px-4 py-1 mx-0 @if($uppercase) uppercase @else normal-case @endif" href="{{ $href }}" style="display: flex; align-items: center; column-gap: 0.3rem;">
                {{ $slot }}
            </a>
        @endif
    </button>
</div>
