<template>
  <div class="label-selector" style="position: relative; width: 100%;">
    <!-- Trigger Button -->
    <div class="dropdown-trigger scrollable-container input-border" @click="toggleDropdown">
      <div v-if="getDisplayLabelsForSite('selector').length === 0" class="input-placeholder">Labels</div>
      <div v-else class="input-container">
       <span v-for="item in getDisplayLabelsForSite('selector')" :key="itemKey(item)" class="label-badge">
         {{ item.name }}
         <span @click.stop="removeItem(item)" class="badge-x"
               :style="{ background: hoverX === item.id ? '#e0e4e7' : 'transparent' }"
               @mouseenter="hoverX = item.id"
               @mouseleave="hoverX = null"
         >×</span>
       </span>
      </div>
    </div>

    <!-- Dropdown -->
    <div v-if="isOpen" class="dropdown-container">
      <!-- First RecursiveDropdown -->
      <recursive-dropdown
        :width="dropdownWidth"
        :style="{ marginRight: 'auto' }"
        :items="labelGroups"
        :level="0"
        :id-attr="'dlg_id'"
        :name-attr="'dlg_name'"
        @select="handleSelect"
        @deselect="handleDeselect"
        :selected-labels="selectedLabelsMap['selector']"
        :partially-selected-groups="partiallySelectedGroupsMap['selector']"
        :fully-selected-groups="fullySelectedGroupsMap['selector']"
        :active-group-id="activeGroupId"
        @show-next-level="handleNextLevel"
      />

      <!-- Second RecursiveDropdown -->
      <recursive-dropdown
        v-for="(items, index) in openMenus"
        :width="dropdownWidth"
        :key="index + 1"
        :items="items"
        :level="index + 1"
        :id-attr="'dl_id'"
        :name-attr="'dl_name'"
        @select="handleSelect"
        @deselect="handleDeselect"
        :selected-labels="selectedLabelsMap['selector']"
        :partially-selected-groups="partiallySelectedGroupsMap['selector']"
        :fully-selected-groups="fullySelectedGroupsMap['selector']"
        :active-group-id="activeGroupId"
        @show-next-level="handleNextLevel"
      />
    </div>
  </div>
</template>

<script>
import RecursiveDropdown from "./RecursiveDropdown.vue";
import LabelsMixin from "../../mixins/LabelsMixin";

export default {
  name: 'LabelSelector',
  components: {RecursiveDropdown},
  mixins: [LabelsMixin],

  props: {
    value: {
      type: Array,
      default: () => []
    },
    labelGroups: {
      type: Array,
      default: () => []
    },
    siteId: {
      type: [String, Number],
      required: true
    }
  },

  data() {
    return {
      isOpen: false,
      openMenus: [],
      activeGroupId: null,
      hoverX: null,
      maxLevels: 2,
    };
  },

  computed: {
    dropdownWidth() {
      return this.$el ? `${this.$el.offsetWidth / this.maxLevels}px` : '200px';
    }
  },

  mounted() {
    document.addEventListener('click', this.handleClickOutside);
    this.allLabelsGroups = this.labelGroups;
    this.initSiteLabels('selector', this.value, true);
  },

  beforeDestroy() {
    document.removeEventListener('click', this.handleClickOutside);
  },

  watch: {
    value: {
      immediate: true,
      deep: true,
      handler(newVal, oldVal) {
        if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
          this.initSiteLabels('selector', newVal, true);
        }
      }
    }
  },

  methods: {
    handleClickOutside(event) {
      if (!this.$el.contains(event.target)) {
        this.isOpen = false;
        this.openMenus = [];
        this.activeGroupId = null;
      }
    },

    toggleDropdown() {
      this.isOpen = !this.isOpen;
      if (!this.isOpen) {
        this.openMenus = [];
        this.activeGroupId = null;
      }
    },

    handleNextLevel({level, items, groupId}) {
      if (!items || items.length === 0) {
        this.openMenus = this.openMenus.slice(0, level);
      } else {
        this.openMenus = this.openMenus.slice(0, level);
        this.$set(this.openMenus, level, items);
      }
      this.activeGroupId = groupId;
    },

    handleSelect(item) {
      if (item.labels !== undefined) {
        if (item.labels && item.labels.length > 0) {
          const isSelected = this.fullySelectedGroupsMap['selector']?.some(g => g.dlg_id === item.dlg_id);
          if (isSelected) {
            this.deselectGroup(item);
          } else {
            this.selectGroup(item);
          }
        }
      } else {
        if (this.selectedLabelsMap['selector']?.some(l => l.dl_id === item.dl_id)) {
          this.deselectLabel(item);
        } else {
          this.selectLabel(item);
        }
      }
      this.$emit('input', [...this.selectedLabelsMap['selector']]);
    },

    handleDeselect(item) {
      this.handleSelect(item);
    },

    selectGroup(group) {
      const groupLabels = this.getAllLabelsForGroup(group);
      const currentLabels = this.selectedLabelsMap['selector'] || [];

      groupLabels.forEach(label => {
        if (!currentLabels.some(l => l.dl_id === label.dl_id)) {
          currentLabels.push(label);
        }
      });

      this.initSiteLabels('selector', currentLabels, true);
      this.$emit('change', [...currentLabels]);
    },

    deselectGroup(group) {
      const groupLabels = this.getAllLabelsForGroup(group);
      const currentLabels = this.selectedLabelsMap['selector'] || [];

      const filteredLabels = currentLabels.filter(
        label => !groupLabels.some(gl => gl.dl_id === label.dl_id)
      );

      this.initSiteLabels('selector', filteredLabels, true);
      this.$emit('change', [...filteredLabels]);
    },

    selectLabel(label) {
      const currentLabels = this.selectedLabelsMap['selector'] || [];
      if (!currentLabels.some(l => l.dl_id === label.dl_id)) {
        currentLabels.push(label);
        this.initSiteLabels('selector', currentLabels, true);
        this.$emit('change', [...currentLabels]);
      }
    },

    deselectLabel(label) {
      const currentLabels = this.selectedLabelsMap['selector'] || [];
      const filteredLabels = currentLabels.filter(l => l.dl_id !== label.dl_id);
      this.initSiteLabels('selector', filteredLabels, true);
      this.$emit('change', [...filteredLabels]);
    },

    removeItem(item) {
      if (item.type === 'group') {
        const group = this.fullySelectedGroupsMap['selector']?.find(g => g.dlg_id === item.id);
        if (group) {
          this.deselectGroup(group);
        }
      } else {
        const label = this.selectedLabelsMap['selector']?.find(l => l.dl_id === item.id);
        if (label) {
          this.deselectLabel(label);
        }
      }
    },
  }
};
</script>

<style scoped>
.dropdown-trigger {
  margin-left: 1rem;
  border: solid 0.001rem #f7d9d9;
  border-radius: 0;
  cursor: pointer;
  background: white;
  height: 1.75rem;
  min-height: 1.75rem;
  padding: 0 0.5rem;
  display: flex;
  align-items: center;
  overflow-x: auto;
  overflow-y: hidden;
  flex-shrink: 1;
  flex-grow: 0;
}

.input-border {
  border-style: solid;
  border-width: 1px;
  border-color: #808080a2;
}

.input-placeholder {
  color: #64748b;
  font-size: 0.8rem;
}

.input-container {
  display: flex;
  flex-wrap: nowrap;
  gap: 4px;
  min-width: min-content;
}

.label-badge {
  background: rgb(236 236 236);
  padding: 0 6px;
  border-radius: 3px;
  display: flex;
  align-items: center;
  border: 1px solid #f0f3f5;
  font-size: 0.8rem;
  color: #333;
  white-space: nowrap;
  height: 1.25rem;
  flex-shrink: 0;
}

.badge-x {
  margin-left: 4px;
  cursor: pointer;
  color: #666;
  font-size: 0.8rem;
  line-height: 1;
}

.dropdown-container {
  position: absolute;
  top: calc(100% + 2px);
  left: 0;
  background: white;
  border: solid 0.001rem #f7d9d9;
  z-index: 1000;
  display: flex;
  width: fit-content;
  min-width: 50%;
  max-width: 100%;
}

.scrollable-container::-webkit-scrollbar {
  height: 3px;
}

.scrollable-container::-webkit-scrollbar-track {
  background: transparent;
}

.scrollable-container::-webkit-scrollbar-thumb {
  background: #cdd4d9;
  border-radius: 3px;
}

.scrollable-container:hover::-webkit-scrollbar-thumb {
  background: #aab4bb;
}
</style>