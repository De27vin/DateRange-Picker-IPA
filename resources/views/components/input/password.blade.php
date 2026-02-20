@props([
    'leadingAddOn' => false,
    'rounded' => false,
    'canWriteSettings'=>true
])

<input {{ ($canWriteSettings ? '' : ' readonly=readonly ') }} {{ $attributes->merge(['class' => 'h-16']) }} type="password" autocomplete="new-password" />
