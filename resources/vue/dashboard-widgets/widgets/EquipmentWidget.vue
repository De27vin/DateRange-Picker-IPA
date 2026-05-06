<template>
  <WidgetCard title="Equipment" subtitle="Last datapoint per bucket.">
    <div class="widget-layout compact-widget">
      <div class="compact-widget__top">
        <div ref="preview" class="chart-wrap compact-widget__preview">
          <svg viewBox="0 0 180 94" class="bar-chart">
            <line x1="24" y1="72" x2="170" y2="72" class="bar-chart__axis" />
            <line x1="24" y1="10" x2="24" y2="72" class="bar-chart__axis" />

            <text x="2" y="14" class="bar-chart__y-label">{{ yAxis.max }}</text>
            <text x="2" y="44" class="bar-chart__y-label">{{ yAxis.mid }}</text>
            <text x="8" y="74" class="bar-chart__y-label">0</text>

            <g v-for="(point, index) in normalizedSeries" :key="index">
              <rect
                :x="point.activeBar.x"
                :y="point.activeBar.y"
                :width="point.activeBar.width"
                :height="point.activeBar.height"
                rx="4"
                fill="#3b82f6"
                @mouseenter="showTooltip($event, point.activeBar.tooltip)"
                @mousemove="moveTooltip($event)"
                @mouseleave="hideTooltip"
              >
              </rect>
              <rect
                :x="point.inactiveBar.x"
                :y="point.inactiveBar.y"
                :width="point.inactiveBar.width"
                :height="point.inactiveBar.height"
                rx="4"
                fill="#94a3b8"
                @mouseenter="showTooltip($event, point.inactiveBar.tooltip)"
                @mousemove="moveTooltip($event)"
                @mouseleave="hideTooltip"
              >
              </rect>
            </g>
          </svg>
          <div
            v-if="tooltip.visible"
            class="chart-tooltip"
            :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
          >{{ tooltip.text }}</div>
          <div class="mini-labels" :style="labelGridStyle">
            <span v-for="(point, index) in normalizedSeries" :key="`label-${index}`">{{ point.label }}</span>
          </div>
        </div>
      </div>

      <div class="compact-metrics">
        <div class="compact-metrics__labels">
          <div>Active</div>
          <div>Inactive</div>
        </div>
        <div class="compact-metrics__values">
          <div>{{ summary.active }}</div>
          <div>{{ summary.inactive }}</div>
        </div>
      </div>
    </div>
  </WidgetCard>
</template>

<script>
import WidgetCard from '../components/WidgetCard.vue'

export default {
  name: 'EquipmentWidget',
  components: {
    WidgetCard,
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
  },
  data() {
    return {
      tooltip: {
        visible: false,
        text: '',
        x: 0,
        y: 0,
      },
    }
  },
  computed: {
    normalizedSeries() {
      const chartLeft = 30
      const chartWidth = 136
      const bucketCount = Math.max(this.series.length, 1)
      const slotWidth = chartWidth / bucketCount
      const maxValue = Math.max(1, ...this.series.reduce((values, point) => {
        values.push(Number(point?.series?.enabled || 0))
        values.push(Number(point?.series?.disabled || 0))
        return values
      }, []))

      return this.series.map((point, index) => {
        const centerX = chartLeft + (slotWidth * index) + (slotWidth / 2)
        const barWidth = Math.min(10, Math.max(6, slotWidth * 0.22))
        const gap = Math.max(3, slotWidth * 0.06)
        const enabled = Number(point?.series?.enabled || 0)
        const disabled = Number(point?.series?.disabled || 0)
        const activeHeight = (enabled / maxValue) * 54
        const inactiveHeight = (disabled / maxValue) * 54
        const pointTs = point?.point_ts || point?.label_ts || point?.bucket_end

        return {
          centerX,
          label: this.formatLabel(point?.label_ts || point?.bucket_end),
          activeBar: {
            x: centerX - gap - barWidth,
            y: 70 - activeHeight,
            width: barWidth,
            height: activeHeight,
            tooltip: this.buildTooltip('Active', enabled, pointTs),
          },
          inactiveBar: {
            x: centerX + gap,
            y: 70 - inactiveHeight,
            width: barWidth,
            height: inactiveHeight,
            tooltip: this.buildTooltip('Inactive', disabled, pointTs),
          },
        }
      })
    },
    yAxis() {
      const values = this.series.reduce((out, point) => {
        out.push(Number(point?.series?.enabled || 0))
        out.push(Number(point?.series?.disabled || 0))
        return out
      }, [])
      const max = Math.max(1, ...values)

      return {
        max,
        mid: Math.round(max / 2),
      }
    },
    labelGridStyle() {
      return {
        gridTemplateColumns: `repeat(${Math.max(this.normalizedSeries.length, 1)}, minmax(0, 1fr))`,
      }
    },
  },
  methods: {
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
  position: relative;
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

.bar-chart__y-label {
  fill: #7b8ca1;
  font-size: 13px;
  font-weight: 700;
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
