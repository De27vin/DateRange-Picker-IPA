@props([
    'color' => 'default',
    'icon' => null,
    'type' => 'span',
    'rounded' => null,
    'id' => null,
    'link' => null,
    'size' => 'sm',
    'opacity' => true,
    'target' => null
])
@php
    if($type == 'link'){
        $identifier = 'a';
    } else {
        $identifier = 'div';
    }
    if($opacity){
        $opacity = ' transparent ';
        $textcolor = '';
    } else {
        $opacity = ' full ';
        $textcolor = 'text-white';
    }
@endphp
<{{$identifier}} 
    class="f7-icons-wrapper {{$opacity}} {{$size}}"
    @if($link != null) href="{{$link}}" @endif >
    <i class="f7-icons {{$size}}">{{$icon}}</i>
</{{$identifier}}>