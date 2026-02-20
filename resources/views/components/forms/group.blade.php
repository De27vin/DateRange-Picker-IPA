@props([
    'error' => false,
    'helpText' => false,
    'class' => '',
    'shrink' => true
])

@php
    $merge = 'relative w-full px-1 mb-1';
    if ($shrink) {
        $merge = $merge . ' md:w-1/2 lg:w-1/3';
    }
@endphp

<div {{ $attributes->merge(['class' => " $merge {$class}"]) }}>
	{{$slot}}

    @if ($error)
        <div class="mt-1 text-red-500 text-sm">{{ $error }}</div>
    @endif

    @if ($helpText)
        <p class="mt-2 text-sm text-gray-500">{{ $helpText }}</p>
    @endif

</div>
