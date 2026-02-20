<template>
  <div>
    <!-- Basic usage with default icon -->
    <SearchCounter />
    
    <!-- Usage with custom icon -->
    <SearchCounter icon="house" />
    
    <!-- Another example with different icon -->
    <SearchCounter icon="person_2" />
  </div>
</template>

<script>
import SearchCounter from './SearchCounter.vue'

export default {
  name: 'SearchCounterUsageExample',
  components: {
    SearchCounter
  },
  mounted() {
    // Example of triggering the counter events
    setTimeout(() => {
      // Trigger loading state
      window.dispatchEvent(new CustomEvent('total_count_load'))
      
      // Update the count after 2 seconds
      setTimeout(() => {
        window.dispatchEvent(new CustomEvent('total_count_updated', {
          detail: { total: 42 }
        }))
      }, 2000)
    }, 1000)
  }
}
</script>