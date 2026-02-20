@props([
    'for',
    'fallback' => null,
    'class'
])

<div>
    <label {{$attributes->merge(['class' => 'default'])}}  for="{{ $for }}">
        {{$slot}}
    </label>
    @if($fallback != null && !isset($fallback['value']))
        <span class="badge fill fields">{{$fallback}}</span>
    @endif
    @if(isset($fallback['value']) && $fallback['value'] !== '' && isset($fallback['label']))
        <div class="badge fill fields">
            <span>{{$fallback['value']}}</span>
            <span class="fallback-tooltip">{{$fallback['label']}}</span>
        </div>
    @endif
</div>

<style>
    /*
    .fallback-tooltip {
        position: absolute;
        display: none;
        bottom : 110%;
        right: 50%;
        padding: 10px;
        background-color: rgb(253 186 116);
        border-radius: 3px;
        font-size: 12px;
        color: #eee;
        animation: moveup 0.1s linear;
    }
    .badge:hover > .fallback-tooltip {
        display: block;
    }
    */
</style>