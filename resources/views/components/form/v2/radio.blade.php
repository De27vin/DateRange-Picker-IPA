@props([
    'name' => null,
    'model' => null,
    'id' => null,
    'functionName' => null,
    'label' => null,
    'active' => false,
    'disabled' => false,
    'rl' => false,
    'rr' => false,
])

<div class="relative flex items-center" style="@if($disabled) opacity: 0.65; @endif">
    <input
        @if(!$disabled) wire:click.prevent="{{$functionName}}('{{$id}}')" @endif
        wire:model="{{$model}}"
        id="{{$id}}"
        class="absolute left-0 opacity-0"
        type="radio"
        value="{{$active}}"
        @if($disabled) disabled @endif />
    @if($active)
        <label {!! $attributes->merge(['class' => 'bg-color-new py-2 px-4 ' . ($disabled ? ' cursor-default' : 'cursor-pointer') . ($rl ? ' rounded-l-full' : '') . ($rr ? ' rounded-r-full' : '') ]) !!} for="{{$id}}">{{$label}}</label>
    @else
        <label {!! $attributes->merge(['class' => 'bg-gray-400 py-2 px-4 ' . ($disabled ? ' cursor-default' : 'cursor-pointer hover:bg-color-new-400') . ($rl ? ' rounded-l-full' : '') . ($rr ? ' rounded-r-full' : '') ]) !!} for="{{$id}}">{{$label}}</label>
    @endif
</div>
