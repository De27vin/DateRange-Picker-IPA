<template>
  <div class="chart-container">
    <div class="top-controls">
      <!-- Date range picker -->
      <div class="date-picker-wrap">
        <date-picker
          v-model="dateRange"
          range
          :clearable="false"
          :editable="false"
          format="DD.MM.YYYY"
          value-type="date"
          :disabled-date="disableFuture"
          :append-to-body="false"
          range-separator=" - "
          input-class="date-range-input"
          popup-class="single-panel-range force-below-popup"
          @change="onDateRangeChange"
        />
        <div v-if="dateError" class="date-error">{{ dateError }}</div>
        <div v-if="fetchError" class="date-error">{{ fetchError }}</div>
      </div>
      
      <div class="resolution-selector">

      </div>
    </div>

    <canvas ref="barChart"></canvas>
  </div>
</template>

<script>
import axios from 'axios'
import Chart from 'chart.js'
import { normalizeHourlyTimeseries } from '../../../../utils/timeseries'
import { aggregateTimeseries, pickResolution } from '../../../js/utils/timeseriesAggregation'
import DatePicker from 'vue2-datepicker'
import 'vue2-datepicker/index.css'

export default {
  name: 'EquipmentChart',
  components: { DatePicker },

  // Props for live data from Charts.vue
  props: {
    liveEnabled: Number,
    liveDisabled: Number
  },

  data() {
    const today = new Date()
    const start = new Date(today)
    start.setDate(today.getDate() - 6)
    start.setHours(0,0,0,0)

    const end = new Date(today)
    end.setHours(23,0,0,0)

    return {
      _chart: null,
      rawSeries: [],
      series: [],
      seriesResolution: '1h',

      // date range
      dateRange: [start, end],
      dateError: '',
      fetchError: '',
      lastValidRange: [new Date(start), new Date(end)],

      // data
      fullSeries: []
    }
  },

  watch: {
    liveEnabled() {
      this.injectLiveData()
      this.renderChart()
    },
    liveDisabled() {
      this.injectLiveData()
      this.renderChart()
    },
  },

  async mounted() {
    await this.loadData()
  },

  methods: {

    // Method: Disable future dates in date picker
    disableFuture(date) {
      const now = new Date()
      now.setHours(23, 59, 59, 999)
      return date > now
    },

    toYmd(date) {
      const y = date.getFullYear()
      const m = String(date.getMonth() + 1).padStart(2, '0')
      const d = String(date.getDate()).padStart(2, '0')
      return `${y}-${m}-${d}`
    },

    daysInRange(start, end) {
      const msPerDay = 24 * 60 * 60 * 1000
      return Math.floor((end.getTime() - start.getTime()) / msPerDay) + 1
    },

    buildLabel(ts, resolution, isSingleDay) {
      if (!ts) {
        return 'Live'
      }

      const d = new Date(ts)
      const hh = String(d.getHours()).padStart(2, '0')
      if (resolution === '1d' || resolution === '1w') {
        const dd = String(d.getDate()).padStart(2, '0')
        const mm = String(d.getMonth() + 1).padStart(2, '0')
        return `${dd}.${mm}`
      }
      if (isSingleDay && (resolution === '1h' || resolution === '6h')) {
        return `${hh}:00`
      }
      const dd = String(d.getDate()).padStart(2, '0')
      const mm = String(d.getMonth() + 1).padStart(2, '0')
      return `${dd}.${mm} ${hh}:00`
    },

    // Method: Handle date range changes
    async onDateRangeChange(value) {
      const [startRaw, endRaw] = value || this.dateRange || []
      if (!startRaw || !endRaw) {
        this.dateError = ''
        return
      }

      const start = new Date(startRaw)
      start.setHours(0, 0, 0, 0)
      const end = new Date(endRaw)
      end.setHours(23, 0, 0, 0)

      const diffDays = this.daysInRange(start, end)
      if (start > end) {
        this.dateError = 'Start date must be before end date.'
        return
      }
      if (diffDays > 365) {
        this.dateError = 'Date range must be 365 days or less.'
        return
      }

      this.dateError = ''
      this.lastValidRange = [new Date(start), new Date(end)]
      await this.loadData()
    },

    async loadData() {
      const [startRaw, endRaw] = this.dateRange || []
      if (!startRaw || !endRaw) return

      const start = new Date(startRaw)
      start.setHours(0, 0, 0, 0)
      const end = new Date(endRaw)
      end.setHours(23, 0, 0, 0)

      try {
        this.fetchError = ''
        const res = await axios.get('/api/timeseries', {
          params: {
            chart: 'EquipmentChart',
            start: this.toYmd(start),
            end: this.toYmd(end),
          }
        })
        const sorted = (res.data.data ?? []).slice().sort((a, b) => String(a.ts).localeCompare(String(b.ts)))
        const normalized = normalizeHourlyTimeseries(sorted, {
          fill: 'null',
          min: 0,
          max: 100,
        })

        this.rawSeries = normalized
        this.fullSeries = normalized
        this.rebuildAggregatedSeries(start, end)
        this.injectLiveData()
        this.renderChart()
      } catch (e) {
        console.error('Timeseries fetch failed:', e)
        this.rawSeries = []
        this.series = []
        this.fullSeries = []
        this.fetchError = e?.response?.status === 422 ? 'Invalid date range' : 'Failed to load data'
        this.injectLiveData()
        this.renderChart()
      }
    },

    rebuildAggregatedSeries(start, end) {
      const rangeDays = this.daysInRange(start, end)
      this.seriesResolution = pickResolution(rangeDays)
      const aggregated = aggregateTimeseries(this.rawSeries, { start, end })
        .sort((a, b) => String(a.ts).localeCompare(String(b.ts)))

      this.series = aggregated.map((row) => {
        const enabled = Math.max(0, Math.min(100, Number(row.value) || 0))
        return {
          enabled,
          disabled: 100 - enabled,
          timestamp: row.ts,
        }
      })
    },

    injectLiveData() {
      this.series = this.series.filter((x) => x.timestamp !== null)
      this.series.push({
        enabled: Math.max(0, Math.min(100, Number(this.liveEnabled) || 0)),
        disabled: Math.max(0, Math.min(100, Number(this.liveDisabled) || 0)),
        timestamp: null,
      })
    },

    // line chart rendering
    renderChart() {
      const ctx = this.$refs.barChart.getContext('2d');

      const enabledGradient = ctx.createLinearGradient(0, 0, 0, 400)
      enabledGradient.addColorStop(0, 'rgba(214,15,18,0.45)')
      enabledGradient.addColorStop(1, 'rgba(214,15,18,0)')

      const disabledGradient = ctx.createLinearGradient(0, 0, 0, 400)
      disabledGradient.addColorStop(0, 'rgba(103,112,128,0.35)')
      disabledGradient.addColorStop(1, 'rgba(103,112,128,0)')

      const isSingleDay = this.toYmd(new Date(this.lastValidRange[0])) === this.toYmd(new Date(this.lastValidRange[1]))
      const labels = this.series.map(item => this.buildLabel(item.timestamp, this.seriesResolution, isSingleDay))

      const enabled = this.series.map(x => x.enabled);
      const disabled = this.series.map(x => x.disabled);

      if (this._chart) this._chart.destroy();

      this._chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Enabled',
              data: enabled,
              borderColor: '#d60f12',
              backgroundColor: enabledGradient,
              fill: true,
              tension: 0,
              pointRadius: 3,
              pointHoverRadius: 5,
              borderWidth: 2,
            },
            {
              label: 'Disabled',
              data: disabled,
              borderColor: '#677080',
              backgroundColor: disabledGradient,
              fill: true,
              tension: 0,
              pointRadius: 3,
              pointHoverRadius: 5,
              borderWidth: 2,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          tooltips: { enabled: true },
          title: {
            display: true,
            text: 'Equipment',
            fontSize: 16,
            fontStyle: 'bold',
            color: '#beb4b6',
            padding: 10
          },
          plugins: {
            legend: {
              labels: {
                color: '#beb4b6', 
                font: { size: 13, weight: '500' }
              }
            }
          },
          scales: {
            x: {
              ticks: { color: '#beb4b6' },
              grid: { color: 'transparent' }
            },
            y: {
              ticks: { color: '#beb4b6', beginAtZero: true },
              grid: { color: 'rgba(190,180,182,0.15)' }
            }
          }
        }
      });
    }

  }
}
</script>

<style scoped>
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

/* Date Picker Styles */
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
</style>

<style>
/* Custom style to show only one calendar panel */
.single-panel-range .mx-range-wrapper .mx-calendar + .mx-calendar {
  display: none;
}

/* Date range button styling */
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

.date-error {
  margin-top: 4px;
  font-size: 12px;
  color: #b91c1c;
}
</style>
