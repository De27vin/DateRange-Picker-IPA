@props(['class' => ''])
<span {{ $attributes->merge(['class' => "{$class} block w-4 h-4 mx-1"]) }} style="margin-top: 0.1rem;">
	<svg class="block h-full h-full" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm-6 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm12 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor"/></svg>
</span>
