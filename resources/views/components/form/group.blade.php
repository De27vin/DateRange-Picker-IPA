@props([
    'error' => false,
    'helpText' => false,
    'class' => '',
    'tile' => 3,
    'alignRight' => 0,
    'alignCenter' => 0
])
<div {{ $attributes->class([
    "relative mb-4 flex flex-col",
    " w-full" => ($tile == 'full'),
    " w-full md:w-1/3 lg:w-1/6" => ($tile == 6),
    " w-full md:w-1/2 lg:w-1/4" => ($tile == 4),
    " w-full md:w-1/2 lg:w-1/3" => ($tile == 3),
    " w-full md:w-1/2 lg:w-1/2" => ($tile == 2),
    " items-end " => ($alignRight == 1),
    " items-center " => ($alignCenter == 1),
    " items-start " => ($alignCenter == 0)
    ])}}>
   {{$slot}}
    @if ($error)
        <div class="mt-1 text-red-500 text-sm">{{ $error }}</div>
    @endif

    @if ($helpText)
        <p class="mt-2 text-sm text-gray-500">{{ $helpText }}</p>
    @endif

</div>
