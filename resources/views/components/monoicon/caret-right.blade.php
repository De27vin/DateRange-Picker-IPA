@props(['class' => ''])
<span {{ $attributes->merge(['class' => " w-6 h-6 {$class} block"]) }}>
	<svg class="block h-full w-full" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 7l6 5-6 5V7z" fill="currentColor"/></svg>
</span>
