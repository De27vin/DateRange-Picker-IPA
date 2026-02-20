<ul x-data="{showEditSection: @entangle('showEditSection') }" class="deviceinfos bottom-underline divide-y divide-gray-400 -ml-10 pl-10">
    {{-- Device Infos --}}
    <li>
        <div class="info-container text-medium block px-2 py-2 text-sm hover:text-gray-900 has-children">
            <div class="flex items-center justify-between">
                    <span wire:click="toggleCustomFields" class="flex justify-start w-full text-base h-12 items-center">
                        @if($showEditSection)<i><x-monoicon.caret-down  class="flex mr-2" /></i>@endif
                        @if(!$showEditSection)<i><x-monoicon.caret-right  class="flex mr-2" /></i>@endif
                        @lang('Device Custom Fields')
                    </span>
            </div>
        </div>

        @php $fieldsCount = 0; @endphp

        <div x-cloak
                x-show="showEditSection"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-10"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-10"
                class="mt-0">
            <form wire:submit.prevent="updateCustomFields">

                <div class="md:flex flex-wrap mb-0">
                    <div class="block w-full">
                        <div class="ml-8 justify-between pb-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">

                            @forelse($deviceCustomFields as $customKey => $customField)
                                @php $fieldsCount++; @endphp
                                <x-forms.grid-group class="mb-4">
                                    <x-forms.label for="{{$customKey}}" :fallback="''">
                                        {{ !empty($customField['translations'][$locale]) ? $customField['translations'][$locale] : $customField['name'] }}
                                        @if(!empty($customField['required']))
                                            <x-monoicon.required />
                                        @endif
                                    </x-forms.label>
                                    <x-input.text wire:model.defer="deviceCustomFields.{{$customKey}}.value" canWriteSettings="{{$canWriteSettings}}" type="text" name="{{$customKey}}"  />
                                </x-forms.grid-group>
                            @empty
                                <p class="col-span-4 text-sm py-2 px-4 mb-8 mx-1 text-white bg-color-new-400">@lang('No custom fields are configured for Devices.')</p>
                            @endforelse

                        </div>
                    </div>
                </div>

                @if($canWriteSettings && !empty($fieldsCount))
                    <div class="flex justify-end mb-2 gap-2">
                        <x-button.primary type="submit">@lang('Update')</x-button.primary>
                        <x-button.secondary wire:click.prevent.self="cancelEditForm">@lang('Cancel')</x-button.secondary>
                    </div>
                @endif

            </form>

        </div>

    </li>
</ul>

