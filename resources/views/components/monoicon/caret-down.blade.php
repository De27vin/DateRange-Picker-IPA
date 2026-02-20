@props(['class' => ''])
<span {{ $attributes->merge(['class' => " w-6 h-6 {$class} block"]) }}>
	<svg class="block h-full w-full" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 10l-5 6-5-6h10z" fill="currentColor"/></svg>
</span>
