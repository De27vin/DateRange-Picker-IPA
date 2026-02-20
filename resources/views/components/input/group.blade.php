@props([
    'label',
    'for',
    'error'       => false,
    'helpText'    => false,
    'inline'      => false,
    'paddingless' => false,
    'borderless'  => true,
    'rounded'     => false,
    'required'    => false,
    'readonly'    => false,
    'errNegMar'    => false,
])

    <div class="relative w-full">
        <label for="{{ $for }}" class="default">
            {{__($label)}}
            @if($readonly)
                <div><x-monoicon.locked class="absolute top-0 right-0 pr-2 pt-2 text-red-600" /></div>
            @else
                @if($required)<x-monoicon.required />@endif
            @endif    

        </label>

            {{ $slot }}
            @if ($error)
                <div class="@if($errNegMar) -mt-1 mb-1 @else mt-1 @endif py-1 px-2 text-white text-sm" style="background-color: #e297ac;">@lang($error)</div>
            @endif

            @if ($helpText)
                <p class="mt-2 text-sm text-gray-500">{{ $helpText }}</p>
            @endif
    </div>

