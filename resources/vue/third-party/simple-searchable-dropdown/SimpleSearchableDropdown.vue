<template>
  <div class="dropdown" v-if="options">

    <!-- Dropdown Input -->
    <input class="dropdown-input"
      :name="name"
      @focus="showOptions()"
      @blur="exit()"
      @keyup="keyMonitor"
      v-model="searchFilter"
      :disabled="disabled"
      :placeholder="placeholder" />

    <!-- Dropdown Menu -->
    <div class="dropdown-content" v-show="optionsShown">
      <div class="dropdown-item"
        :class="{ 'dropdown-item-selected': option.id === selected.id }"
        @mousedown="selectOption(option)"
        v-for="(option, index) in filteredOptions"
        :key="index">
          {{ option.name || option.id || '-' }}
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      name: {
        type: String,
        required: false,
        default: 'dropdown',
        note: 'Input name'
      },
      options: {
        type: Array,
        required: true,
        default: [],
        note: 'Options of dropdown. An array of options with id and name',
      },
      placeholder: {
        type: String,
        required: false,
        default: 'Please select an option',
        note: 'Placeholder of dropdown'
      },
      disabled: {
        type: Boolean,
        required: false,
        default: false,
        note: 'Disable the dropdown'
      },
      maxItem: {
        type: Number,
        required: false,
        default: 10,
        note: 'Max items showing'
      },
      defaultSelected: {
        type: Object,
        required: false,
        default: () => ({}),
        note: 'Default selected option'
      }
    },
    data() {
      return {
        selected: {},
        optionsShown: false,
        searchFilter: ''
      }
    },
    created() {
      if (this.defaultSelected?.id) {
        this.selected = this.defaultSelected;
        this.searchFilter = this.defaultSelected.name;
      }
      this.$emit('selected', this.selected);
    },
    computed: {
      filteredOptions() {
        try {
          const emptyOption = { id: '', name: 'Please select value' };
          const escapedSearch = this.searchFilter.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          const regOption = new RegExp(escapedSearch, 'ig');

          let filtered = [];

          // If no search filter, show empty option at top
          if (this.searchFilter.trim().length < 1) {
            filtered = [emptyOption, ...this.options];
          } else {
            // When searching, filter options and include currently selected option if it matches
            filtered = this.options.filter(option =>
              option.name.match(regOption)
            );
          }

          // Always include selected option in the list if it exists and matches filter
          if (this.selected?.id && this.selected.name.match(regOption)) {
            if (!filtered.find(opt => opt.id === this.selected.id)) {
              filtered.push(this.selected);
            }
          }

          return filtered;
        } catch (e) {
          const emptyOption = { id: '', name: 'Please select value' };
          const search = this.searchFilter.trim().toLowerCase();

          let filtered = [];

          if (search.length < 1) {
            filtered = [emptyOption, ...this.options];
          } else {
            filtered = this.options.filter(option =>
              option.name.toLowerCase().includes(search)
            );
          }

          // Always include selected option in the list if it exists and matches filter
          if (this.selected?.id && this.selected.name.toLowerCase().includes(search)) {
            if (!filtered.find(opt => opt.id === this.selected.id)) {
              filtered.push(this.selected);
            }
          }

          return filtered;
        }
      }
    },
    methods: {
      selectOption(option) {
        this.selected = option.id === '' ? {} : option;
        this.optionsShown = false;
        this.searchFilter = this.selected.name || '';
        this.$emit('selected', this.selected);
      },

      showOptions() {
        if (!this.disabled) {
          // Keep the current selection visible in the input
          this.searchFilter = this.selected.name || '';
          this.optionsShown = true;
        }
      },

      exit() {
        // Restore the selected value when clicking outside
        this.searchFilter = this.selected.name || '';
        this.optionsShown = false;
        this.$emit('selected', this.selected);
      },

      // Selecting when pressing Enter
      keyMonitor: function(event) {
        if (event.key === "Enter" && this.filteredOptions[0]) {
          this.selectOption(this.filteredOptions[0]);
        }
      }
    },
    watch: {
      searchFilter() {
        // Don't automatically select first option when typing
        if (this.filteredOptions.length === 0) {
          // Only clear selection if user explicitly chooses empty option
          // this.selected = {};
        }
        this.$emit('filter', this.searchFilter);
      }
    },
  };
</script>

<style lang="scss" scoped>
  .dropdown {
      width: 100%;
      margin-left: 1rem;

      .dropdown-input {
        width: 100%;
        padding-left: 0.3rem;
        background: #fff;
        cursor: pointer;
        border: 1px solid #808080a2;
        border-radius: 3px;

        &:hover {
          background: #f8f8fa;
        }

        &:focus {
            outline: none;
        }
      }

      .dropdown-content {
        position: absolute;
        background-color: #fff;
        min-width: 248px;
        max-width: 248px;
        height: 248px;      // Fixed height instead of max-height
        border: 1px solid #808080a2;
        box-shadow: 0px -8px 34px 0px rgba(0,0,0,0.05);
        overflow-y: auto;   // This will enable scrolling for all items
        overflow-x: hidden;
        z-index: 1;

          &::-webkit-scrollbar {
            width: 8px;
          }

          &::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
          }

          &::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;

            &:hover {
              background: #a8a8a8;
            }
          }

          .dropdown-item {
              color: black;
              font-size: .7em;
              line-height: 1em;
              padding: 8px;
              text-decoration: none;
              display: block;
              cursor: pointer;
              &:hover {
                  background-color: #e7ecf5;
              }
              &.dropdown-item-selected {
                  background-color: #e7ecf5;
                  font-weight: 500;
              }
          }
      }
  }
</style>