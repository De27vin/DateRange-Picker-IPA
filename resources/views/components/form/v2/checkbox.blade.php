@props([
    'name' => null,
    'model' => null,
    'id' => null,
    'functionName' => null,
    'label' => null,
    'active' => false,
    'disabled' => false
])

<div
    wire:model="{{$model}}"
    style="@if($disabled) opacity: 0.65; @endif"
>
    <input
            type="checkbox"
            id="{{$id}}"
            name="{{$name}}"
            class="appearance-none hidden h-0 w-0"
            @if($disabled) disabled @endif
    />
    <label
            @if(!$disabled) wire:click="{{$functionName}}('{{$id}}')" @endif
            for="{{$id}}"
            class="group w-full flex items-center justify-between h-9 px-0 py-0 text-sm @if($disabled) cursor-default @else cursor-pointer @endif"
    >
        @if($active)
            <div class="py-2 pl-4 pr-2 w-full h-9 flex items-center border-none rounded-none rounded-l-full bg-color-new text-white truncate select-none">{{$label}}</div>
            <div class="flex justify-center bg-color-new h-9 w-12 items-center px-2 border-none rounded-none rounded-r-full">
                <i class="f7-icons text-white text-xl">checkmark_alt</i>
            </div>
        @else
            <div class="py-1 pl-4 pr-2 w-full h-9 flex items-center border border-color-new rounded-none rounded-l-full bg-white truncate select-none">{{$label}}</div>
            <div class="flex justify-center bg-color-new h-9 w-12 items-center px-2 border-none rounded-none rounded-r-full text-white">
                &nbsp;
            </div>
        @endif
    </label>
</div>