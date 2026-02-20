@props([
    'color' => "default",
    'size' => "xs",
	'rounded' => null,
	'outlined' => null
])

<span {{ $attributes->class([
	"font-medium px-4 py-1 leading-0 whitespace-nowrap h-6 items-center flex",

	"text-color-new-800" => ($color == "primary"),
	"text-secondary-800" => ($color == "secondary"),
	"text-success-800" => ($color == "success"),
	"text-warning-800" => ($color == "warning"),
	"text-danger-800" => ($color == "danger"),
	"text-gray-800" => ($color == "info"),
	"text-error-800" => ($color == "error"),
	"text-white" => ($color == "default"),
	
	"bg-color-new-400" => ($color == "primary" && !$outlined),
	"bg-secondary-400" => ($color == "secondary" && !$outlined),
	"bg-success-400" => ($color == "success" && !$outlined),
	"bg-warning-400" => ($color == "warning" && !$outlined),
	"bg-danger-400" => ($color == "danger" && !$outlined),
	"bg-gray-400" => ($color == "info" && !$outlined),
	"bg-error-400" => ($color == "error" && !$outlined),
	"bg-gray-400" => ($color == "default" && !$outlined),
	
	"border border-color-new-400" => ($color == "primary" && $outlined),
	"border border-secondary-400" => ($color == "secondary" && $outlined),
	"border border-success-400" => ($color == "success" && $outlined),
	"border border-warning-400" => ($color == "warning" && $outlined),
	"border border-danger-400" => ($color == "danger" && $outlined),
	"border border-gray-400" => ($color == "info" && $outlined),
	"border border-error-400" => ($color == "error" && $outlined),
	"border border-gray-400" => ($color == "default" && $outlined),

    'text-xs' => ($size == 'xs'),
    'text-sm' => $size == 'sm',
    'text-md' => $size == 'md',
    'text-lg' => ($size == 'lg'),
    'text-xl' => ($size == 'xl'),
    'text-2xl' => ($size == '2xl'),
    'text-3xl' => ($size == '3xl'),

	'rounded' => ($rounded),
	
]) }}>

{{ $slot }}

</span>
