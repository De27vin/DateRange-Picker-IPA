{{-- input.select.blade.php --}}
@props([
    'placeholder' => __('Choose list item ...'),
    'trailingAddOn' => null,
])
<div class="w-full">
  <select {{ $attributes->merge(['class' => 'form-select block w-full h-16 pl-3 pr-10 pt-8 mt-0 mb-1 text-normal leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-color-new-300' . ($trailingAddOn ? ' rounded-r-none' : '')]) }}>
    <option value="">{{ $placeholder }}</option>

    {{ $slot }}
  </select>

  @if ($trailingAddOn)
    {{ $trailingAddOn }}
  @endif
</div>
