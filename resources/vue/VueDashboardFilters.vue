<template>
  <vue-filters :show-menu="true" :show-create-site="false" :filters-id="'Dashboard'" :counter-icon="'tag'" />
</template>

<script>
import VueFilters from './VueFilters.vue'

export default {
  name: 'VueDashboardFilters',
  components: { VueFilters },
}
</script> 