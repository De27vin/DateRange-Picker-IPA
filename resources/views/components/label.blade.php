@props(['value', 'required' => "false"])

<label {{ $attributes->merge(['class' => '']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <i class="absolute right-0 px-4 text-xs fa fa-star text-red-600"></i>
    @endif
</label>
