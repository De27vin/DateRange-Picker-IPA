@props(['group', 'editing'])

<x-laravel-blade-sortable::sortable-item
        sort-key="group-{{ $group['dlg_id'] }}"
        wire:key="group-{{ $group['dlg_id'] }}"
        draggable="false"
>
    <div class="node_ block cursor-pointer @if($this->selectedGroup?->dlg_id == $group['dlg_id']) bg-color-new-400 text-white @endif">
        <div wire:click.prevent.stop="toggleGroup({{$group['dlg_id']}})" class="node-content text-gray-700 @if($this->selectedGroup?->dlg_id === $group['dlg_id']) active @endif flex justify-between">

            <div class="min-w-0 w-full flex text-sm h-6 items-center justify-start">

                <div class="items-center group-drag-handle" style="cursor: s-resize;">
                    <x-monoicon.drag class="flex text-gray-500 text-medium hover:text-white w-6 h-6"></x-monoicon.drag>
                </div>

                <label class="relative px-2 cursor-pointer font-medium select-none" for="{{ $group['dlg_id'] }}">
                    <span>{{ $group['dlg_name'] }}</span>
                </label>

            </div>
        </div>

        <x-laravel-blade-sortable::sortable
            name="labels-{{ $group['dlg_id'] }}"
            group="labels-only"
            wire:onSortOrderChange="handleSortOrderChangeLabel"
            class="node node-{{ $group['dlg_id'] }}"
            drag-handle="label-drag-handle"
            ghost-class="opacity-300"
            style="border-left: 1px solid #94a3b8;">

            @forelse($group->labels->sortBy('dl_order') as $label)
                <x-labels.label :label="$label" :editing="$editing" wire:key="label-{{ $label['dl_id'] }}"></x-labels.label>
            @empty
                <div class="empty-placeholder text-gray-500 italic">
                    @lang('no labels')
                </div>
            @endforelse
        </x-laravel-blade-sortable::sortable>

    </div>
</x-laravel-blade-sortable::sortable-item>
