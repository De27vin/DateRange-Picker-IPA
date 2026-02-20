<div>
    @if(!empty($readonly))
        <div class="switch-toggler flex items-center text-white font-medium text-sm opacity-50">
            <div class="relative flex items-center">
                <input id="three-{{$key}}-radio-off" class="absolute left-0 opacity-0" type="radio" value="0" @if($value === '0') checked @else disabled @endif />
                <label class=" bg-gray-400 py-2 px-4 rounded-l-full" for="three-{{$key}}-radio-off">{{ __('OFF') }}</label>
                @if(empty($fallback))<span class="fallback absolute top-0 right-0 rounded-full bg-white opacity-40 w-2 h-2 mt-1 mr-1"></span>@endif
            </div>
            <div class="relative flex items-center">
                <input id="three-{{$key}}-radio-unset" class="absolute left-0 opacity-0" type="radio" value="" @if($value === '') checked @else disabled @endif />
                <label class=" bg-gray-400 py-2 px-4 border-l border-r border-gray-300" for="three-{{$key}}-radio-unset">{{ __('N/A') }}</label>
            </div>
            <div class="relative flex items-center">
                <input id="three-{{$key}}-radio-on" class="absolute left-0 opacity-0" type="radio" value="1" @if($value === '1') checked @else disabled @endif />
                <label class=" bg-gray-400 py-2 px-4 rounded-r-full" for="three-{{$key}}-radio-on">{{ __('ON') }}</label>
                @if(!empty($fallback))<span class="fallback absolute top-0 left-0 rounded-full bg-white opacity-40 w-2 h-2 mt-1 ml-1"></span>@endif
            </div>
        </div>
    @else
        <div class="switch-toggler flex items-center text-white font-medium text-sm">
            <div class="relative flex items-center">
                <input wire:model="{{$model}}" id="three-{{$key}}-radio-off" class="absolute left-0 opacity-0" type="radio" value="0" />
                <label class=" bg-gray-400 py-2 px-4 rounded-l-full" for="three-{{$key}}-radio-off">{{ __('OFF') }}</label>
                @if(empty($fallback))<span class="fallback absolute top-0 right-0 rounded-full bg-white opacity-40 w-2 h-2 mt-1 mr-1"></span>@endif
            </div>
            <div class="relative flex items-center">
                <input wire:model="{{$model}}" id="three-{{$key}}-radio-unset" class="absolute left-0 opacity-0" type="radio" value="" />
                <label class=" bg-gray-400 py-2 px-4 border-l border-r border-gray-300" for="three-{{$key}}-radio-unset">{{ __('N/A') }}</label>
            </div>
            <div class="relative flex items-center">
                <input wire:model="{{$model}}" id="three-{{$key}}-radio-on" class="absolute left-0 opacity-0" type="radio" value="1" />
                <label class=" bg-gray-400 py-2 px-4 rounded-r-full" for="three-{{$key}}-radio-on">{{ __('ON') }}</label>
                @if(!empty($fallback))<span class="fallback absolute top-0 left-0 rounded-full bg-white opacity-40 w-2 h-2 mt-1 ml-1"></span>@endif
            </div>
        </div>
    @endif

</div>

<style>
    .switch-toggler div input[type=radio]:checked + label {
        background-color: #3b82f6;
        color: #fff;
    }

</style>
