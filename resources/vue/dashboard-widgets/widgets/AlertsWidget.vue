<template>
  <WidgetCard title="Alerts" subtitle="Last datapoint per bucket." configurable @toggle-settings="settingsOpen = !settingsOpen">
    <template v-if="settingsOpen" #settings>
      <DateRangeSettings :value="range" @apply="applyRange" @cancel="settingsOpen = false" />
    </template>

    <div class="widget-layout">
      <div class="compact-widget__top">
        <div class="chart-wrap compact-widget__preview">
          <svg viewBox="0 0 180 94" class="bar-chart">
            <line x1="10" y1="72" x2="170" y2="72" class="bar-chart__axis" />

            <g v-for="(point, index) in normalizedSeries" :key="index">
              <rect
                :x="point.bar.x"
                :y="point.nonCriticalRect.y"
                :width="point.bar.width"
                :height="point.nonCriticalRect.height"
                rx="4"
                fill="#facc15"
              />
              <rect
                :x="point.bar.x"
                :y="point.criticalRect.y"
                :width="point.bar.width"
                :height="point.criticalRect.height"
                rx="4"
                fill="#dc2626"
              />
            </g>
          </svg>
          <div class="mini-labels" :style="labelGridStyle">
            <span v-for="(point, index) in normalizedSeries" :key="`label-${index}`">{{ point.label }}</span>
          </div>
        </div>
      </div>

      <div class="compact-metrics">
        <div class="compact-metrics__labels">
          <div>Critical</div>
          <div>Non-critical</div>
        </div>
        <div class="compact-metrics__values">
          <div>{{ summary.critical }}</div>
          <div>{{ summary.non_critical }}</div>
        </div>
      </div>
    </div>
  </WidgetCard>
</template>

<script>
import WidgetCard from '../components/WidgetCard.vue'
import DateRangeSettings from '../components/DateRangeSettings.vue'

export default {
  name: 'AlertsWidget',
  components: {
    WidgetCard,
    DateRangeSettings,
  },
  props: {
    summary: {
      type: Object,
      required: true,
    },
    series: {
      type: Array,
      required: true,
    },
    range: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      settingsOpen: false,
    }
  },
  computed: {
    normalizedSeries() {
      const chartLeft = 14
      const chartWidth = 152
      const bucketCount = Math.max(this.series.length, 1)
      const slotWidth = chartWidth / bucketCount
      const maxValue = Math.max(1, ...this.series.map((point) =>
        Number(point?.series?.critical || 0) + Number(point?.series?.non_critical || 0)
      ))

      return this.series.map((point, index) => {
        const centerX = chartLeft + (slotWidth * index) + (slotWidth / 2)
        const barWidth = Math.min(16, Math.max(10, slotWidth * 0.44))
        const critical = Number(point?.series?.critical || 0)
        const nonCritical = Number(point?.series?.non_critical || 0)
        const totalHeight = ((critical + nonCritical) / maxValue) * 54
        const criticalHeight = totalHeight * (critical / Math.max(critical + nonCritical, 1))
        const nonCriticalHeight = Math.max(0, totalHeight - criticalHeight)
        const topY = 70 - totalHeight

        return {
          centerX,
          label: this.formatLabel(point?.label_ts || point?.bucket_end),
          bar: {
            x: centerX - (barWidth / 2),
            width: barWidth,
          },
          criticalRect: {
            y: topY,
            height: criticalHeight,
          },
          nonCriticalRect: {
            y: topY + criticalHeight,
            height: nonCriticalHeight,
          },
        }
      })
    },
    labelGridStyle() {
      return {
        gridTemplateColumns: `repeat(${Math.max(this.normalizedSeries.length, 1)}, minmax(0, 1fr))`,
      }
    },
  },
  methods: {
    applyRange(nextRange) {
      this.settingsOpen = false
      this.$emit('update-range', nextRange)
    },
    formatLabel(ts) {
      const date = new Date(ts)
      const options = this.series.length <= 3
        ? { month: 'short', year: '2-digit' }
        : { day: '2-digit', month: 'short' }

      return new Intl.DateTimeFormat(undefined, options).format(date)
    },
  },
}
</script>

<style scoped>
.widget-layout {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  height: 100%;
}

.compact-widget__top {
  display: flex;
  justify-content: flex-end;
}

.compact-widget__preview {
  width: 11rem;
}

.bar-chart {
  width: 100%;
  height: 4.9rem;
}

.bar-chart__axis {
  stroke: rgba(148, 163, 184, 0.45);
  stroke-width: 1.5;
}

.mini-labels {
  display: grid;
  gap: 0.2rem;
  margin-top: 0.1rem;
  color: #64748b;
  font-size: 0.63rem;
  text-align: center;
  font-weight: 600;
}

.compact-metrics {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 0.8rem;
  margin-top: auto;
}

.compact-metrics__labels,
.compact-metrics__values {
  display: grid;
  gap: 0.35rem;
  font-size: 0.9rem;
}

.compact-metrics__labels {
  color: #5f7084;
}

.compact-metrics__values {
  text-align: right;
  color: #12243d;
  font-weight: 800;
}
</style>
