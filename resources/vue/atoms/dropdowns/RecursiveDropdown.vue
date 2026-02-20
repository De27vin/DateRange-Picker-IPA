<template>
  <div class="dropdown-menu">
    <div class="menu-content">
      <div v-for="item in items" :key="itemKey(item)"
        @mouseenter="handleMouseEnter(item)"
        @mouseleave="handleMouseLeave"
        @click="handleItemClick(item)"
        :class="['menu-item', { 'menu-item--active': item[idAttr] === hoverItemId || item[idAttr] === activeGroupId }]">

        <div class="custom-checkbox">
          <div v-if="getCheckboxState(item) === 'full'" class="checkbox-indicator checkbox-indicator--full">
            <div class="checkmark"></div>
          </div>

          <div v-else-if="getCheckboxState(item) === 'partial'" class="checkbox-indicator checkbox-indicator--partial"></div>
        </div>

        <span class="item-label">{{ item[nameAttr] }}</span>

        <span
          v-if="item.labels && item.labels.length"
          class="arrow-icon"
        >›</span>
      </div>
    </div>
  </div>
</template>

<script>
import LabelsMixin from "../../mixins/LabelsMixin";

export default {
  mixins: [LabelsMixin],

  props: {
    items: Array,
    level: Number,
    selectedLabels: Array,
    partiallySelectedGroups: Array,
    fullySelectedGroups: Array,
    width: { type: String, default: '200px'},
    idAttr: { type: String, default: 'id'},
    nameAttr: { type: String, default: 'name'},
    activeGroupId: {
      type: [String, Number],
      default: null
    }
  },

  data() {
    return {
      openGroupId: null,
      hoverItemId: null
    };
  },

  created() {
    if (this.items) {
      // in mixin
      this.allLabelsGroups = this.items;
    }
  },

  methods: {
    handleMouseEnter(item) {
      this.hoverItemId = item[this.idAttr];

      // If item has labels, show them in next level
      if (item.labels && item.labels.length > 0) {
        this.$emit('show-next-level', {
          level: this.level,
          items: item.labels,
          groupId: item[this.idAttr]
        });
      } else if (item.labels && item.labels.length === 0) {
        // If item is a group but has no labels, clear the next levels
        this.$emit('show-next-level', {
          level: this.level,
          items: [],
          groupId: item[this.idAttr]
        });
      }
    },

    handleMouseLeave() {
      this.hoverItemId = null;
    },

    handleItemClick(item) {
      this.$emit('select', item);
      if (item.labels && item.labels.length) {
        this.$emit('show-next-level', {
          level: this.level,
          items: item.labels,
          groupId: item[this.idAttr]
        });
      }
    },

    getCheckboxState(item) {
      if (item.labels && item.labels.length) {
        if (this.isGroupFullySelected(item)) {
          return 'full';
        } else if (this.isGroupPartiallySelected(item)) {
          return 'partial';
        }
        return 'empty';
      } else {
        return this.isLabelSelected(item) ? 'full' : 'empty';
      }
    },

    isLabelSelected(label) {
      return this.selectedLabels.some(l => l[this.idAttr] === label[this.idAttr]);
    },

    isGroupFullySelected(group) {
      return this.fullySelectedGroups.some(g => g[this.idAttr] === group[this.idAttr]);
    },

    isGroupPartiallySelected(group) {
      return this.partiallySelectedGroups.some(g => g[this.idAttr] === group[this.idAttr]);
    }
  }
};
</script>

<style scoped>
.dropdown-menu {
  width: v-bind(width);
  border-right: 1px solid #cdd4d9;
  overflow: auto;
  max-height: 300px;
  direction: rtl;
}

.menu-content {
  direction: ltr;
}

.menu-item {
  padding: 8px 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  background: white;
  gap: 10px;
  font-size: 13px;
  color: #333;
  border-bottom: 1px solid #f0f3f5;
}

.menu-item--active {
  background: #f5f8fa;
}

.custom-checkbox {
  width: 16px;
  height: 16px;
  border: 1px solid #aab4bb;
  border-radius: 3px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  background: white;
}

.checkbox-indicator {
  pointer-events: none;
}

.checkbox-indicator--full {
  width: 10px;
  height: 10px;
  position: relative;
}

.checkmark {
  position: absolute;
  left: -1px;
  top: 2px;
  width: 12px;
  height: 6px;
  border-left: 2px solid #0095db;
  border-bottom: 2px solid #0095db;
  transform: rotate(-45deg);
}

.checkbox-indicator--partial {
  width: 10px;
  height: 2px;
  background-color: #0095db;
}

.item-label {
  pointer-events: none;
  flex: 1;
}

.arrow-icon {
  color: #8895a0;
  font-size: 10px;
  pointer-events: none;
  transform: scaleY(1.5);
}

/* Scrollbar styles */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f5f8fa;
}

::-webkit-scrollbar-thumb {
  background: #cdd4d9;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #aab4bb;
}
</style>