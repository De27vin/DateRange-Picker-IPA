{{--input.text.blade.php--}}
@props([
    'leadingAddOn' => false,
    'rounded' => false,
    'canWriteSettings'=>true,
    'disabled' => false,
])

    <input {{ ($canWriteSettings ? '' : ' readonly=readonly ') }} {{ $attributes->merge(['class' => 'h-16 text-normal']) }} type="text" {{ ($disabled ? 'disabled="disabled"' : '') }}/>
