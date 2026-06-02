@props([
    'id' => null,
    'name' => null,
    'type' => 'text',
	'color' => 'default',
	'value' => null,
	'size' => 'md',
	'fullWidth' => null,
	'square' => false,
	'link' => null,
	'shadow' => null,
	'disabled' => null,
	'icon' => null,
    'iconPosition' => 'before',
])

{{--@dump($color == "primary" && !$disabled)--}}

@php
    isset($id) ? $icon : $name;
@endphp

@if($link)
<a
	href="{{ $link }}"
@else
<button
	type="{{ isset($type) ? $type : 'button' }}"
@endif

	@if ($id) id="{{ $id }}" @endif
	@if ($disabled) disabled="disabled" @endif

	{{ $attributes
		->class([
			"uppercase justify-center text-medium no-underline p-0 my-2",

			"inline-flex items-center" => (!$fullWidth),
			"inline-block w-full" => ($fullWidth),
			"rounded-full" => (!$square),
			"rounded-none" => ($square),
			'cursor-pointer text-white hover:text-white border-none bg-color-new hover:bg-color-new-700 focus:bg-color-new-700' => (($color == "primary" || $color == "blue") && !$disabled),
			'cursor-pointer text-white hover:text-white border-none bg-secondary-500 hover:bg-secondary-700 focus:bg-secondary-700' => ($color == "secondary" && !$disabled),
			'cursor-pointer text-white hover:text-white border-none bg-success-500 hover:bg-success-700 focus:bg-success-700' => ($color == "success" && !$disabled),
			'cursor-pointer text-white hover:text-white border-none bg-danger-500 hover:bg-danger-700 focus:bg-danger-700' => ($color == "danger" && !$disabled),
			'cursor-pointer text-white hover:text-white border-none bg-warning-500 hover:bg-warning-700 focus:bg-warning-700' => ($color == "warning" && !$disabled),
			'cursor-pointer text-white hover:text-white border-none bg-info-500 hover:bg-info-700 focus:bg-info-700' => ($color == "info" && !$disabled),
			'cursor-pointer text-white hover:text-white border-none bg-gray-400 hover:bg-gray-700 focus:bg-gray-700' => ($color == "default" && !$disabled),
            'cursor-pointer text-gray-600 hover:text-white border-none bg-white hover:bg-color-new-700 focus:bg-color-new-700' => ($color == "white" && !$disabled),
			'cursor-pointer text-gray-500 hover:text-gray-200 border-none bg-gray-400 bg-opacity-40 hover:bg-gray-500 focus:bg-gray-500 border-none rounded-none' => ($color == "light" && !$disabled),
			'cursor-pointer text-gray-600 hover:text-color-new-600 border-none bg-transparent hover:bg-transparent focus:bg-transparent' => ($color == "transparent" && !$disabled),

			'text-white opacity-20 border-color-new bg-color-new' => ($color == "primary" && $disabled),
			'text-white opacity-20 border-blue-500 bg-color-new' => ($color == "blue" && $disabled),
			'text-white opacity-20 bg-secondary-500 border-secondary-500' => ($color == "secondary" && $disabled),
			'text-white opacity-20 bg-success-500 border-success-500' => ($color == "success" && $disabled),
			'text-white opacity-20 bg-danger-500 border-danger-500' => ($color == "danger" && $disabled),
			'text-white opacity-20 bg-warning-500 border-warning-500' => ($color == "warning" && $disabled),
			'text-white opacity-20 bg-info-500 border-info-500' => ($color == "info" && $disabled),
			'text-white opacity-20 bg-gray-400 border-gray-400' => ($color == "default" && $disabled),
            'text-gray-600 opacity-20 bg-white border-gray-400' => ($color == "white" && $disabled),
			'text-gray-600 opacity-20 bg-gray-400 border-gray-400 border-none rounded-none' => ($color == "light" && $disabled),
			'text-gray-500 opacity-20 border-none bg-transparent' => ($color == "transparent" && $disabled),

			'text-xs py-0.5 px-1.5' => ($size == 'xs' && !$icon),
			'text-xs py-1 px-2' => ($size == 'sm' && !$icon),
			'text-xs py-2 px-6' => ($size == 'compact' && !$icon),
			'text-sm py-2 px-6' => ($size == 'md' && !$icon),
			'text-lg py-2 px-6' => ($size == 'lg' && !$icon),
			'text-lg py-2 px-6' => ($size == 'xl' && !$icon),
			'text-xl py-2.5 px-8' => ($size == '2xl' && !$icon),
			'text-2xl py-2.5 px-9' => ($size == '3xl' && !$icon),

			'text-xs p-0' => ($size == 'xs' && $icon),
			'text-xs p-0' => ($size == 'sm' && $icon),
			'text-xs p-0' => ($size == 'compact' && $icon),
			'text-sm p-0' => ($size == 'md' && $icon),
			'text-lg p-0' => ($size == 'lg' && $icon),
			'text-lg p-0' => ($size == 'xl' && $icon),
			'text-xl p-0' => ($size == '2xl' && $icon),
			'text-3xl p-0' => ($size == '3xl' && $icon),

			'shadow' => ($color != "transparent"),

			])
	}}
>


		@if ($icon && $iconPosition === 'before')
            <x-form.icon :icon="$icon" :size="$size" class="mr-1"/>
        @endif


	@if(!$value)
		{{ $slot }}
	@else
		{{ $value }}
	@endif

	@if ($icon && $iconPosition === 'after')
		<x-form.icon :icon="$icon" :size="$size" class="ml-1" />
	@endif


	@if($link)
</a>
@else
</button>
@endif

