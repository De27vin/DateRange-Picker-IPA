@props([
    'model' => null,
    'key' => null,
    'readonly' => true,
    'fallback' => null,
])

<div class="switch-toggler flex items-center text-white font-medium text-sm @if($readonly) opacity-50 @endif">
    <div class="relative flex items-center">
        <label @if(!$readonly) wire:click="changeBoolSetting({{$key}}, 'off')" @endif class="py-2 px-4 rounded-l-full cursor-pointer @if($model[$key]['bool']['off']) bg-color-new @else bg-gray-400 opacity-80 @endif ">@lang('OFF')</label>
        @if(!empty($fallback) && $fallback['value'] === '0')
            <span class="fallback absolute top-0 right-0 rounded-full w-2 h-2 mt-1 mr-1 @if($model[$key]['bool']['off'] || $model[$key]['bool']['on'] || $model[$key]['bool']['na']) bg-gray-200 @else bg-color-new-600 border-1 @endif ">
                @if(!$readonly)<span class="fallback-tt text-nowrap w-56 text-center uppercase">{{ $fallback['label'] }}</span>@endif
            </span>
        @endif
    </div>
    <div class="relative flex items-center">
        <label @if(!$readonly) wire:click="changeBoolSetting({{$key}}, 'na')" @endif class="py-2 px-4 border-l border-r border-slate-300 cursor-pointer @if($model[$key]['bool']['na']) bg-color-new @else bg-gray-400 opacity-80 @endif ">@lang('N/A')</label>
        @if(!empty($fallback) && $fallback['value'] === '')
            <span class="fallback absolute top-0 right-0 rounded-full w-2 h-2 mt-1 mr-1 @if($model[$key]['bool']['off'] || $model[$key]['bool']['on'] || $model[$key]['bool']['na']) bg-gray-200 @else bg-color-new-600 border-1 @endif ">
                @if(!$readonly)<span class="fallback-tt text-nowrap w-56 text-center uppercase">{{ $fallback['label'] }}</span>@endif
            </span>
        @endif
    </div>
    <div class="relative flex items-center">
        <label @if(!$readonly) wire:click="changeBoolSetting({{$key}}, 'on')" @endif class="py-2 px-4 rounded-r-full cursor-pointer @if($model[$key]['bool']['on']) bg-color-new @else bg-gray-400 opacity-80 @endif ">@lang('ON')</label>
        @if(!empty($fallback) && $fallback['value'] === '1')
            <span class="fallback absolute top-0 left-0 rounded-full w-2 h-2 mt-1 ml-1 @if($model[$key]['bool']['off'] || $model[$key]['bool']['on'] || $model[$key]['bool']['na']) bg-gray-200 @else bg-color-new-600 border-1 @endif ">
                @if(!$readonly)<span class="fallback-tt text-nowrap w-56 text-center uppercase">{{ $fallback['label'] }}</span>@endif
            </span>
        @endif
    </div>
</div>