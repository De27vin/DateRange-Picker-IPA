<template>
  <div class="dashboard-widgets">
    <div class="dashboard-widgets__grid">
      <section v-for="widget in widgetViews" :key="widget.key" class="widget-card">
        <header class="widget-card__header">
          <div>
            <h3 class="widget-card__title">{{ widget.title }}</h3>
            <p v-if="widget.subtitle" class="widget-card__subtitle">{{ widget.subtitle }}</p>
          </div>
        </header>

        <div class="widget-card__body">
          <div class="widget-layout">
            <div class="compact-widget__top">
              <div
                v-if="widget.chart.type === 'bars'"
                class="chart-wrap compact-widget__preview js-widget-preview"
              >
                <svg viewBox="0 0 180 94" class="bar-chart">
                  <line x1="24" y1="72" x2="170" y2="72" class="bar-chart__axis" />
                  <line x1="24" y1="10" x2="24" y2="72" class="bar-chart__axis" />

                  <text x="2" y="14" class="bar-chart__y-label">{{ widget.chart.yAxis.max }}</text>
                  <text x="2" y="44" class="bar-chart__y-label">{{ widget.chart.yAxis.mid }}</text>
                  <text x="8" y="74" class="bar-chart__y-label">0</text>

                  <g v-for="(point, pointIndex) in widget.chart.points" :key="pointIndex">
                    <rect
                      v-for="bar in point.bars"
                      :key="bar.key"
                      :x="bar.x"
                      :y="bar.y"
                      :width="bar.width"
                      :height="bar.height"
                      rx="4"
                      :fill="bar.color"
                      @mouseenter="showTooltip($event, bar.tooltip, widget.key)"
                      @mousemove="moveTooltip($event)"
                      @mouseleave="hideTooltip"
                    />
                  </g>
                </svg>

                <div
                  v-if="tooltip.visible && tooltip.owner === widget.key"
                  class="chart-tooltip"
                  :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
                >{{ tooltip.text }}</div>

                <div class="mini-labels" :style="widget.chart.labelGridStyle">
                  <span v-for="(label, labelIndex) in widget.chart.labels" :key="labelIndex">{{ label }}</span>
                </div>
              </div>

              <div
                v-else-if="widget.chart.type === 'trend'"
                class="trend-preview js-widget-preview"
              >
                <div v-for="line in widget.chart.lines" :key="line.key" class="trend-preview__row">
                  <svg viewBox="0 0 180 50" class="trend-chart">
                    <line x1="24" y1="36" x2="172" y2="36" class="trend-chart__axis" />
                    <line x1="24" y1="8" x2="24" y2="36" class="trend-chart__axis" />
                    <text x="2" y="10" class="trend-chart__y-label">{{ line.max }}</text>
                    <text x="2" y="23" class="trend-chart__y-label">{{ line.mid }}</text>
                    <text x="8" y="38" class="trend-chart__y-label">0</text>
                    <polyline
                      :points="line.points"
                      fill="none"
                      :stroke="line.color"
                      stroke-width="2.4"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <g v-for="(point, pointIndex) in line.pointItems" :key="pointIndex">
                      <circle
                        :cx="point.x"
                        :cy="point.y"
                        r="4.2"
                        :fill="line.color"
                        @mouseenter="showTooltip($event, point.tooltip, widget.key)"
                        @mousemove="moveTooltip($event)"
                        @mouseleave="hideTooltip"
                      />
                      <circle
                        :cx="point.x"
                        :cy="point.y"
                        r="8"
                        fill="transparent"
                        @mouseenter="showTooltip($event, point.tooltip, widget.key)"
                        @mousemove="moveTooltip($event)"
                        @mouseleave="hideTooltip"
                      />
                    </g>
                    <polyline
                      :points="line.projection"
                      fill="none"
                      :stroke="line.color"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-dasharray="4 4"
                    />
                  </svg>
                </div>

                <div
                  v-if="tooltip.visible && tooltip.owner === widget.key"
                  class="chart-tooltip"
                  :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
                >{{ tooltip.text }}</div>

                <div class="mini-labels" :style="widget.chart.labelGridStyle">
                  <span v-for="(label, labelIndex) in widget.chart.labels" :key="labelIndex">{{ label }}</span>
                </div>
              </div>

              <div
                v-else-if="widget.chart.type === 'gauges'"
                :class="['gauge-preview', { 'gauge-preview--single': widget.chart.single }]"
              >
                <div v-for="gauge in widget.chart.gauges" :key="gauge.key" class="gauge-preview__item">
                  <svg class="dashboard-gauge" viewBox="0 0 200 120" aria-hidden="true">
                    <path
                      v-for="section in gauge.sections"
                      :key="section.key"
                      :d="describeGaugeArc(section, gauge.max)"
                      fill="none"
                      :stroke="section.color"
                      stroke-width="16"
                      stroke-linecap="round"
                    />

                    <path
                      d="M 24 100 A 76 76 0 0 1 176 100"
                      fill="none"
                      stroke="rgba(148, 163, 184, 0.18)"
                      stroke-width="4"
                      stroke-linecap="round"
                    />

                    <line
                      v-if="gauge.showNeedle"
                      x1="100"
                      y1="100"
                      :x2="gauge.needle.x"
                      :y2="gauge.needle.y"
                      stroke="#12243d"
                      stroke-width="4"
                      stroke-linecap="round"
                    />
                    <circle v-if="gauge.showNeedle" cx="100" cy="100" r="6" fill="#12243d" />
                  </svg>
                </div>
              </div>
            </div>

            <div v-if="widget.chart.legend" class="threshold-legend">
              <span v-for="item in widget.chart.legend" :key="item.label">
                <i class="legend-dot" :style="{ background: item.color }"></i>
                {{ item.label }}
              </span>
            </div>

            <div class="compact-metrics">
              <div class="compact-metrics__labels">
                <div v-for="metric in widget.metrics" :key="metric.label">{{ metric.label }}</div>
              </div>
              <div class="compact-metrics__values">
                <div v-for="metric in widget.metrics" :key="metric.label">{{ metric.value }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import {
  resolveRollingRange,
  sanitizeRollingRange,
  SYSTEM_DASHBOARD_WIDGET_DEFAULTS,
} from '../../js/utils/dashboardWidgetSettings'

const DEFAULT_SUMMARY = {
  equipment: { active: 0, inactive: 0 },
  alarms: { inbound_calls: 0, active_alarms: 0 },
  overdues: { periodic_calls: 0, local_checks: 0 },
  alerts: { critical: 0, non_critical: 0 },
  service_level: { automated_checks: 0, physical_checks: 0 },
}

const WIDGETS = [
  {
    key: 'equipment',
    title: 'Equipment',
    subtitle: 'Last datapoint per bucket.',
    summaryKey: 'equipment',
    seriesKey: 'equipment',
    chart: 'groupedBars',
    metrics: [
      { label: 'Active', field: 'active' },
      { label: 'Inactive', field: 'inactive' },
    ],
    fields: [
      { label: 'Active', field: 'enabled', color: '#3b82f6' },
      { label: 'Inactive', field: 'disabled', color: '#94a3b8' },
    ],
  },
  {
    key: 'alarms',
    title: 'Alarms',
    subtitle: 'Current state only.',
    summaryKey: 'alarms',
    chart: 'alarmGauge',
    metrics: [
      { label: 'Inbound calls', field: 'inbound_calls' },
      { label: 'Active alarms', field: 'active_alarms' },
    ],
  },
  {
    key: 'overdues',
    title: 'Overdues',
    subtitle: 'Last datapoint with projection.',
    summaryKey: 'overdues',
    seriesKey: 'overdues',
    chart: 'trend',
    metrics: [
      { label: 'Periodic calls', field: 'periodic_calls' },
      { label: 'Local checks', field: 'local_checks' },
    ],
    fields: [
      { key: 'periodic', label: 'Periodic calls', field: 'periodical_calls', color: '#355c8c' },
      { key: 'local', label: 'Local checks', field: 'local_checks', color: '#4b78a8' },
    ],
  },
  {
    key: 'alerts',
    title: 'Alerts',
    subtitle: 'Last datapoint per bucket.',
    summaryKey: 'alerts',
    seriesKey: 'alerts',
    chart: 'stackedBars',
    metrics: [
      { label: 'Critical', field: 'critical' },
      { label: 'Non-critical', field: 'non_critical' },
    ],
    fields: [
      { label: 'Critical', field: 'critical', color: '#dc2626' },
      { label: 'Non-critical', field: 'non_critical', color: '#facc15' },
    ],
  },
  {
    key: 'service_level',
    title: 'Service Level',
    subtitle: 'Threshold-based interpretation.',
    summaryKey: 'service_level',
    chart: 'serviceGauges',
    metrics: [
      { label: 'Automated checks', field: 'automated_checks', suffix: '%' },
      { label: 'Physical checks', field: 'physical_checks', suffix: '%' },
    ],
  },
]

export default {
  name: 'DashboardWidgetsPage',
  data() {
    const systemDefaults = this.normalizeSettings(SYSTEM_DASHBOARD_WIDGET_DEFAULTS)

    return {
      summary: { ...DEFAULT_SUMMARY },
      series: {
        equipment: [],
        overdues: [],
        alerts: [],
      },
      settings: {
        ...systemDefaults,
      },
      tooltip: {
        visible: false,
        owner: '',
        text: '',
        x: 0,
        y: 0,
      },
      pollHandle: null,
    }
  },
  computed: {
    widgetViews() {
      return WIDGETS.map((widget) => {
        const summary = this.summary[widget.summaryKey] || {}

        return {
          ...widget,
          metrics: widget.metrics.map((metric) => ({
            label: metric.label,
            value: `${this.numberFrom(summary, metric.field)}${metric.suffix || ''}`,
          })),
          chart: this.buildChart(widget, summary),
        }
      })
    },
  },
  async mounted() {
    await this.fetchSettings()

    await Promise.all([
      this.fetchSummary(),
      this.fetchSeries('equipment'),
      this.fetchSeries('overdues'),
      this.fetchSeries('alerts'),
    ])

    this.pollHandle = window.setInterval(() => {
      this.fetchSummary()
    }, 30000)
  },
  beforeDestroy() {
    if (this.pollHandle) {
      window.clearInterval(this.pollHandle)
    }
  },
  methods: {
    async fetchSummary() {
      try {
        const response = await axios.get('/api/dashboard/widgets/summary')
        this.summary = { ...DEFAULT_SUMMARY, ...(response?.data?.data || {}) }
      } catch (error) {
        console.error('Failed to load dashboard summary', error)
      }
    },
    async fetchSettings() {
      try {
        const response = await axios.get('/api/dashboard/widgets/settings')
        const accountDefaults = this.normalizeSettings(response?.data?.data || SYSTEM_DASHBOARD_WIDGET_DEFAULTS)

        this.settings = { ...accountDefaults }
      } catch (error) {
        console.error('Failed to load dashboard widget settings', error)
      }
    },
    normalizeSettings(settings) {
      const ranges = settings?.ranges || {}

      return {
        equipmentRange: sanitizeRollingRange(ranges.equipment, SYSTEM_DASHBOARD_WIDGET_DEFAULTS.ranges.equipment),
        overduesRange: sanitizeRollingRange(ranges.overdues, SYSTEM_DASHBOARD_WIDGET_DEFAULTS.ranges.overdues),
        alertsRange: sanitizeRollingRange(ranges.alerts, SYSTEM_DASHBOARD_WIDGET_DEFAULTS.ranges.alerts),
        serviceThresholds: {
          ...SYSTEM_DASHBOARD_WIDGET_DEFAULTS.serviceThresholds,
          ...(settings?.serviceThresholds || {}),
        },
      }
    },
    async fetchSeries(widget) {
      const rangeMap = {
        equipment: resolveRollingRange(this.settings.equipmentRange),
        overdues: resolveRollingRange(this.settings.overduesRange),
        alerts: resolveRollingRange(this.settings.alertsRange),
      }

      try {
        const response = await axios.get('/api/dashboard/widgets/series', {
          params: {
            widget,
            start: rangeMap[widget].start,
            end: rangeMap[widget].end,
          },
        })

        this.$set(this.series, widget, response?.data?.data || [])
      } catch (error) {
        console.error(`Failed to load ${widget} widget series`, error)
        this.$set(this.series, widget, [])
      }
    },
    buildChart(widget, summary) {
      if (widget.chart === 'groupedBars') {
        return this.buildBarChart(widget, false)
      }

      if (widget.chart === 'stackedBars') {
        return this.buildBarChart(widget, true)
      }

      if (widget.chart === 'trend') {
        return this.buildTrendChart(widget)
      }

      if (widget.chart === 'alarmGauge') {
        return this.buildAlarmGauge(summary)
      }

      return this.buildServiceGauges(summary)
    },
    buildBarChart(widget, stacked) {
      const rows = this.series[widget.seriesKey] || []
      const chartLeft = 30
      const chartWidth = 136
      const bucketCount = Math.max(rows.length, 1)
      const slotWidth = chartWidth / bucketCount
      const maxValue = Math.max(1, ...rows.map((point) => (
        stacked
          ? widget.fields.reduce((sum, field) => sum + this.numberFrom(point?.series, field.field), 0)
          : Math.max(...widget.fields.map((field) => this.numberFrom(point?.series, field.field)))
      )))

      return {
        type: 'bars',
        yAxis: {
          max: maxValue,
          mid: Math.round(maxValue / 2),
        },
        labels: rows.map((point) => this.formatLabel(point?.label_ts || point?.bucket_end)),
        labelGridStyle: this.labelGridStyle(rows.length),
        points: rows.map((point, index) => {
          const centerX = chartLeft + (slotWidth * index) + (slotWidth / 2)
          const pointTs = point?.point_ts || point?.label_ts || point?.bucket_end

          return {
            bars: stacked
              ? this.stackedBars(widget, point, centerX, slotWidth, maxValue, pointTs)
              : this.groupedBars(widget, point, centerX, slotWidth, maxValue, pointTs),
          }
        }),
      }
    },
    groupedBars(widget, point, centerX, slotWidth, maxValue, pointTs) {
      const barWidth = Math.min(10, Math.max(6, slotWidth * 0.22))
      const gap = Math.max(3, slotWidth * 0.06)

      return widget.fields.map((field, index) => {
        const value = this.numberFrom(point?.series, field.field)
        const height = (value / maxValue) * 54
        const x = widget.fields.length === 2
          ? (index === 0 ? centerX - gap - barWidth : centerX + gap)
          : centerX - ((widget.fields.length * barWidth) / 2) + (index * barWidth)

        return {
          key: field.field,
          x,
          y: 70 - height,
          width: barWidth,
          height,
          color: field.color,
          tooltip: this.buildTooltip(field.label, value, pointTs),
        }
      })
    },
    stackedBars(widget, point, centerX, slotWidth, maxValue, pointTs) {
      const barWidth = Math.min(16, Math.max(10, slotWidth * 0.44))
      const total = widget.fields.reduce((sum, field) => sum + this.numberFrom(point?.series, field.field), 0)
      const totalHeight = (total / maxValue) * 54
      let y = 70 - totalHeight
      const topToBottom = widget.fields.map((field) => {
        const value = this.numberFrom(point?.series, field.field)
        const height = totalHeight * (value / Math.max(total, 1))
        const bar = {
          key: field.field,
          x: centerX - (barWidth / 2),
          y,
          width: barWidth,
          height,
          color: field.color,
          tooltip: this.buildTooltip(field.label, value, pointTs),
        }
        y += height
        return bar
      })

      return topToBottom.reverse()
    },
    buildTrendChart(widget) {
      const rows = this.series[widget.seriesKey] || []

      return {
        type: 'trend',
        lines: widget.fields.map((field) => this.trendLine(field, rows)),
        labels: rows.map((point) => this.formatLabel(point?.label_ts || point?.bucket_end)),
        labelGridStyle: this.labelGridStyle(rows.length),
      }
    },
    trendLine(field, rows) {
      const values = rows.map((point) => this.numberFrom(point?.series, field.field))
      const maxValue = Math.max(1, ...values)
      const left = 24
      const right = 172
      const baseY = 36
      const height = 24
      const step = rows.length > 1 ? ((right - left) / Math.max(rows.length - 1, 1)) : (right - left)
      const points = values.map((value, index) => {
        const sourcePoint = rows[index] || {}
        const x = left + (step * index)
        const y = baseY - ((value / maxValue) * height)
        const ts = sourcePoint?.point_ts || sourcePoint?.label_ts || sourcePoint?.bucket_end

        return {
          x,
          y,
          value,
          tooltip: this.buildTooltip(field.label, value, ts),
        }
      })

      const last = points[points.length - 1] || { x: left, y: baseY, value: 0 }
      const previous = points[points.length - 2] || last
      const projectedValue = Math.max(0, Math.round(last.value + (last.value - previous.value)))
      const projectedY = baseY - ((projectedValue / maxValue) * height)
      const projectedX = Math.min(402, last.x + step)

      return {
        key: field.key,
        color: field.color,
        points: points.map((point) => `${point.x},${point.y}`).join(' '),
        pointItems: points,
        projection: `${last.x},${last.y} ${projectedX},${projectedY}`,
        max: maxValue,
        mid: Math.round(maxValue / 2),
      }
    },
    buildAlarmGauge(summary) {
      const inbound = this.numberFrom(summary, 'inbound_calls')
      const active = this.numberFrom(summary, 'active_alarms')
      const total = inbound + active
      const snippetPercent = 4
      let inboundPercent = 50

      if (total > 0) {
        if (inbound <= 0) {
          inboundPercent = snippetPercent
        } else if (active <= 0) {
          inboundPercent = 100 - snippetPercent
        } else {
          inboundPercent = (inbound / total) * 100
        }
      }

      return {
        type: 'gauges',
        single: true,
        gauges: [
          this.gauge('alarm', [
            { from: 0, to: inboundPercent, color: '#4b78a8' },
            { from: inboundPercent, to: 100, color: '#b42318' },
          ]),
        ],
      }
    },
    buildServiceGauges(summary) {
      const redMax = Math.max(0, Math.min(100, Number(this.settings.serviceThresholds.redMax || 75)))
      const orangeMax = Math.max(redMax, Math.min(100, Number(this.settings.serviceThresholds.orangeMax || 90)))
      const sections = [
        { from: 0, to: redMax, color: '#dc2626' },
        { from: redMax, to: orangeMax, color: '#f59e0b' },
        { from: orangeMax, to: 100, color: '#16a34a' },
      ]

      return {
        type: 'gauges',
        single: false,
        gauges: [
          this.gauge('automated', sections, this.numberFrom(summary, 'automated_checks'), true),
          this.gauge('physical', sections, this.numberFrom(summary, 'physical_checks'), true),
        ],
        legend: [
          { color: '#dc2626', label: `0-${redMax}%` },
          { color: '#f59e0b', label: `${redMax}-${orangeMax}%` },
          { color: '#16a34a', label: `${orangeMax}-100%` },
        ],
      }
    },
    gauge(key, sections, value = 0, showNeedle = false) {
      const max = 100

      return {
        key,
        max,
        showNeedle,
        needle: this.needlePoint(value, max),
        sections: sections.map((section, index) => ({
          ...section,
          key: `${key}-${index}-${section.from}-${section.to}-${section.color}`,
        })),
      }
    },
    numberFrom(source, field) {
      return Math.max(0, Number(source?.[field] || 0))
    },
    labelGridStyle(count) {
      return {
        gridTemplateColumns: `repeat(${Math.max(count, 1)}, minmax(0, 1fr))`,
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
    showTooltip(event, text, owner) {
      this.tooltip.visible = true
      this.tooltip.owner = owner
      this.tooltip.text = text
      this.moveTooltip(event)
    },
    moveTooltip(event) {
      const preview = event?.currentTarget?.closest('.js-widget-preview')
      if (!preview || !event) {
        return
      }

      const rect = preview.getBoundingClientRect()
      this.tooltip.x = event.clientX - rect.left + 10
      this.tooltip.y = event.clientY - rect.top - 10
    },
    hideTooltip() {
      this.tooltip.visible = false
      this.tooltip.owner = ''
    },
    polarToCartesian(angle) {
      return {
        x: 100 + (Math.cos(angle) * 76),
        y: 100 - (Math.sin(angle) * 76),
      }
    },
    describeGaugeArc(section, max) {
      const safeFrom = Math.max(0, Math.min(max, Number(section.from) || 0))
      let safeTo = Math.max(0, Math.min(max, Number(section.to) || 0))

      if (safeTo <= safeFrom) {
        safeTo = Math.min(max, safeFrom + Math.max(max * 0.01, 0.5))
      }

      const startAngle = Math.PI - ((safeFrom / max) * Math.PI)
      const endAngle = Math.PI - ((safeTo / max) * Math.PI)
      const start = this.polarToCartesian(startAngle)
      const end = this.polarToCartesian(endAngle)

      return `M ${start.x} ${start.y} A 76 76 0 0 1 ${end.x} ${end.y}`
    },
    needlePoint(value, max) {
      const clamped = Math.max(0, Math.min(max, Number(value) || 0))
      const angle = Math.PI - ((clamped / max) * Math.PI)
      const radius = 60

      return {
        x: 100 + (Math.cos(angle) * radius),
        y: 100 - (Math.sin(angle) * radius),
      }
    },
  },
}
</script>

<style scoped>
.dashboard-widgets {
  padding: 0.25rem 0 1.5rem;
}

.dashboard-widgets__grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

.widget-card {
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 13rem;
  padding: 0.95rem 1rem 0.9rem;
  border-radius: 1rem;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 249, 252, 0.96)),
    #ffffff;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
}

.widget-card__header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
}

.widget-card__title {
  margin: 0;
  color: #12243d;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.15;
}

.widget-card__subtitle {
  margin: 0.2rem 0 0;
  color: #64748b;
  font-size: 0.73rem;
  line-height: 1.25;
}

.widget-card__body {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  margin-top: 0.6rem;
}

.widget-layout {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  gap: 0.8rem;
  height: 100%;
}

.compact-widget__top {
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  gap: 0.35rem;
}

.compact-widget__preview,
.trend-preview,
.gauge-preview {
  position: relative;
  width: 11rem;
}

.bar-chart {
  width: 100%;
  height: 4.9rem;
}

.bar-chart__axis,
.trend-chart__axis {
  stroke: rgba(148, 163, 184, 0.45);
  stroke-width: 1.5;
}

.bar-chart__y-label,
.trend-chart__y-label {
  fill: #7b8ca1;
  font-size: 13px;
  font-weight: 700;
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

.mini-labels {
  display: grid;
  gap: 0.2rem;
  margin-top: 0.1rem;
  color: #64748b;
  font-size: 0.63rem;
  text-align: center;
  font-weight: 600;
}

.trend-preview .mini-labels {
  margin-top: 0.18rem;
}

.gauge-preview {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.35rem;
  align-items: flex-end;
}

.gauge-preview--single {
  display: block;
  margin-top: -0.1rem;
}

.gauge-preview__item {
  display: flex;
  align-items: center;
}

.dashboard-gauge {
  width: 100%;
  max-width: 8rem;
  height: auto;
  overflow: visible;
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

.threshold-legend {
  display: flex;
  justify-content: flex-end;
  gap: 0.7rem;
  flex-wrap: wrap;
  margin-top: 0.05rem;
  color: #64748b;
  font-size: 0.68rem;
  font-weight: 600;
}

.threshold-legend span {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.legend-dot {
  width: 0.58rem;
  height: 0.58rem;
  border-radius: 999px;
  display: inline-block;
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

@media (min-width: 900px) {
  .dashboard-widgets__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1440px) {
  .dashboard-widgets__grid {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }
}
</style>
