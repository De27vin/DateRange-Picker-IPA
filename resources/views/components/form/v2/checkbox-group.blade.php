<div {!! $attributes->merge([
    'class' =>
        'block md:flex md:flex-wrap w-full space-y-4 md:space-y-0 space-x-0 md:space-x-4'
]) !!}>
    {{ $slot }}
</div>