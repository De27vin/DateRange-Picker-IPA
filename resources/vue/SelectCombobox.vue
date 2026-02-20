<template>
  <div class="relative z-30 _w-full _combobox" v-click-outside="closeDropdown">
    <div @click="showCheckboxes = !showCheckboxes" class="text-gray-400 min-h-full cursor-pointer p-1 flex _flex-col items-center _combobox-select">
      <div class="mr-auto text-xs text-bold uppercase _combobox-placeholder">{{ printLabel() }}</div>
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
      </svg>
    </div>
    <div v-show="showCheckboxes" class="combobox-dropdown w-72" style="top:100%;">
      <template v-for="option in options">
        <div class="combobox-item" :key="option.value">
          <input class="uiswitch uiswitch-new" type="checkbox"
                 v-model="selectedItems"
                 :value="option.value"
                 @change="toggleOptionAll(option.value)">
          <label class="block m-1 px-4"><span>{{ option.label }}</span></label>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SelectCombobox',
  directives: {
    'click-outside': {
      bind(el, binding, vnode) {
        el.clickOutsideEvent = function(event) {
          if (!(el === event.target || el.contains(event.target))) {
            vnode.context[binding.expression](event);
          }
        };
        document.body.addEventListener('click', el.clickOutsideEvent);
      },
      unbind(el) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
      }
    }
  },
  props: {
    options: {
      type: Array,
      required: true
    },
    value: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      showCheckboxes: false,
      selectedItems: this.value,
      defaultLabel: 'All'
    }
  },
  watch: {
    value(newVal) {
      this.selectedItems = newVal;
    },
    selectedItems(newVal) {
      this.$emit('input', newVal);
    }
  },
  methods: {
    printLabel() {
      if (this.selectedItems.length > 0) {
        if (this.selectedItems.includes('all')) {
          return this.defaultLabel;
        } else {
          return this.selectedItems.length;
        }
      } else {
        return this.defaultLabel;
      }
    },
    toggleOptionAll(item) {
      if (item === 'all') {
        this.selectedItems = ['all'];
      } else {
        this.selectedItems = this.selectedItems.filter(selectedItem => selectedItem !== 'all');
      }
    },
    closeDropdown() {
      this.showCheckboxes = false;
    }
  }
}
</script>

<style>
.combobox {
  /* z-index: 60; */
}

.combobox-select {
  /* background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E"); */
  /* background-position:right .5rem center; */
  /* background-repeat:no-repeat; */
  /* background-size:2em 2em; */
  /* background-color:rgb(241 245 249); */
  /* outline:2px solid transparent; */
  /* outline-offset:2px; */
  /* color:#94a3b8; */
  /* opacity:1; */
  /* font-size: .75rem; */
  /* left: 0; */
  /* letter-spacing: 0; */
  /* line-height: 1rem; */
  /* padding-left: .75rem; */
  /* padding-right: .75rem; */
  /* padding-top: .5rem; */
  /* text-transform: uppercase; */
  /* right: 0; */
}

.combobox-selected {
  color: #1e293b;
}
</style>
