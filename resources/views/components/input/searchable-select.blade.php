@props([
    'placeholder' => __('Choose list item ...'),
    'trailingAddOn' => null,
    'disabled' => false,
])

<div
        x-data="{
        open: false,
        search: '',
        selected: null,
        selectedText: '',
        init() {
            // Initialize with preselected value if it exists
            this.$nextTick(() => {
                if (this.$refs.select.value) {
                    const selectedOption = [...this.$refs.select.options].find(option => option.value === this.$refs.select.value);
                    if (selectedOption) {
                        this.selectedText = selectedOption.textContent;
                    }
                }
            });
        },
        select(value, text) {
            this.selected = value;
            this.selectedText = text;
            this.open = false;
            this.search = '';

            // Update the actual select element
            this.$refs.select.value = value;

            // Dispatch change event for Livewire to pick up
            this.$refs.select.dispatchEvent(new Event('change'));
        },
        clear() {
            this.selected = null;
            this.selectedText = '';
            this.search = '';

            // Update the actual select element
            this.$refs.select.value = '';

            // Dispatch change event for Livewire to pick up
            this.$refs.select.dispatchEvent(new Event('change'));
        },
        toggle() {
            if (!{{ $disabled ? 'true' : 'false' }}) {
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick(() => {
                        this.$refs.searchInput.focus();
                    });
                }
            }
        }
    }"
        class="w-full relative"
>
    <!-- Hidden select element for Livewire binding -->
    <select
            x-ref="select"
            {{ $attributes->except('class') }}
            class="hidden"
    >
        <option value="">{{ $placeholder }}</option>
        {{ $slot }}
    </select>

    <!-- Custom dropdown appearance - exactly matching regular select styling -->
    <div
            @click="toggle()"
            class="form-select block w-full h-16 pl-3 pr-10 pt-8 mt-0 mb-1 text-normal leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-color-new-300 {{ $trailingAddOn ? ' rounded-r-none' : '' }} {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : 'cursor-pointer' }}"
    >
        <div class="truncate flex justify-between items-center">
            <span x-text="selectedText || '{{ $placeholder }}'" class="truncate"></span>
            <div class="flex items-center">
                <button
                        x-show="selectedText && !{{ $disabled ? 'true' : 'false' }}"
                        @click.stop="clear()"
                        type="button"
                        class="text-gray-400 hover:text-gray-600 mr-1"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Dropdown options -->
    <div
            x-show="open"
            @click.away="open = false"
            class="absolute z-50 w-full bg-white mt-1 border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
            style="display: none;"
    >
        <div class="p-2 sticky top-0 bg-white border-b border-gray-200">
            <input
                    x-ref="searchInput"
                    x-model="search"
                    type="text"
                    class="h-10 w-full px-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-normal"
                    placeholder="Search..."
                    @keydown.escape.stop.prevent="open = false"
            >
        </div>

        <template x-for="option in [...$refs.select.options].slice(1).filter(option => option.textContent.toLowerCase().includes(search.toLowerCase()))" :key="option.value">
            <div
                    @click="select(option.value, option.textContent)"
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-normal"
                    :class="{ 'bg-blue-50': option.value === selected }"
                    x-text="option.textContent"
            ></div>
        </template>

        <div x-show="[...$refs.select.options].slice(1).filter(option => option.textContent.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-3 py-2 text-gray-500 italic">
            No results found
        </div>
    </div>

    @if ($trailingAddOn)
        {{ $trailingAddOn }}
    @endif
</div>