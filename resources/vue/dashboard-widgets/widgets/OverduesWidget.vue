<template>
  <WidgetCard title="Overdues" subtitle="Last datapoint with projection." configurable @toggle-settings="settingsOpen = !settingsOpen">
    <template v-if="settingsOpen" #settings>
      <DateRangeSettings :value="range" @apply="applyRange" @cancel="settingsOpen = false" />
    </template>

    <div class="widget-layout">
      <div class="compact-widget__top">
        <div class="trend-preview">
          <div class="trend-preview__row">
            <svg viewBox="0 0 180 50" class="trend-chart">
              <line x1="24" y1="36" x2="172" y2="36" class="trend-chart__axis" />
              <line x1="24" y1="8" x2="24" y2="36" class="trend-chart__axis" />
              <text x="2" y="10" class="trend-chart__y-label">{{ periodic.max }}</text>
              <text x="2" y="23" class="trend-chart__y-label">{{ periodic.mid }}</text>
              <text x="8" y="38" class="trend-chart__y-label">0</text>
              <polyline :points="periodic.points" fill="none" stroke="#355c8c" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
              <polyline :points="periodic.projection" fill="none" stroke="#355c8c" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 4" />
              <circle :cx="periodic.currentPoint.x" :cy="periodic.currentPoint.y" r="2.8" fill="#355c8c" />
            </svg>
          </div>
          <div class="trend-preview__row">
            <svg viewBox="0 0 180 50" class="trend-chart">
              <line x1="24" y1="36" x2="172" y2="36" class="trend-chart__axis" />
              <line x1="24" y1="8" x2="24" y2="36" class="trend-chart__axis" />
              <text x="2" y="10" class="trend-chart__y-label">{{ local.max }}</text>
              <text x="2" y="23" class="trend-chart__y-label">{{ local.mid }}</text>
              <text x="8" y="38" class="trend-chart__y-label">0</text>
              <polyline :points="local.points" fill="none" stroke="#4b78a8" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
              <polyline :points="local.projection" fill="none" stroke="#4b78a8" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 4" />
              <circle :cx="local.currentPoint.x" :cy="local.currentPoint.y" r="2.8" fill="#4b78a8" />
            </svg>
          </div>
          <div class="mini-labels" :style="labelGridStyle(periodic.labels)">
            <span v-for="(label, index) in periodic.labels" :key="index">{{ label }}</span>
          </div>
        </div>
      </div>

      <div class="compact-metrics">
        <div class="compact-metrics__labels">
          <div>Periodic calls</div>
          <div>Local checks</div>
        </div>
        <div class="compact-metrics__values">
          <div>{{ summary.periodic_calls }}</div>
          <div>{{ summary.local_checks }}</div>
        </div>
      </div>
    </div>
  </WidgetCard>
</template>

<script>
import WidgetCard from '../components/WidgetCard.vue'
import DateRangeSettings from '../components/DateRangeSettings.vue'

export default {
  name: 'OverduesWidget',
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
    periodic() {
      return this.buildTrend('periodical_calls')
    },
    local() {
      return this.buildTrend('local_checks')
    },
  },
  methods: {
    applyRange(nextRange) {
      this.settingsOpen = false
      this.$emit('update-range', nextRange)
    },
    buildTrend(key) {
      const values = this.series.map((point) => Number(point?.series?.[key] || 0))
      const maxValue = Math.max(1, ...values)
      const left = 24
      const right = 172
      const baseY = 36
      const height = 24
      const step = this.series.length > 1 ? ((right - left) / Math.max(this.series.length - 1, 1)) : (right - left)
      const points = values.map((value, index) => {
        const x = left + (step * index)
        const y = baseY - ((value / maxValue) * height)
        return { x, y, value }
      })

      const last = points[points.length - 1] || { x: left, y: baseY, value: 0 }
      const previous = points[points.length - 2] || last
      const projectedValue = Math.max(0, Math.round(last.value + (last.value - previous.value)))
      const projectedY = baseY - ((projectedValue / maxValue) * height)
      const projectedX = Math.min(402, last.x + step)

      return {
        points: points.map((point) => `${point.x},${point.y}`).join(' '),
        projection: `${last.x},${last.y} ${projectedX},${projectedY}`,
        currentPoint: last,
        max: maxValue,
        mid: Math.round(maxValue / 2),
        labels: this.series.map((point) => this.formatLabel(point?.label_ts || point?.bucket_end)),
      }
    },
    formatLabel(ts) {
      const date = new Date(ts)
      const day = String(date.getUTCDate()).padStart(2, '0')
      const month = String(date.getUTCMonth() + 1).padStart(2, '0')
      return `${day}.${month}`
    },
    labelGridStyle(labels) {
      return {
        gridTemplateColumns: `repeat(${Math.max(labels.length, 1)}, minmax(0, 1fr))`,
      }
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

.trend-preview {
  width: 11rem;
}

.trend-preview__row + .trend-preview__row {
  margin-top: 0.2rem;
}

.trend-chart {
  width: 100%;
  height: 2.35rem;
}

.trend-chart__axis {
  stroke: rgba(148, 163, 184, 0.34);
  stroke-width: 1.4;
}

.trend-chart__y-label {
  fill: #7b8ca1;
  font-size: 13px;
  font-weight: 700;
}

.mini-labels {
  display: grid;
  gap: 0.2rem;
  margin-top: 0.18rem;
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
