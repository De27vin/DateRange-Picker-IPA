<template>
  <WidgetCard title="Overdues" subtitle="Last datapoint with projection." configurable :settings-error="errorMessage" @toggle-settings="settingsOpen = !settingsOpen">
    <template v-if="settingsOpen" #settings>
      <DateRangeSettings :value="range" :default-value="defaultRange" @apply="applyRange" @reset="resetRange" @cancel="settingsOpen = false" />
    </template>

    <div class="widget-layout">
      <div class="compact-widget__top">
        <div ref="preview" class="trend-preview">
          <div class="trend-preview__row">
            <svg viewBox="0 0 180 50" class="trend-chart">
              <line x1="24" y1="36" x2="172" y2="36" class="trend-chart__axis" />
              <line x1="24" y1="8" x2="24" y2="36" class="trend-chart__axis" />
              <text x="2" y="10" class="trend-chart__y-label">{{ periodic.max }}</text>
              <text x="2" y="23" class="trend-chart__y-label">{{ periodic.mid }}</text>
              <text x="8" y="38" class="trend-chart__y-label">0</text>
              <polyline :points="periodic.points" fill="none" stroke="#355c8c" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
              <g v-for="(point, index) in periodic.pointItems" :key="`periodic-${index}`">
                <circle
                  :cx="point.x"
                  :cy="point.y"
                  r="4.2"
                  fill="#355c8c"
                  @mouseenter="showTooltip($event, point.tooltip)"
                  @mousemove="moveTooltip($event)"
                  @mouseleave="hideTooltip"
                />
                <circle
                  :cx="point.x"
                  :cy="point.y"
                  r="8"
                  fill="transparent"
                  @mouseenter="showTooltip($event, point.tooltip)"
                  @mousemove="moveTooltip($event)"
                  @mouseleave="hideTooltip"
                />
              </g>
              <polyline :points="periodic.projection" fill="none" stroke="#355c8c" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 4" />
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
              <g v-for="(point, index) in local.pointItems" :key="`local-${index}`">
                <circle
                  :cx="point.x"
                  :cy="point.y"
                  r="4.2"
                  fill="#4b78a8"
                  @mouseenter="showTooltip($event, point.tooltip)"
                  @mousemove="moveTooltip($event)"
                  @mouseleave="hideTooltip"
                />
                <circle
                  :cx="point.x"
                  :cy="point.y"
                  r="8"
                  fill="transparent"
                  @mouseenter="showTooltip($event, point.tooltip)"
                  @mousemove="moveTooltip($event)"
                  @mouseleave="hideTooltip"
                />
              </g>
              <polyline :points="local.projection" fill="none" stroke="#4b78a8" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 4" />
            </svg>
          </div>
          <div
            v-if="tooltip.visible"
            class="chart-tooltip"
            :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
          >{{ tooltip.text }}</div>
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
    defaultRange: {
      type: Object,
      required: true,
    },
    errorMessage: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      settingsOpen: false,
      tooltip: {
        visible: false,
        text: '',
        x: 0,
        y: 0,
      },
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
    resetRange() {
      this.settingsOpen = false
      this.$emit('reset-range')
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
        const sourcePoint = this.series[index] || {}
        const x = left + (step * index)
        const y = baseY - ((value / maxValue) * height)
        const ts = sourcePoint?.point_ts || sourcePoint?.label_ts || sourcePoint?.bucket_end
        return {
          x,
          y,
          value,
          tooltip: this.buildTooltip(this.labelForKey(key), value, ts),
        }
      })

      const last = points[points.length - 1] || { x: left, y: baseY, value: 0 }
      const previous = points[points.length - 2] || last
      const projectedValue = Math.max(0, Math.round(last.value + (last.value - previous.value)))
      const projectedY = baseY - ((projectedValue / maxValue) * height)
      const projectedX = Math.min(402, last.x + step)

      return {
        points: points.map((point) => `${point.x},${point.y}`).join(' '),
        pointItems: points,
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
    formatTooltip(ts) {
      const date = new Date(ts)
      const day = String(date.getUTCDate()).padStart(2, '0')
      const month = String(date.getUTCMonth() + 1).padStart(2, '0')
      const hours = String(date.getUTCHours()).padStart(2, '0')
      const minutes = String(date.getUTCMinutes()).padStart(2, '0')
      return `${day}.${month} ${hours}:${minutes} UTC`
    },
    buildTooltip(label, value, ts) {
      return `${label}: ${value}\n${this.formatTooltip(ts)}`
    },
    labelForKey(key) {
      return key === 'periodical_calls' ? 'Periodic calls' : 'Local checks'
    },
    showTooltip(event, text) {
      this.tooltip.visible = true
      this.tooltip.text = text
      this.moveTooltip(event)
    },
    moveTooltip(event) {
      const preview = this.$refs.preview
      if (!preview || !event) {
        return
      }

      const rect = preview.getBoundingClientRect()
      this.tooltip.x = event.clientX - rect.left + 10
      this.tooltip.y = event.clientY - rect.top - 10
    },
    hideTooltip() {
      this.tooltip.visible = false
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
  position: relative;
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

.chart-tooltip {
  position: absolute;
  z-index: 5;
  max-width: 10rem;
  padding: 0.35rem 0.5rem;
  border-radius: 0.55rem;
  background: rgba(15, 23, 42, 0.94);
  color: #ffffff;
  font-size: 0.68rem;
  line-height: 1.25;
  pointer-events: none;
  transform: translate(-50%, -100%);
  white-space: pre-line;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.24);
}
</style>
