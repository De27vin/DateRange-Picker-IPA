@props(['class' => ''])
<span {{ $attributes->merge(['class' => "{$class} block w-3 h-3 m-1"]) }}>
	<svg class="block h-full w-full" viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg"><path xmlns="http://www.w3.org/2000/svg" d="M5 7C5 5.89543 5.89543 5 7 5H17C18.1046 5 19 5.89543 19 7V17C19 18.1046 18.1046 19 17 19H7C5.89543 19 5 18.1046 5 17V7ZM17 7L7 7V17H17V7Z" fill="currentColor"></path></svg>
</span>

