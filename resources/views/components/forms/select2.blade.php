<div
        x-data="select({ data: {{ $options }}, emptyOptionsMessage: 'No sort option selected.', name: 'sortOptions', placeholder: '{{ $placeholder }}', value: $wire.entangle('{{ $model }}')})"
        x-init="init()"
        x-on:click.away="closeListbox()"
        x-on:keydown.escape="closeListbox()"
        class="relative h-full py-0">
    <div class="h-full m-0">
        <x-form.button
                :color="$color ?? 'default'"
                x-ref="button"
                x-on:click="toggleListboxVisibility()"
                class="rounded-r-none h-9 font-bold">
            <span
                    {{-- x-show="! open" --}}
                    x-text="value in options ? options[value] : placeholder"
                    :class="{ 'text-white': ! (value in options), 'text-white' : open , 'text-white' : ! open}"
                    class="block h-full truncate pr-px"></span>

            <input
                    x-ref="search"
                    x-show="open"
                    x-model="search"
                    x-on:keydown.enter.stop.prevent="selectOption()"
                    x-on:keydown.arrow-up.prevent="focusPreviousOption()"
                    x-on:keydown.arrow-down.prevent="focusNextOption()"
                    type="hidden"
                    class="w-full h-full form-control focus:outline-none"/>

            <span class=" inset-y-0 right-0 flex items-center ml-4 pointer-events-none">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                        <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </span>
        </x-form.button>
    </div>

    <div
            x-show="open"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak
            class="absolute border-none mt-1 shadow-lg z-10 w-36">
        <ul
                x-ref="listbox"
                x-on:keydown.enter.stop.prevent="selectOption()"
                x-on:keydown.arrow-up.prevent="focusPreviousOption()"
                x-on:keydown.arrow-down.prevent="focusNextOption()"
                role="listbox"
                :aria-activedescendant="focusedOptionIndex ? name + 'Option' + focusedOptionIndex : null"
                tabindex="-1"
                class="max-h-60 overflow-auto focus:outline-none bg-secondary-600 text-secondary-200 text-sm text-bold">
            <template x-for="(key, index) in Object.keys(options)" :key="index">
                <li
                        :id="name + 'Option' + focusedOptionIndex"
                        x-on:click="selectOption()"
                        x-on:mouseenter="focusedOptionIndex = index"
                        x-on:mouseleave="focusedOptionIndex = null"
                        role="option"
                        :aria-selected="focusedOptionIndex === index"
                        :class="{ 'text-white bg-color-new-600': index === focusedOptionIndex, '': index !== focusedOptionIndex }"
                        class="relative py-2 pl-3 cursor-pointer select-none pr-9">
                    <span x-text="Object.values(options)[index]"
                          class="block font-bold truncate"></span>

                    <span
                            x-show="key === value"
                            :class="{ 'text-white': index === focusedOptionIndex, 'text-white': index !== focusedOptionIndex }"
                            class="absolute inset-y-0 right-0 flex items-center pr-2 pl-4 text-white">
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                </li>
            </template>

            <div
                    x-show="! Object.keys(options).length"
                    x-text="emptyOptionsMessage"
                    class="px-3 py-2 text-gray-900 cursor-pointer select-none"></div>
        </ul>
    </div>
</div>


@push('scripts')
    <script>
        function select(config) {
            return {
                data: config.data,
                emptyOptionsMessage: config.emptyOptionsMessage ?? 'No results match your search.',
                focusedOptionIndex: null,
                name: config.name,
                open: false,
                options: {},
                placeholder: config.placeholder ?? 'Select an option',
                search: '',
                value: config.value,
                closeListbox: function () {
                    this.open = false
                    this.focusedOptionIndex = null
                    this.search = ''
                },

                focusNextOption: function () {
                    if (this.focusedOptionIndex === null) return this.focusedOptionIndex = Object.keys(this.options).length - 1
                    if (this.focusedOptionIndex + 1 >= Object.keys(this.options).length) return
                    this.focusedOptionIndex++
                    this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                        block: "center",
                    })
                },

                focusPreviousOption: function () {
                    if (this.focusedOptionIndex === null) return this.focusedOptionIndex = 0
                    if (this.focusedOptionIndex <= 0) return
                    this.focusedOptionIndex--
                    this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                        block: "center",
                    })
                },

                init: function () {
                    this.options = this.data
                    if (!(this.value in this.options)) this.value = null
                    // this.$watch('search', ((value) => {
                    //     if (!this.open || !value) return this.options = this.data
                    //     this.options = Object.keys(this.data)
                    //         .filter((key) => this.data[key].toLowerCase().includes(value.toLowerCase()))
                    //         .reduce((options, key) => {
                    //             options[key] = this.data[key]
                    //             return options
                    //         }, {})
                    // }))
                },

                selectOption: function () {
                    if (!this.open) return this.toggleListboxVisibility()
                    this.value = Object.keys(this.options)[this.focusedOptionIndex]

                    this.closeListbox()
                },

                toggleListboxVisibility: function () {
                    if (this.open) return this.closeListbox()
                    this.focusedOptionIndex = Object.keys(this.options).indexOf(this.value)
                    if (this.focusedOptionIndex < 0) this.focusedOptionIndex = 0
                    this.open = true
                    this.$nextTick(() => {
                        // this.$refs.search.focus()
                        this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                            block: "nearest"
                        })
                    })
                },
            }
        }
    </script>
@endpush
