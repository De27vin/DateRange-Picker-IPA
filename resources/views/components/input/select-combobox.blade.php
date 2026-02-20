<div class="relative z-30 _w-full _combobox"
     x-data="combobox($wire)"
     x-on:click.away="closeDropdown($wire)">
    <div x-on:click="showCheckboxes = !showCheckboxes" class=" text-gray-400 min-h-full cursor-pointer p-1 flex _flex-col items-center _combobox-select">
        <div class="mr-auto text-xs text-bold uppercase _combobox-placeholder" x-text="printLabel()"></div>
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
        {{-- <div class="mr-auto pr-6 combobox-selected" x-text="printSelected()"></div> --}}
    </div>
    <div x-show.transition="showCheckboxes" class="combobox-dropdown w-72" style="top:100%;">
        <template x-for="option in options">
            <div class="combobox-item">
                <input class="uiswitch uiswitch-new" type="checkbox"
                       x-model="selectedItems"
                       :value="option.value"
                       x-on:change="toggleOptionAll(option.value)">
                <label class="block m-1 px-4"><span x-text="option.label"></span></label>
            </div>
        </template>
    </div>
</div>

<script>
    function combobox($wire) {
        return {
            options: $wire.searchOptions,
            selectedItems: $wire.entangle('searchSelected').defer,
            filtersSearch: $wire.entangle('filters.search'),
            showCheckboxes: false,
            defaultLabel: '{!! __('All') !!}',
            printSelected() {
                return this.selectedItems.length > 0 ? this.selectedItems.map(item => this.options.find(option => option.value === item).label).join(', ') : ""
            },
            printLabel() {
                if(this.selectedItems.length > 0){
                    if(this.selectedItems.map(item => this.options.find(option => option.value === item).value) == 'all'){
                        return this.defaultLabel
                    } else {
                        return this.selectedItems.length
                    }
                } else {
                    return this.defaultLabel
                }
            },
            toggleOptionAll(item) {
                if(item === 'all') {
                    this.selectedItems = this.selectedItems.filter(selectedItem => selectedItem === 'all')
                } else {
                    this.selectedItems = this.selectedItems.filter(selectedItem => selectedItem !== 'all')
                }
            },
            closeDropdown($wire) {
                if (this.showCheckboxes) {
                    this.showCheckboxes = false
                    if (this.filtersSearch) {
                        $wire.searchSelected = this.selectedItems
                    }
                }
            }
        };
    }
</script>

<style>

    .combobox {
        /*        z-index: 60;*/
    }

    .combobox-select {
        /*        background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");*/
        /*        background-position:right .5rem center;*/
        /*        background-repeat:no-repeat;*/
        /*        background-size:2em 2em;*/
        /*        background-color:rgb(241 245 249);*/
        /*        outline:2px solid transparent;*/
        /*        outline-offset:2px;*/
        /*        color:#94a3b8;*/
        /*        opacity:1;*/
        /*        font-size: .75rem;*/
        /*        left: 0;*/
        /*        letter-spacing: 0;*/
        /*        line-height: 1rem;*/
        /*        padding-left: .75rem;*/
        /*        padding-right: .75rem;*/
        /*        padding-top: .5rem;*/
        /*        text-transform: uppercase;*/
        /*        right: 0;*/
    }

    .combobox-selected {
        color: #1e293b;
    }

    .label {

    }
</style>