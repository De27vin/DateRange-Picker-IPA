<template>
  <div>
    <div v-if="loading" class="flex items-center uppercase text-sm" style="font-weight: bold;">
      <!-- Single Pulsating Circle -->
      <i class="f7-icons text-lg ml-4">{{ icon }}</i>
      <div class="v-spinner-custom" style="padding-left: 0.5rem;">
        <div class="v-pulse-custom" :style="spinnerStyle"></div>
      </div>
    </div>
    <div v-else class="flex items-center uppercase text-sm" style="font-weight: bold;">
      <span class="tt cursor-default">
        <div class="tts flex items-center">
          <i class="f7-icons text-lg ml-4">{{ icon }}</i>
          <span class="" style="padding-left: 0.5rem;">{{ total }}</span>
        </div>
        <span class="ttt elip ttt-r bg-white border border-slate-300 text-dark shadow-md text-sm" style="font-weight: normal;">
          {{ __('Results count') }}
        </span>
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SearchCounter',
  props: {
    icon: {
      type: String,
      default: 'building'
    }
  },
  data() {
    return {
      total: null,
      loading: true,
      spinnerStyle: {
        backgroundColor: 'lightblue',
        width: '10px',
        height: '10px',
        margin: 'auto',
        borderRadius: '100%',
        display: 'inline-block',
        animationName: 'v-pulseStretch-custom',
        animationDuration: '1s',
        animationIterationCount: 'infinite',
        animationTimingFunction: 'ease-in-out',
        animationFillMode: 'both'
      }
    }
  },
  mounted() {
    this.initialize()
  },
  beforeUnmount() {
    window.removeEventListener('total_count_load', this.handleTotalCountLoad)
    window.removeEventListener('total_count_updated', this.handleTotalCountUpdated)
  },
  methods: {
    initialize() {
      if (window.latestTotalCount !== null && window.latestTotalCount !== undefined) {
        this.total = window.latestTotalCount
        this.loading = false
      }

      window.addEventListener('total_count_load', this.handleTotalCountLoad)
      window.addEventListener('total_count_updated', this.handleTotalCountUpdated)
    },
    handleTotalCountLoad() {
      this.loading = true
    },
    handleTotalCountUpdated(event) {
      this.total = event.detail.total
      this.loading = false
    },
    __(key) {
      // Translation helper - matches Laravel's __() function
      // You might want to use vue-i18n or your preferred translation library
      if (typeof window.__ === 'function') {
        return window.__(key)
      }
      return key
    }
  }
}
</script>

<style scoped>
.v-spinner-custom {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 10px auto;
  height: 10px; /* Add a height to ensure it's visible */
}

.v-pulse-custom {
  background-color: lightblue;
  width: 10px;
  height: 10px;
  border-radius: 100%;
  display: inline-block;
  animation: v-pulseStretch-custom 1s infinite ease-in-out;
  -webkit-animation: v-pulseStretch-custom 1s infinite ease-in-out;
}

@keyframes v-pulseStretch-custom {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(0.5);
    opacity: 0.5;
  }
}
</style>

<style>
/* 
  Note: The following styles are global tooltip styles that should be in your main CSS file.
  If they're not already included, add them to your global styles:
*/

/* tooltipped element */
.tt {
  position: relative;
  display: inline-block;
}

/* tooltip text */
.ttt {
  opacity: 0;
  text-align: center;
  padding: 5px 0;
  border-radius: 6px;
  padding-inline: 1rem;
  position: absolute;
  z-index: 1;
}

.elip {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Icon hover to show tooltip */
.tt .tts:hover + .ttt {
  opacity: 1;
  transition: opacity 0.1s ease-in-out;
}

/* Hide tooltip when hovering over tooltip text */
.tt .ttt:hover {
  opacity: 0;
}

/* tooltip right */
.ttt-r {
  top: -5px;
  left: 105%;
}
</style>