<template>
  <vue-filters :show-menu="true" :show-create-site="true" :filters-id="'Equipment'" :counter-icon="'building'" :can-import="canImport" />
</template>

<script>
import VueFilters from './VueFilters.vue'

export default {
  name: 'VueDashboardFilters',
  components: { VueFilters },
  props: {
    canImport: {
      type: Boolean,
      default: false
    }
  }
}
</script>