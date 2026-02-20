@props(['label', 'editing'])

<x-laravel-blade-sortable::sortable-item
        sort-key="label-{{ $label['dl_id'] }}"
        wire:key="label-{{ $label['dl_id'] }}"
        draggable="false"
>
    <div class="node_ block @if($this->selectedLabel?->dl_id === $label['dl_id']) bg-color-new-400 text-white @endif">

        <div wire:click.prevent.stop="toggleLabel({{ $label['dl_id'] }})" class="node-content text-gray-700 @if($this->selectedLabel?->dl_id === $label['dl_id']) active @endif flex justify-between"
            style="margin-left: 3rem;"
        >
            <div class="min-w-0 w-full flex text-sm h-6 items-center justify-start">

                <div class="items-center label-drag-handle" style="cursor: s-resize;">
                    <x-monoicon.drag class="flex text-gray-500 text-medium hover:text-white w-6 h-6"></x-monoicon.drag>
                </div>

                <label class="relative px-2 cursor-pointer font-medium select-none" for="{{ $label['dl_id'] }}">
                    <span>{{ $label['dl_name'] }}</span>
                </label>

            </div>
        </div>
    </div>
</x-laravel-blade-sortable::sortable-item>
