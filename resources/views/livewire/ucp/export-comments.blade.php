<div>
    <div class="flex flex-col items-top justify-between">
        <div class="block_header w-full pb-4">
            <h3 class="title" id="message-heading">
                @lang('Export Comments')
            </h3>
            <p class="description pb-8 lg:pb-0">
                @lang('If desired, select additional fields for identification in addition to the ID.' )
            </p>
        </div>

         <div class="block md:flex md:flex-wrap w-full space-x-4">
            <div class="opacity-40">
                <input type="checkbox" checked name="identifiers.device_id" class="appearance-none hidden h-0 w-0">
                <label for="identifiers.equipment" class="cursor-not-allowed group w-full flex items-center justify-between h-8 px-0 py-0 text-sm">
{{--                    <div class="py-1 pl-4 pr-2 w-full h-full flex items-center border-none rounded-none rounded-l-full bg-color-new text-white truncate uppercase select-none">{{__('Device ID')}}</div>--}}
                    <div class="py-1 pl-4 pr-2 w-full h-full flex items-center border-none rounded-none rounded-l-full bg-color-new text-white truncate uppercase select-none">{{ 'Device ID' }}</div>
                    <div class="flex justify-center bg-color-new h-full w-12 items-center  px-2 border-none rounded-none rounded-r-full"><i class="f7-icons text-white text-sm">checkmark_alt</i></div>
                </label>
            </div>

             @foreach($identifiers as $identifier => $selected)
                 <div wire:model="identifiers.{{$identifier}}">
                     <input type="checkbox" @if($selected) checked @endif name="identifiers.{{$identifier}}" class="appearance-none hidden h-0 w-0">
                     <label wire:click="toggleIdentifier('{{$identifier}}')" for="identifiers.{{$identifier}}" class="group cursor-pointer w-full flex items-center justify-between h-8 px-0 py-0 text-sm">
{{--                         <div class="py-1 pl-4 pr-2 w-full h-full flex items-center border-none rounded-none rounded-l-full @if($selected) bg-color-new text-white @else bg-white @endif truncate uppercase select-none">{{$fieldTranslations[$identifier] ?? __($identifier)}}</div>--}}
                         <div class="py-1 pl-4 pr-2 w-full h-full flex items-center border-none rounded-none rounded-l-full @if($selected) bg-color-new text-white @else bg-white @endif truncate uppercase select-none">{{ $identifier }}</div>
                         @if($selected)
                             <div class="flex justify-center bg-color-new h-full w-12 items-center  px-2 border-none rounded-none rounded-r-full"><i class="f7-icons text-white text-sm">checkmark_alt</i></div>
                         @else
                             <div class="flex justify-center bg-color-new h-full w-12 items-center px-2 border-none rounded-none rounded-r-full text-white">&nbsp;</div>
                         @endif
                     </label>
                 </div>
             @endforeach


{{--            <div wire:model="identifiers.identity">--}}
{{--                <input type="checkbox" @if($identifiers['identity'] == true) checked @endif name="identifiers.identity" class="appearance-none hidden h-0 w-0">--}}
{{--                <label wire:click="toggleIdentifier('identity')" for="identifiers.identity" class="group cursor-pointer w-full flex items-center justify-between h-8 px-0 py-0 text-sm">--}}
{{--                    <div class="py-1 pl-4 pr-2 w-full h-full flex items-center border-none rounded-none rounded-l-full @if($identifiers['identity'] == true) bg-color-new text-white @else bg-white @endif truncate uppercase select-none">{{$fieldTranslations['identity']}}</div>--}}
{{--                    @if($identifiers['identity'] == true)--}}
{{--                        <div class="flex justify-center bg-color-new h-full w-12 items-center  px-2 border-none rounded-none rounded-r-full"><i class="f7-icons text-white text-sm">checkmark_alt</i></div>--}}
{{--                    @else--}}
{{--                        <div class="flex justify-center bg-color-new h-full w-12 items-center px-2 border-none rounded-none rounded-r-full text-white">&nbsp;</div>--}}
{{--                    @endif--}}
{{--                </label>--}}
{{--            </div>--}}
        </div>

        <div class="flex justify-end mt-4">
            <x-button.primary class="ml-4" wire:click.prevent="doExportComments">
                @lang('export')
            </x-button.primary>
            <x-button.secondary class="ml-4" x-on:click="$dispatch('dropdown-select', { element: '' })">
                @lang('cancel')
            </x-button.secondary>
        </div>
    </div>
</div>