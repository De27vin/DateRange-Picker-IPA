<template>
  <div class="multilevel-dropdown">

    <!-- Main dropdown trigger -->
    <div class="dropdown-trigger" @click="isOpen = !isOpen">
      <div class="selected-items">
        <template v-if="selectedItems.length">
          <span v-for="item in selectedItems" :key="item.id" class="selected-tag">
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
      <recursive-menu
        :items="options"
        :selected-items="selectedItems"
        @select="handleSelect"
        @toggle-submenu="handleToggleSubmenu"
      />
    </div>

  </div>
</template>



<script>
const RecursiveMenu = {
  name: 'RecursiveMenu',

  props: {
    items: {
      type: Array,
      required: true
    },

    selectedItems: {
      type: Array,
      default: () => []
    },

    level: {
      type: Number,
      default: 0
    }
  },

  data() {
    return {
      openSubmenus: {}
    }
  },

  methods: {
    isSelected(item) {
      return this.selectedItems.some(selected => selected.id === item.id)
    },
    toggleSubmenu(itemId) {
      this.$set(this.openSubmenus, itemId, !this.openSubmenus[itemId])
      this.$emit('toggle-submenu', itemId)
    },
    handleSelect(item) {
      this.$emit('select', item)
    }
  },

  template: `
    <ul class="menu-level" :style="{ marginLeft: level * 20 + 'px' }">
      <li v-for="item in items" :key="item.id" class="menu-item">
        <div class="item-content">
          <input
            type="checkbox"
            :checked="isSelected(item)"
            @change="handleSelect(item)"
          >
          <span class="item-label">{{ item.label }}</span>
          <button
            v-if="item.children && item.children.length"
            @click.stop="toggleSubmenu(item.id)"
            class="submenu-toggle"
          >
            {{ openSubmenus[item.id] ? '▼' : '▶' }}
          </button>
        </div>

        <recursive-menu
          v-if="item.children && item.children.length && openSubmenus[item.id]"
          :items="item.children"
          :selected-items="selectedItems"
          :level="level + 1"
          @select="handleSelect"
          @toggle-submenu="$emit('toggle-submenu', $event)"
        />
      </li>
    </ul>
  `
}

export default {

  name: 'MultiLevelDropdown',

  components: {
    RecursiveMenu
  },

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
      selectedItems: this.value
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

    handleSelect(item) {
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

    handleToggleSubmenu() {
      // Handle any additional logic needed when submenus are toggled
    }
  }
}
</script>

<style lang="scss" scoped>
  .multilevel-dropdown {
    position: relative;
    width: 300px;
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
  }

  .selected-tag {
    background-color: #e9ecef;
    border-radius: 4px;
    padding: 2px 8px;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .remove-btn {
    background: none;
    border: none;
    padding: 0 4px;
    cursor: pointer;
    font-size: 14px;
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
    width: 100%;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-top: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
  }

  .menu-level {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .menu-item {
    padding: 4px 8px;
  }

  .item-content {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .submenu-toggle {
    background: none;
    border: none;
    padding: 0 4px;
    cursor: pointer;
    font-size: 12px;
  }

  .placeholder {
    color: #6c757d;
  }
</style>