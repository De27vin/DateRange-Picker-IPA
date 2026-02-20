@props([
    'error' => false,
    'helpText' => false,
    'class' => '',
])

<div {{ $attributes->merge(['class' => " relative w-full px-1 mb-1  {$class}"]) }}>
	{{$slot}}

    @if ($error)
        <div class="mt-1 text-red-500 text-sm">{{ $error }}</div>
    @endif

    @if ($helpText)
        <p class="mt-2 text-sm text-gray-500">{{ $helpText }}</p>
    @endif

</div>
