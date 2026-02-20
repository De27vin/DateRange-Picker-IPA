@props(['label' => '', 'class' => ''])
@if(strlen($slot->toHtml()) != 0)
    <div {{ $attributes->merge(['class' => "{$class} boxfield"]) }}>
        <div class="label">@lang($label)</div>
        <div class="fieldvalue flex flex-wrap pt-1 space-x-2">{{ $slot }}</div>
    </div>
@else
    <p class="boxfield">&nbsp;</p>
@endif
