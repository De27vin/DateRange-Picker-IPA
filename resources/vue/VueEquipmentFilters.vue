<template>
  <vue-filters :show-menu="true" :show-create-site="true" :filters-id="'Equipment'" :counter-icon="'building'" />
</template>

<script>
import VueFilters from './VueFilters.vue'

export default {
  name: 'VueDashboardFilters',
  components: { VueFilters },
}
</script>