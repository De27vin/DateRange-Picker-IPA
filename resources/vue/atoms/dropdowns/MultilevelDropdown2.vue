<!-- MultiLevelDropdown.vue -->
<template>
  <div class="multilevel-dropdown">
    <!-- Main dropdown trigger -->
    <div class="dropdown-trigger" @click="isOpen = !isOpen">
      <div class="selected-items">
        <template v-if="selectedItems.length">
          <span
            v-for="item in selectedItems"
            :key="item.id"
            class="selected-tag"
          >
            {{ item.label }}
            <button @click.stop="removeItem(item)" class="remove-btn">&times;</button>
          </span>
        </template>
        <span v-else class="placeholder">Select items...</span>
      </div>
      <span class="arrow" :class="{ 'open': isOpen }">▼</span>
    </div>

    <!-- Dropdown menu -->
    <div v-if="isOpen" class="dropdown-menu">
      <div class="menu-wrapper">
        <template v-for="(level, levelIndex) in menuLevels">
          <div
            :key="levelIndex"
            class="menu-level"
            :style="{ left: `${levelIndex * 200}px` }"
          >
            <div
              v-for="item in level"
              :key="item.id"
              class="menu-item"
              :class="{
                'active': isItemActive(item),
                'selected': isSelected(item)
              }"
              @click.stop="handleItemClick(item, levelIndex)"
            >
              <div class="item-content">
                <input
                  type="checkbox"
                  :checked="isSelected(item)"
                  @click.stop
                  @change="toggleSelection(item)"
                >
                <span class="item-label">{{ item.label }}</span>
                <span
                  v-if="hasChildren(item)"
                  class="submenu-indicator"
                >▶</span>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MultiLevelDropdown',
  props: {
    options: {
      type: Array,
      required: true
    },
    value: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      isOpen: false,
      selectedItems: this.value,
      menuLevels: [this.options], // Initialize with root level
      activeItems: [], // Track active items for each level
    }
  },
  watch: {
    value: {
      handler(newValue) {
        this.selectedItems = newValue
      },
      deep: true
    }
  },
  methods: {
    hasChildren(item) {
      return item.children && item.children.length > 0
    },
    isSelected(item) {
      return this.selectedItems.some(selected => selected.id === item.id)
    },
    isItemActive(item) {
      return this.activeItems.some(active => active.id === item.id)
    },
    handleItemClick(item, levelIndex) {
      if (this.hasChildren(item)) {
        // Trim menu levels after current level
        this.menuLevels.splice(levelIndex + 1)
        // Add new level
        this.menuLevels.push(item.children)

        // Update active items
        this.activeItems = this.activeItems.slice(0, levelIndex)
        this.activeItems.push(item)
      }
    },
    toggleSelection(item) {
      const index = this.selectedItems.findIndex(selected => selected.id === item.id)
      if (index === -1) {
        this.selectedItems.push(item)
      } else {
        this.selectedItems.splice(index, 1)
      }
      this.$emit('input', this.selectedItems)
    },
    removeItem(item) {
      const index = this.selectedItems.findIndex(selected => selected.id === item.id)
      if (index !== -1) {
        this.selectedItems.splice(index, 1)
        this.$emit('input', this.selectedItems)
      }
    },
    // Reset menu state when closing
    closeMenu() {
      this.isOpen = false
      this.menuLevels = [this.options]
      this.activeItems = []
    }
  }
}
</script>

<style scoped>
  .multilevel-dropdown {
    position: relative;
    width: 200px;
  }

  .dropdown-trigger {
    border: 1px solid #ddd;
    padding: 8px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .selected-items {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    min-height: 24px;
  }

  .selected-tag {
    background-color: #e9ecef;
    border-radius: 4px;
    padding: 2px 8px;
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.875rem;
  }

  .remove-btn {
    background: none;
    border: none;
    padding: 0 4px;
    cursor: pointer;
    font-size: 14px;
    color: #666;
  }

  .remove-btn:hover {
    color: #333;
  }

  .arrow {
    transition: transform 0.2s;
  }

  .arrow.open {
    transform: rotate(180deg);
  }

  .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 4px;
    z-index: 1000;
  }

  .menu-wrapper {
    display: flex;
    position: relative;
  }

  .menu-level {
    position: absolute;
    top: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    width: 200px;
    max-height: 300px;
    overflow-y: auto;
  }

  .menu-item {
    padding: 8px;
    cursor: pointer;
  }

  .menu-item:hover {
    background-color: #f8f9fa;
  }

  .menu-item.active {
    background-color: #e9ecef;
  }

  .item-content {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .item-label {
    flex: 1;
    font-size: 0.875rem;
  }

  .submenu-indicator {
    font-size: 12px;
    color: #666;
  }

  .placeholder {
    color: #6c757d;
    font-size: 0.875rem;
  }

  /* Animation classes */
  .menu-level {
    transition: left 0.3s ease;
  }
</style>