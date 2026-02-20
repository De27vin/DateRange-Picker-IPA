{{--searchable-input.blade.php--}}
<div>
    <div x-data="window.searchableInput($wire)" @click.away="closeOptions()" x-cloak>
        <input type="hidden" :value="selected.value">
        <input type="text" x-model="search" placeholder="{{__('Click to search')}}..."
               :class="(valid ? '' : 'invalid-border') + ' h-16'"
               @click="showOptions = !showOptions"
               @focus="handleFocus()"
               @keyup="handleKeyUp()">
{{--        <span x-show="!valid && !showOptions" class="invalid-text">invalid value</span>--}}
        <div class="bg-white w-full h-48 overflow-y-scroll z-50" x-show="showOptions">
            <div x-text="filteredOptions().length === 0 ? 'No result' : '{{ $optionsHeader }}'" class="my-1 mx-2 border-b-2"></div>
            <template x-for="option in filteredOptions()">
                <div @click="selectOption(option)" x-text="option.label"
                     class="my-1 mx-2 cursor-pointer hover:bg-color-new-400 border-b-1">
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    window.searchableInput = function ($wire) {
        return {
            options: $wire.{{ $optionsField }},
            selected: $wire.entangle('{{ $selectedField }}').defer,
            search: '{{ $initValue }}',
            valid: true,
            showOptions: false,
            filteredOptions() {
                if (this.search === '{{ $initValue }}') {
                    return this.options;
                }
                return this.options.filter((option) => {
                    return option.label.includes(this.search.toLowerCase());
                });
            },
            selectOption(option) {
                this.selected = option;
                this.showOptions = false;
                this.search = option.label;
                this.validate();
            },
            closeOptions() {
                this.showOptions = false;
                this.validate();
            },
            handleFocus() {
                this.valid = true;
            },
            handleKeyUp() {
                this.showOptions = true;
                this.validate();
            },
            validate() {
                let selected = this.options.find(opt => opt.label === this.search);
                this.valid = !!selected;
                if (!this.valid) {
                    this.selected = {};
                } else {
                    this.selected = selected;
                }
            }
        };
    };
</script>

<style>
    .invalid-border {
        border-color: red !important;
        border-width: 2px !important;
    }
    .invalid-text {
        color: red !important;
        font-size: 16px !important;
    }
</style>