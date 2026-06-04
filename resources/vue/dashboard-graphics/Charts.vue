<template>
  <div v-if="settingsLoaded" id="dashboard-grid">
    <div v-for="chart in chartConfigs" :key="chart.key" class="grid-box">
      <div class="chart-container">
        <div class="top-controls">
          <div class="date-picker-wrap">
            <date-picker
              v-model="chartStates[chart.key].dateRange"
              range
              :clearable="false"
              :editable="false"
              format="DD.MM.YYYY"
              value-type="date"
              :disabled-date="disableFuture"
              :append-to-body="false"
              range-separator=" - "
              input-class="date-range-input"
              :popup-class="chart.shiftPicker ? 'single-panel-range force-below-popup shift-right-popup' : 'single-panel-range force-below-popup'"
              @change="onDateRangeChange(chart.key, $event)"
            />
            <div v-if="chartStates[chart.key].dateError" class="date-error">
              {{ chartStates[chart.key].dateError }}
            </div>
            <div v-if="chartStates[chart.key].fetchError" class="date-error">
              {{ chartStates[chart.key].fetchError }}
            </div>
          </div>

          <div class="resolution-selector"></div>
        </div>

        <button v-if="chart.filterable" class="filter-btn" @click="chartStates[chart.key].showFilters = true">
          Filters
        </button>

        <canvas :ref="`chart-${chart.key}`"></canvas>

        <div v-if="chart.filterable && chartStates[chart.key].showFilters" class="filter-overlay">
          <div class="filter-modal">
            <button class="close-btn" @click="chartStates[chart.key].showFilters = false">x</button>

            <div class="filter-grid">
              <div v-for="field in chart.fields" :key="field.key" class="filter-cell">
                <input
                  v-model="chartStates[chart.key].selectedKeys"
                  type="checkbox"
                  :value="field.key"
                  @change="renderChart(chart.key)"
                />
                <span>{{ field.label }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Chart from 'chart.js'
import DatePicker from 'vue2-datepicker'
import 'vue2-datepicker/index.css'
import { formatChartLabel } from '../../js/utils/timeseriesDisplay'
import { buildLiveSeriesRow, normalizeSeriesRows } from '../../js/utils/timeseriesSeries'
import {
  disableFutureUtc,
  toIso8601Utc,
  toYmdUtc,
  validateAndNormalizeRange,
} from '../../js/utils/timeseriesRangeValidation'
import {
  resolveRollingRange,
  sanitizeRollingRange,
  SYSTEM_DASHBOARD_WIDGET_DEFAULTS,
} from '../../js/utils/dashboardWidgetSettings'

const SYSTEM_CHART_SETTINGS = {
  ranges: {
    equipment: { amount: 3, unit: 'months' },
    alarms: { amount: 3, unit: 'months' },
    alerts: { amount: 3, unit: 'months' },
    serviceLevel: { amount: 3, unit: 'months' },
  },
}

const ALERT_FIELDS = [
  { key: 'active_alarm', liveKey: 'Active_alarm', label: 'Active alarm' },
  { key: 'battery_malfunction', liveKey: 'Battery_malfunction', label: 'Battery malfunction' },
  { key: 'battery_low', liveKey: 'Battery_low', label: 'Battery low' },
  { key: 'button_malfunction', liveKey: 'Button_malfunction', label: 'Button malfunction' },
  { key: 'charge_malfunction', liveKey: 'Charge_malfunction', label: 'Charge malfunction' },
  { key: 'database_malfunction', liveKey: 'Database_malfunction', label: 'Database malfunction' },
  { key: 'disk_low', liveKey: 'Disk_low', label: 'Disk low' },
  { key: 'object_door_failure', liveKey: 'Object_door_failure', label: 'Object door failure' },
  { key: 'elevator_failure', liveKey: 'Elevator_failure', label: 'Elevator failure' },
  { key: 'gateway_malfunction', liveKey: 'Gateway_malfunction', label: 'Gateway malfunction' },
  { key: 'identity_mismatch', liveKey: 'Identity_mismatch', label: 'Identity mismatch' },
  { key: 'line_alarm', liveKey: 'Line_alarm', label: 'Line alarm' },
  { key: 'object_is_under_maintenance', liveKey: 'Object_is_under_maintenance', label: 'Object under maintenance' },
  { key: 'microphone_malfunction', liveKey: 'Microphone_malfunction', label: 'Microphone malfunction' },
  { key: 'network_malfunction', liveKey: 'Network_malfunction', label: 'Network malfunction' },
  { key: 'periodical_call_overdue', liveKey: 'Periodical_call_overdue', label: 'Periodical call overdue' },
  { key: 'pin_mismatch', liveKey: 'Pin_mismatch', label: 'Pin mismatch' },
  { key: 'power_malfunction', liveKey: 'Power_malfunction', label: 'Power malfunction' },
  { key: 'ram_low', liveKey: 'Ram_low', label: 'Ram low' },
  { key: 'reserved_device', liveKey: 'Reserved_device', label: 'Reserved device' },
  { key: 'serial_port_malfunction', liveKey: 'Serial_port_malfunction', label: 'Serial port malfunction' },
  { key: 'shaft_failure', liveKey: 'Shaft_failure', label: 'Shaft failure' },
  { key: 'low_signal', liveKey: 'Low_signal', label: 'Low signal' },
  { key: 'sip_registration_failure', liveKey: 'Sip_registration_failure', label: 'SIP registration failure' },
  { key: 'speaker_malfunction', liveKey: 'Speaker_malfunction', label: 'Speaker malfunction' },
  { key: 'technician_check_overdue', liveKey: 'Technician_check_overdue', label: 'Technician check overdue' },
  { key: 'voice_alarm', liveKey: 'Voice_alarm', label: 'Voice alarm' },
]

const CHART_CONFIGS = [
  {
    key: 'equipment',
    apiChart: 'EquipmentChart',
    title: 'Equipment',
    rangeKey: 'equipment',
    liveSource: 'equipmentStats',
    fields: [
      {
        key: 'enabled',
        liveKey: 'enabled',
        label: 'Enabled',
        borderColor: '#d60f12',
        pointRadius: 3,
        pointHoverRadius: 5,
        borderWidth: 2,
      },
      {
        key: 'disabled',
        liveKey: 'disabled',
        label: 'Disabled',
        borderColor: '#677080',
        pointRadius: 3,
        pointHoverRadius: 5,
        borderWidth: 2,
      },
    ],
    xGridColor: 'transparent',
    yGridColor: 'rgba(190,180,182,0.15)',
  },
  {
    key: 'alarms',
    apiChart: 'AlarmChart',
    title: 'Alarms',
    rangeKey: 'alarms',
    liveSource: 'alarmStats',
    shiftPicker: true,
    fields: [
      {
        key: 'inbound_calls',
        liveKey: 'inbound',
        label: 'Inbound calls',
        borderColor: '#c17579',
        pointBackgroundColor: '#ff7a18',
        pointRadius: 4,
      },
      {
        key: 'active_alarms',
        liveKey: 'active',
        label: 'Active alarms',
        borderColor: '#a2232a',
        pointBackgroundColor: '#e11d48',
        pointRadius: 4,
      },
    ],
    xGridColor: 'transparent',
    yGridColor: 'rgba(190,180,182,0.15)',
  },
  {
    key: 'alerts',
    apiChart: 'AlertsChart',
    title: 'Alerts',
    rangeKey: 'alerts',
    liveSource: 'alertsStats',
    filterable: true,
    fields: ALERT_FIELDS,
    xGridColor: 'rgba(255,255,255,0.05)',
    yGridColor: 'rgba(255,255,255,0.05)',
    tickFont: { size: 12, weight: '500' },
  },
  {
    key: 'serviceLevel',
    apiChart: 'ServiceLevelChart',
    title: 'Service Level',
    rangeKey: 'serviceLevel',
    liveSource: 'serviceStats',
    shiftPicker: true,
    fields: [
      {
        key: 'periodical_calls',
        liveKey: 'periodicalCalls',
        label: 'Periodic calls',
        borderColor: '#d60f12',
        pointBackgroundColor: '#ff7a18',
        pointRadius: 4,
      },
      {
        key: 'local_checks',
        liveKey: 'localChecks',
        label: 'Local checks',
        borderColor: '#c17579',
        pointBackgroundColor: '#e11d48',
        pointRadius: 4,
      },
    ],
    xGridColor: 'transparent',
    yGridColor: 'rgba(190,180,182,0.15)',
  },
]

function interpolateColor(start, end, factor) {
  return start.map((value, index) => Math.round(value + factor * (end[index] - value)))
}

function gradientColor(index, total) {
  const start = [23, 44, 81]
  const end = [162, 35, 42]

  if (total <= 1) {
    return `rgba(${start.join(',')}, 1)`
  }

  const [r, g, b] = interpolateColor(start, end, index / (total - 1))
  return `rgba(${r}, ${g}, ${b}, 1)`
}

export default {
  name: 'ChartsPage',
  components: { DatePicker },
  data() {
    return {
      chartConfigs: CHART_CONFIGS,
      chartSettings: SYSTEM_CHART_SETTINGS,
      chartStates: this.createChartStates(SYSTEM_CHART_SETTINGS),
      chartInstances: {},
      settingsLoaded: false,
      equipmentStats: this.windowValue('EQUIPMENT_STATS', { enabled: 0, disabled: 0 }),
      alarmStats: this.windowValue('ALARM_STATS', { inbound: 0, active: 0 }),
      alertsStats: this.windowValue('ALERTS_STATS', this.emptyAlertStats()),
      serviceStats: this.windowValue('SERVICE_STATS', { periodicalCalls: 0, localChecks: 0 }),
    }
  },
  watch: {
    equipmentStats: {
      deep: true,
      handler() {
        this.refreshLiveData('equipment')
      },
    },
    alarmStats: {
      deep: true,
      handler() {
        this.refreshLiveData('alarms')
      },
    },
    alertsStats: {
      deep: true,
      handler() {
        this.refreshLiveData('alerts')
      },
    },
    serviceStats: {
      deep: true,
      handler() {
        this.refreshLiveData('serviceLevel')
      },
    },
  },
  async mounted() {
    try {
      const response = await axios.get('/api/charts/settings')
      this.chartSettings = response?.data?.data || SYSTEM_CHART_SETTINGS
    } catch (error) {
      console.error('Charts settings fetch failed:', error)
      this.chartSettings = SYSTEM_CHART_SETTINGS
    }

    this.applyDefaultRanges()
    this.settingsLoaded = true
    await this.$nextTick()
    await Promise.all(this.chartConfigs.map((chart) => this.loadData(chart.key)))
  },
  beforeDestroy() {
    Object.values(this.chartInstances).forEach((chart) => chart?.destroy())
  },
  methods: {
    windowValue(key, fallback) {
      return typeof window !== 'undefined' && window[key] ? window[key] : fallback
    },
    emptyAlertStats() {
      return ALERT_FIELDS.reduce((stats, field) => {
        stats[field.liveKey] = 0
        return stats
      }, {})
    },
    createChartStates(settings) {
      return CHART_CONFIGS.reduce((states, chart) => {
        const dateRange = this.resolveDateRange(settings?.ranges?.[chart.rangeKey])

        states[chart.key] = {
          series: [],
          seriesResolution: '1h',
          dateRange,
          dateError: '',
          fetchError: '',
          lastValidRange: [new Date(dateRange[0]), new Date(dateRange[1])],
          isManualDateRange: false,
          selectedKeys: chart.filterable ? chart.fields.slice(0, 5).map((field) => field.key) : [],
          showFilters: false,
        }

        return states
      }, {})
    },
    applyDefaultRanges() {
      this.chartConfigs.forEach((chart) => {
        const state = this.chartStates[chart.key]
        if (!state?.isManualDateRange) {
          this.setDateRange(chart.key, this.chartSettings?.ranges?.[chart.rangeKey])
        }
      })
    },
    setDateRange(key, range) {
      const dateRange = this.resolveDateRange(range)
      const state = this.chartStates[key]

      state.dateRange = dateRange
      state.lastValidRange = [new Date(dateRange[0]), new Date(dateRange[1])]
    },
    disableFuture(date) {
      return disableFutureUtc(date)
    },
    toYmd(date) {
      return toYmdUtc(date)
    },
    toIso(date) {
      return toIso8601Utc(date)
    },
    buildLabel(ts, resolution, isSingleDay) {
      return formatChartLabel(ts, resolution, isSingleDay, {
        displayMode: 'local',
      })
    },
    resolveDateRange(range) {
      const resolved = resolveRollingRange(sanitizeRollingRange(range, SYSTEM_DASHBOARD_WIDGET_DEFAULTS.ranges.equipment))
      const [startYear, startMonth, startDay] = resolved.start.split('-').map(Number)
      const [endYear, endMonth, endDay] = resolved.end.split('-').map(Number)

      return [
        new Date(startYear, startMonth - 1, startDay, 0, 0, 0, 0),
        new Date(endYear, endMonth - 1, endDay, 23, 0, 0, 0),
      ]
    },
    async onDateRangeChange(key, value) {
      const state = this.chartStates[key]
      const [startRaw, endRaw] = value || state.dateRange || []
      const normalized = validateAndNormalizeRange(startRaw, endRaw)

      if (!normalized.ok) {
        state.dateError = normalized.error
        return
      }

      const { startUtc: start, endUtc: end } = normalized
      state.dateError = ''
      state.isManualDateRange = true
      state.lastValidRange = [new Date(start), new Date(end)]
      await this.loadData(key)
    },
    async loadData(key) {
      const chart = this.chartByKey(key)
      const state = this.chartStates[key]
      const [startRaw, endRaw] = state.dateRange || []
      const normalized = validateAndNormalizeRange(startRaw, endRaw)

      if (!normalized.ok) {
        state.dateError = normalized.error
        return
      }

      const { startUtc: start, endUtc: end } = normalized
      state.dateError = ''

      try {
        state.fetchError = ''
        const response = await axios.get('/api/timeseries', {
          params: {
            chart: chart.apiChart,
            start: this.toIso(start),
            end: this.toIso(end),
          },
        })

        state.seriesResolution = response?.data?.meta?.resolution ?? '1h'
        state.series = normalizeSeriesRows(response?.data?.data, chart.fields.map((field) => field.key))
      } catch (error) {
        console.error('Timeseries fetch failed:', error)
        state.series = []
        state.fetchError = error?.response?.status === 422 ? 'Invalid date range' : 'Failed to load data'
      }

      this.injectLiveData(key)
      this.renderChart(key)
    },
    refreshLiveData(key) {
      if (!this.chartStates[key]) {
        return
      }

      this.injectLiveData(key)
      this.renderChart(key)
    },
    injectLiveData(key) {
      const chart = this.chartByKey(key)
      const state = this.chartStates[key]
      const liveSource = this[chart.liveSource] || {}
      const liveValues = chart.fields.reduce((values, field) => {
        values[field.key] = liveSource[field.liveKey]
        return values
      }, {})

      state.series = state.series.filter((row) => row.timestamp !== null)
      state.series.push(buildLiveSeriesRow(liveValues))
    },
    renderChart(key) {
      const chart = this.chartByKey(key)
      const state = this.chartStates[key]
      const canvasRef = this.$refs[`chart-${key}`]
      const canvas = Array.isArray(canvasRef) ? canvasRef[0] : canvasRef

      if (!canvas || !state) {
        return
      }

      const ctx = canvas.getContext('2d')
      const isSingleDay = this.toYmd(new Date(state.lastValidRange[0])) === this.toYmd(new Date(state.lastValidRange[1]))
      const labels = state.series.map((item) => this.buildLabel(item.timestamp, state.seriesResolution, isSingleDay))
      const fields = chart.filterable
        ? chart.fields.filter((field) => state.selectedKeys.includes(field.key))
        : chart.fields

      if (this.chartInstances[key]) {
        this.chartInstances[key].destroy()
      }

      this.chartInstances[key] = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: fields.map((field, index) => this.datasetForField(chart, field, index, fields.length)),
        },
        options: this.chartOptions(chart),
      })
    },
    datasetForField(chart, field, index, total) {
      const alertColor = chart.filterable ? gradientColor(index, total) : null
      const backgroundColor = alertColor ? alertColor.replace(', 1)', ', 0)') : 'transparent'

      return {
        label: field.label,
        data: this.chartStates[chart.key].series.map((point) => point[field.key] ?? 0),
        borderColor: alertColor || field.borderColor,
        backgroundColor,
        fill: Boolean(alertColor),
        tension: 0,
        pointRadius: field.pointRadius ?? 4,
        pointHoverRadius: field.pointHoverRadius,
        pointBackgroundColor: alertColor || field.pointBackgroundColor || field.borderColor,
        borderWidth: field.borderWidth,
      }
    },
    chartOptions(chart) {
      return {
        responsive: true,
        maintainAspectRatio: false,
        tooltips: { enabled: true },
        title: {
          display: true,
          text: chart.title,
          fontSize: 16,
          fontStyle: 'bold',
          color: '#beb4b6',
          padding: 10,
        },
        plugins: {
          legend: {
            labels: {
              color: '#beb4b6',
              font: { size: 13, weight: '500' },
            },
          },
        },
        scales: {
          x: {
            ticks: { color: '#beb4b6', font: chart.tickFont },
            grid: { color: chart.xGridColor },
          },
          y: {
            ticks: { color: '#beb4b6', font: chart.tickFont, beginAtZero: true },
            grid: { color: chart.yGridColor },
          },
        },
      }
    },
    chartByKey(key) {
      return this.chartConfigs.find((chart) => chart.key === key)
    },
  },
}
</script>

<style scoped>
#dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-top: 20px;
}

.grid-box {
  border: 1px solid #ccc;
  background-color: #fff;
  padding: 10px;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-container {
  width: 100%;
  height: 420px;
  position: relative;
  padding: 12px;
  border: 1px solid rgba(240, 240, 240, 0.08);
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(11, 18, 51, 0.5);
  overflow: visible;
  animation: chartEnter 700ms cubic-bezier(.2, .9, .2, 1) both;
}

.chart-container::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 10px;
  pointer-events: none;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03);
}

canvas {
  width: 100% !important;
  height: 100% !important;
  display: block;
  background: transparent;
}

@keyframes chartEnter {
  0% {
    opacity: 0;
    transform: translateY(8px);
  }

  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.top-controls {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 30;
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.date-picker-wrap {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.resolution-selector {
  position: relative;
  margin: 0;
  padding: 0;
  z-index: 20;
}

.filter-btn {
  position: absolute;
  top: 4px;
  right: 12px;
  z-index: 10;
  background: rgba(24, 44, 81, 0.15);
  border: 1px solid rgba(53, 64, 85, 0.4);
  color: #354055;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
}

.filter-btn:hover {
  background: rgba(24, 44, 81, 0.25);
}

.filter-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.filter-modal {
  position: relative;
  width: 80%;
  max-width: 1300px;
  height: 95%;
  background: rgba(24, 44, 81, 0.25);
  border-radius: 12px;
  padding: 12px 24px 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
}

.close-btn {
  position: absolute;
  top: -6px;
  right: 0;
  background: transparent;
  border: none;
  color: #cbd5e1;
  font-size: 20px;
  cursor: pointer;
}

.close-btn:hover {
  color: #f43f5e;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  grid-template-rows: repeat(6, 1fr);
  gap: 12px;
  margin-top: 24px;
  height: calc(100% - 32px);
}

.filter-cell {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.05);
  padding: 0 10px;
  border-radius: 6px;
  color: #e5e7eb;
  font-size: 12px;
}

.filter-cell input[type="checkbox"] {
  width: 16px;
  height: 16px;
  color: #354055;
  cursor: pointer;
  border-radius: 4px;
}
</style>

<style>
.single-panel-range .mx-range-wrapper .mx-calendar + .mx-calendar {
  display: none;
}

.date-range-input {
  color: #000000 !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  height: 30px !important;
  padding: 4px 30px 4px 8px !important;
  border-radius: 8px !important;
}

.force-below-popup.mx-datepicker-popup {
  top: calc(100% + 6px) !important;
  bottom: auto !important;
}

.shift-right-popup.mx-datepicker-popup {
  left: 1px !important;
}

.date-error {
  margin-top: 4px;
  font-size: 12px;
  color: #b91c1c;
}
</style>
