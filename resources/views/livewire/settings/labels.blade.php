<div class="mt-16 mx-auto pb-12 px-12">

    @if(!config('ucp.active_labels'))
        @dd('Access Denied')
    @endif

    <x-page.header class="px-4">
        <x-slot name="title">@lang('Group Management')</x-slot>
        <x-slot name="description">
            @lang('On this page you can manage groups and their settings')<br/>
            @lang('The labels can be organized hierarchically. In each case, the values belonging to the lowest label in the structure are taken into account.')
        </x-slot>
    </x-page.header>

    <div class="flex w-full">
        <div class="relative w-full lg:w-1/2">
            <form>
                <div class="relative block group form-select w-full py-8 mt-0 text-gray-800 text-base leading-6 focus:outline-none focus:shadow-outline-blue focus:border-color-new-300 sm:text-sm sm:leading-5" id="">

                    @if(!count($groups ?? []))
                        <div class="relative px-2 py-2 my-2 flex  items-center justify-between">
                            <div class="min-w-0 flex text-sm items-center justify-start">
                                <label class=" font-medium text-gray-400 select-none">
                                    @lang('No labels groups added for your account')
                                </label>
                            </div>
                        </div>
                    @endif

                    <x-laravel-blade-sortable::sortable
                        name="groups"
                        group="groups-only"
                        wire:onSortOrderChange="handleSortOrderChangeGroup"
                        class="text-gray-600 border-r border-b border-slate-300 shadow-lg font-medium"
                        drag-handle="group-drag-handle"
                        ghost-class="opacity-300">


                        @foreach($groups->sortBy('dlg_order') as $group)
                                <x-labels.group :group="$group" :editing="$editing"></x-labels.group>
                        @endforeach
                    </x-laravel-blade-sortable::sortable>
                </div>

                @if(!$showAddGroup && !$showAddLabel && !$selectedLabel && !$selectedGroup)
                    <div class="flex w-full justify-end topActionBtn">
                        <x-button.action wire:click.prevent="$set('showAddGroup', true)">
                            @lang('Add new group')
                        </x-button.action>
                    </div>
                @endif

                @if($showAddGroup && !$showAddLabel && !$selectedLabel && !$selectedGroup)
                    <div class="block lg:flex w-full items-center justify-between pb-12" wire:key="add-group-form">
                        <div class="relative block w-full">
                            <label class="default" for="dt_name">
                                @lang('Group name')
                            </label>
                            <input wire:model.defer="editing.group_name" class="" name="name" id="name" type="text">
                            @error('editing.group_name')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        @if($canWriteSettings)
                            <div class="flex justify-end" style="align-self: start; padding-top: 0.3rem;">
                                <x-button.primary wire:click.prevent="insertGroup" type="submit">@lang('Save')</x-button.primary>
                                <x-button.secondary wire:click.prevent="resetStateFresh">@lang('Cancel')</x-button.secondary>
                            </div>
                        @endif
                    </div>
                @endif

                @if($selectedGroup && !$showAddLabel)
                    <div class="block lg:flex w-full items-center justify-between pb-12" wire:key="edit-group-{{ $selectedGroup->dlg_id }}">
                        <div class="relative block w-full">
                            <label class="default" for="dt_name">
                                @lang('Group name')
                            </label>
                            <input wire:model.defer="selectedGroup.dlg_name" class="" name="name" id="name" type="text">
                            @error('selectedGroup.dlg_name')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        @if($canWriteSettings)
                            <div class="flex justify-end">
                                <x-button.primary wire:click.prevent="updateGroup" type="submit">@lang('Update')</x-button.primary>
                                <x-button.action wire:click.prevent.self="onShowDeleteModalGroup()" class="bg-red-600 text-white text-sm hover:bg-red-800" type="button">@lang('Delete')</x-button.action>
                                <x-button.secondary wire:click.prevent="resetStateFresh">@lang('Cancel')</x-button.secondary>
                            </div>
                        @endif
                        <div class="flex w-full justify-end topActionBtn">
                            <x-button.action wire:click.prevent="$set('showAddLabel', true)">
                                @lang('Add new label')
                            </x-button.action>
                        </div>
                    </div>
                @endif

                @if($showAddLabel)
                    <div class="block lg:flex w-full items-center justify-between pb-12" wire:key="add-label-form">
                        <div class="relative block w-full">
                            <label class="default" for="dt_name">
                                @lang('Label name')
                            </label>
                            <input wire:model.defer="editing.label_name" class="" name="name" id="name" type="text">
                            @error('editing.label_name')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        @if($canWriteSettings)
                            <div class="flex justify-end">
                                <x-button.primary wire:click.prevent="insertLabel" type="submit">@lang('Save')</x-button.primary>
                                <x-button.secondary wire:click.prevent.stop="resetStateFresh">@lang('Cancel')</x-button.secondary>
                            </div>
                        @endif
                    </div>
                @endif


            </form>
        </div>

        <div class="relative w-full lg:w-1/2">
            @if($selectedLabel)
                <dl class="text-sm py-2 px-4 my-8 ml-4 mr-1 text-gray-800 bg-transparent">
                    @foreach($customSettingList as $key => $identifier)
                        <dt class="text-bold uppercase">{{ \Illuminate\Support\Str::replace('_', ' ', $key)}}</dt>
                        <dd class="mb-2 text-gray-500">{{ __($identifier)}}</dd>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>

    @if($selectedLabel)
        <div class="w-full pt-4 pb-8" wire:key="edit-label-{{ $selectedLabel->dl_id }}">

            <div class="block lg:flex w-full items-center justify-between pb-12">
                <div class="w-full">
                    <x-forms.grid-group class="relative w-full px-1">
                        <x-forms.label for="selectedNodeName" :fallback="''">
                            {{ __('Label name') }}
                        </x-forms.label>
                        <x-input.text wire:model.defer="selectedLabel.dl_name" canWriteSettings="{{$canWriteSettings}}" type="text" />
                        @error('selectedLabel.dl_name')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </x-forms.grid-group>
                </div>


                @if($canWriteSettings)
                    <div class="flex w-full justify-end">
                        <div class="flex justify-end">
                            <x-button.primary wire:click.prevent="updateLabel" type="submit">@lang('Update')</x-button.primary>
                            <x-button.action wire:click.prevent.self="onShowDeleteModalLabel()" class="bg-red-600 text-white text-sm hover:bg-red-800" type="submit">@lang('Delete')</x-button.action>
                            <x-button.secondary wire:click.prevent="resetState">@lang('Cancel')</x-button.secondary>
                        </div>
                    </div>
                @endif
            </div>

            {{-- SETTINGS LIST --}}
{{--            <x-labels.settings--}}
{{--                :settings="$settings"--}}
{{--                :canWriteSettings="$canWriteSettings"--}}
{{--                :translations="$translations"--}}
{{--                :locale="$locale"--}}
{{--            ></x-labels.settings>--}}

            <x-settings.advanced-settings
                :advancedSettings="$advancedSettings"
                :updateMethodName="'updateLabelSettings'"
                :model="'advancedSettings'"
            ></x-settings.advanced-settings>

        </div>
    @endif

    <!-- Delete Modal GROUP -->
    <x-modal.confirmation-new wire:model="showDeleteModalGroup">
        <x-slot name="title">
            @lang('Delete Group')
        </x-slot>

        <x-slot name="content">
            <div class="py-8 text-cool-gray-700">
                <p>@lang('Are you sure you want to delete group'): {{ $selectedGroup?->dlg_name }}?</p>
                @if($messages && count($messages))
                    <ul>
                        @foreach($messages as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-button.secondary wire:click="$set('showDeleteModalGroup', false)">{{__('Cancel')}}</x-button.secondary>
            <x-button.primary wire:click="deleteGroup">{{__('Delete')}}</x-button.primary>
        </x-slot>
    </x-modal.confirmation-new>


    <!-- Delete Modal LABEL -->
    <x-modal.confirmation-new wire:model="showDeleteModalLabel">
        <x-slot name="title">
            @lang('Delete label')
        </x-slot>

        <x-slot name="content">
            <div class="py-8 text-cool-gray-700">
                <p>@lang('Are you sure to delete label'): {{ $selectedLabel?->dl_name }}?</p>
                @if(count($messages))
                    <ul>
                        @foreach($messages as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-button.secondary wire:click="$set('showDeleteModalLabel', false)">{{__('Cancel')}}</x-button.secondary>
            <x-button.primary wire:click="deleteLabel">{{__('Delete')}}</x-button.primary>
        </x-slot>
    </x-modal.confirmation-new>

    <script>
    document.querySelectorAll('.node-content').forEach(item => {
        item.addEventListener('dragstart', () => {
            item.classList.add('dragging');
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
        });
    });
    </script>
</div>