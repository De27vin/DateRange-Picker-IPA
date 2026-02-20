<button
    {{ $attributes->merge([
        'type' => 'button',
//        'class' => ($attributes->get('disabled') ? ' opacity-75 cursor-not-allowed' : ''),
        'class' => '',
    ]) }}
>
    {{ $slot }}
</button>