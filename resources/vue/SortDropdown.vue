<template>
  <div class="relative h-full py-0">
    <!-- Trigger button -->
    <button
      ref="button"
      @click.stop="toggle"
      :class="buttonClass"
      class="rounded-r-none h-9 font-bold uppercase justify-center text-medium no-underline p-0 inline-flex items-center"
    >
      <span class="truncate pr-px leading-none pt-1" :class="selectedLabelClass">
        {{ selectedLabel || placeholder }}
      </span>
      <span class="inset-y-0 right-0 flex items-center ml-4 pointer-events-none">
        <!-- double chevron similar to original component -->
        <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="none" stroke="currentColor">
          <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </span>
    </button>

    <!-- pop-over list -->
    <transition name="fade">
      <div
        v-if="open"
        class="absolute border-none mt-1 shadow-lg z-10 w-36"
      >
        <ul
          ref="listbox"
          role="listbox"
          tabindex="-1"
          class="max-h-60 overflow-auto focus:outline-none bg-secondary-600 text-secondary-200 text-sm text-bold"
        >
          <li
            v-for="(label, optKey, idx) in options"
            :key="optKey"
            :id="name + 'Option' + idx"
            :class="[
              'relative py-2 pl-3 cursor-pointer select-none pr-9',
              idx === focusedIndex ? 'text-white bg-color-new-600' : ''
            ]"
            @click.stop="select(optKey)"
            @mouseenter="focusedIndex = idx"
            @mouseleave="focusedIndex = null"
            role="option"
            :aria-selected="focusedIndex === idx"
          >
            <span class="block font-bold truncate">{{ label }}</span>
            <span
              v-if="optKey === value"
              class="absolute inset-y-0 right-0 flex items-center pr-2 pl-4 text-white"
            >
              <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </span>
          </li>
          <li
            v-if="!Object.keys(options).length"
            class="px-3 py-2 text-gray-900 cursor-pointer select-none"
          >
            {{ emptyOptionsMessage }}
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'SortDropdown',
  props: {
    value: {
      type: String,
      default: null
    },
    options: {
      type: Object,
      required: true
    },
    placeholder: {
      type: String,
      default: 'Select an option'
    },
    emptyOptionsMessage: {
      type: String,
      default: 'No results match your search.'
    },
    name: {
      type: String,
      default: 'sortOptions'
    }
  },
  data () {
    return {
      open: false,
      focusedIndex: null
    }
  },
  computed: {
    selectedLabel () {
      return this.value in this.options ? this.options[this.value] : ''
    },
    selectedLabelClass () {
      return { 'text-white': !this.selectedLabel || this.open }
    },
    buttonClass () {
      // replicate primary/default colour path similar to <x-form.button color="default">
      return 'cursor-pointer text-white hover:text-white border-none bg-gray-400 hover:bg-gray-700 focus:bg-gray-700 text-sm px-6'
    }
  },
  mounted () {
    document.addEventListener('click', this.handleOutsideClick)
  },
  beforeDestroy () {
    document.removeEventListener('click', this.handleOutsideClick)
  },
  methods: {
    handleOutsideClick (e) {
      if (!this.$el.contains(e.target)) {
        this.close()
      }
    },
    toggle () {
      if (this.open) {
        this.close()
      } else {
        // position focus index on current value
        this.focusedIndex = Object.keys(this.options).indexOf(this.value)
        if (this.focusedIndex < 0) this.focusedIndex = 0
        this.open = true
        this.$nextTick(() => {
          if (this.$refs.listbox && this.$refs.listbox.children[this.focusedIndex]) {
            this.$refs.listbox.children[this.focusedIndex].scrollIntoView({ block: 'nearest' })
          }
        })
      }
    },
    close () {
      this.open = false
      this.focusedIndex = null
    },
    select (val) {
      this.$emit('input', val)
      this.close()
    }
  }
}
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.1s ease-in;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style> 