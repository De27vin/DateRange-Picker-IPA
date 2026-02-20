@props([
    'for',
    'fallback' => null,
    'settingId' => null,

    'leadingAddOn' => false,
    'rounded' => false,
    'readonly' => true,
    'identifier' => null,
    'valueModel' => null,

    'shrink' => true,
])

<x-forms.group class="mb-4" :shrink="$shrink">
    <div x-data="{ value: @this.entangle('{{ $valueModel.'.'.$settingId.'.value' }}').defer, na: @this.entangle('{{ $valueModel.'.'.$settingId.'.not_applicable' }}') }" >
        <label class="default"  for="{{ $for }}">
            {{$slot}}
        </label>
        @if($fallback != null && !isset($fallback['value']))
            <span class="ml-4 inline-flex uppercase rounded-none tracking-normal px-4 py-1 bg-opacity-100 whitespace-nowrap text-xs justify-between bg-gray-300 mr-2 mt-1 text-gray-600">
            {{$fallback}}
        </span>
        @endif
        @if(isset($fallback['value']) && $fallback['value'] !== '' && isset($fallback['label']))
            @if(strlen($slot->__toString()) + strlen($fallback['value']) > 40)
                {{--  LONG LABEL TEXT --}}
                <div class="absolute right-0 @if($readonly) opacity-70 @endif" style="margin-top: 0.08rem">
                    <div class="ml-4 inline-flex uppercase rounded-none tracking-normal bg-opacity-100 whitespace-nowrap text-xs justify-between mr-2 mt-1">
                        <div x-on:click="'{{empty($readonly)}}' && !value && $wire.changeSettingNa({{ $settingId }}, false)"
                             :class="(!value && !na) ? 'bg-color-new text-white' : 'bg-gray-300 text-gray-600'"
                             class="fallback right-0 px-4 py-1 cursor-pointer">
                            <span>{{ $fallback['value'] }}</span>
                            <span class="fallback-tt">{{ $fallback['label'] }}</span>
                        </div>
                        <span x-on:click="'{{empty($readonly)}}' && !value && $wire.changeSettingNa({{ $settingId }}, true)"
                              :class="(!value && na) ? 'bg-color-new text-white' : 'bg-gray-300 text-gray-600'"
                              class="ml-1 px-4 py-1 cursor-pointer"
                              style="position: absolute; top: 2.05rem; right: 0.6rem;">@lang('N/A')</span>
                    </div>
                </div>
            @else
                {{--  SHORT LABEL TEXT --}}
                <div class="absolute right-0 @if($readonly) opacity-70 @endif" style="margin-top: 0.08rem">
                    <div class="ml-4 inline-flex uppercase rounded-none tracking-normal bg-opacity-100 whitespace-nowrap text-xs justify-between mr-2 mt-1">
                        <div x-on:click="'{{empty($readonly)}}' && !value && $wire.changeSettingNa({{ $settingId }}, false)"
                             :class="(!value && !na) ? 'bg-color-new text-white' : 'bg-gray-300 text-gray-600'"
                             class="fallback px-4 py-1 cursor-pointer">
                            <span>{{ $fallback['value'] }}</span>
                            <span class="fallback-tt">{{ $fallback['label'] }}</span>
                        </div>
                        <span x-on:click="'{{empty($readonly)}}' && !value && $wire.changeSettingNa({{ $settingId }}, true)"
                              :class="(!value && na) ? 'bg-color-new text-white' : 'bg-gray-300 text-gray-600'"
                              class="ml-1 px-4 py-1 cursor-pointer">@lang('N/A')</span>
                    </div>
                </div>
            @endif
        @endif

        <input {{ ($readonly ? ' readonly=readonly ' : '') }}
               class="text-normal h-16 @if($readonly) opacity-70 @endif"
               x-model="value"
               type="text" />
    </div>

</x-forms.group>

<style>
    .fallback-tt {
        position: absolute;
        display: none;
        bottom : 110%;
        right: 50%;
        padding: 10px;
        background-color: rgb(252 211 77);
        border-radius: 3px;
        font-size: 12px;
        color: #222;
        animation: moveup 0.1s linear;
    }
    .fallback:hover > .fallback-tt {
        display: block;
    }
</style>

