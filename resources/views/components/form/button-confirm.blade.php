@props([
    'id' => null,
	'color' => 'default',
	'size' => 'md',
	'fullWidth' => null,
	'square' => false,
	'shadow' => null,
	'disabled' => null,
	'icon' => null,
    'iconPosition' => 'before',
])
<div x-data="{ open: false }">
	<button 
		type="button" 
		id="{{ $id }}_button"
		@if ($disabled) disabled="disabled" @endif

		{{ $attributes
			->class([
				"uppercase justify-center text-medium no-underline p-0",
				
				"inline-flex items-center" => (!$fullWidth),
				"inline-block w-full" => ($fullWidth),
				"rounded-full" => (!$square),
				"rounded-none" => ($square),
				'cursor-pointer text-white hover:text-white border-none bg-color-new hover:bg-color-new-700 focus:bg-color-new-700' => ($color == "primary" && !$disabled),
				'cursor-pointer text-white hover:text-white border-none bg-color-new hover:bg-color-new-700 focus:bg-color-new-700' => ($color == "blue" && !$disabled),
				'cursor-pointer text-white hover:text-white border-none bg-secondary-500 hover:bg-secondary-700 focus:bg-secondary-700' => ($color == "secondary" && !$disabled),
				'cursor-pointer text-white hover:text-white border-none bg-success-500 hover:bg-success-700 focus:bg-success-700' => ($color == "success" && !$disabled),
				'cursor-pointer text-white hover:text-white border-none bg-danger-500 hover:bg-danger-700 focus:bg-danger-700' => ($color == "danger" && !$disabled),
				'cursor-pointer text-white hover:text-white border-none bg-warning-500 hover:bg-warning-700 focus:bg-warning-700' => ($color == "warning" && !$disabled),
				'cursor-pointer text-white hover:text-white border-none bg-info-500 hover:bg-info-700 focus:bg-info-700' => ($color == "info" && !$disabled),
				'cursor-pointer text-white hover:text-white border-none bg-gray-400 hover:bg-gray-700 focus:bg-gray-700' => ($color == "default" && !$disabled),
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
				'text-gray-600 opacity-20 bg-gray-400 border-gray-400 border-none rounded-none' => ($color == "light" && $disabled),
				'text-gray-500 opacity-20 border-none bg-transparent' => ($color == "transparent" && $disabled),

				'text-xs py-0.5 px-1.5' => ($size == 'xs' && !$icon),
				'text-xs py-1 px-2' => ($size == 'sm' && !$icon),
				'text-sm py-2 px-6' => ($size == 'md' && !$icon),
				'text-lg py-2 px-6' => ($size == 'lg' && !$icon),
				'text-lg py-2 px-6' => ($size == 'xl' && !$icon),
				'text-xl py-2.5 px-8' => ($size == '2xl' && !$icon),
				'text-2xl py-2.5 px-9' => ($size == '3xl' && !$icon),
				
				'text-xs p-0' => ($size == 'xs' && $icon),
				'text-xs p-0' => ($size == 'sm' && $icon),
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

			{{ $slot }}

		@if ($icon && $iconPosition === 'after')
			<x-form.icon :icon="$icon" :size="$size" class="ml-1" />
		@endif

	</button>
    <div x-cloak role="dialog"
        aria-labelledby="{{$id}}_label"
        aria-modal="true"
        tabindex="0"
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click.stop.prevent="open = false;"
        x-on:click.away.prevent="open = false;"
        class="fixed top-0 left-0 w-full h-screen flex justify-center items-center z-50 whitespace-normal">
        <div class="absolute top-0 left-0 w-full bg-black opacity-70 z-[70]"
             style="height: 200vh;"
             aria-hidden="true"
             x-show="open"></div>
        <div x-on:click.stop.prevent=""
             x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="flex flex-col shadow-lg overflow-hidden bg-white max-w-2xl z-[100]">

            <div class=" px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg">
                    {{ $title }}
                </h3>

                <div class="mt-2">
                    {{ $content }}
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-100 text-right">
                {{ $footer }}
            </div>
        </div>

    </div>
</div>
