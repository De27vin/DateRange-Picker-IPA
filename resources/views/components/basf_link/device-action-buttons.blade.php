<div class="w-full">
    <div class="relative block lg:flex items-center justify-end h-12">
        <div class="relative z-10 w-full h-full  block lg:flex items-center justify-end">
            <div wire:loading wire:target="makeFSCall" class="absolute z-50 inset-0 bg-white shadow-lg mx-auto "><div class="flex w-full h-full items-center justify-center">{{ __('proceeding') }}...</div></div>
            <span class="topActionBtn block lg:flex flex-nowrap transition ease-in duration-100 opacity-100" >

                @if(!empty($actionButtons['_trigger']))
                    <button  wire:loading.class="opacity-40 blur-lg disabled" wire:target="makeFSCall" class="relative" type="button" wire:click="makeFSCall('trigger')">
                            <span class="px-2">_{{__('trigger')}}</span>
                        </button>
                @endif

{{--                @if(!empty($actionButtons['_carcall']))--}}
{{--                    <button  wire:loading.class="opacity-40 blur-lg disabled" wire:target="makeFSCall" class="relative" type="button"  wire:click="makeFSCall('carcall')">--}}
{{--                            <span class="px-2">{{__('_carcall')}}</span>--}}
{{--                            --}}{{-- <x-monoicon.state-phone></x-monoicon.state-phone> --}}
{{--                        </button>--}}
{{--                @endif--}}
        </div>
    </div>
</div>