@props([
    'for',
    'fallback' => null,
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