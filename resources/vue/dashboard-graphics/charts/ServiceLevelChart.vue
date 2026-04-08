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
          popup-class="single-panel-range force-below-popup shift-right-popup"
          @change="onDateRangeChange"
        />
        <div v-if="dateError" class="date-error">{{ dateError }}</div>
        <div v-if="fetchError" class="date-error">{{ fetchError }}</div>
      </div>
      
      <div class="resolution-selector">

      </div>
    </div>

    <canvas ref="chart"></canvas>
  </div>
</template>

<script>
import axios from 'axios'
import Chart from 'chart.js'
import { formatChartLabel } from '../../../js/utils/timeseriesDisplay'
import { buildLiveSeriesRow, normalizeSeriesRows } from '../../../js/utils/timeseriesSeries'
import {
  disableFutureUtc,
  toIso8601Utc,
  toYmdUtc,
  validateAndNormalizeRange,
} from '../../../js/utils/timeseriesRangeValidation'
import DatePicker from 'vue2-datepicker'
import 'vue2-datepicker/index.css'

const SERVICE_LEVEL_SERIES_KEYS = ['periodical_calls', 'local_checks']

export default {
  name: 'ServiceLevelChart',
  components: { DatePicker },

  props: {
    livePeriodic: Number,
    liveLocal: Number
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
      series: [],
      seriesResolution: '1h',

      // date range
      dateRange: [start, end],
      dateError: '',
      fetchError: '',
      lastValidRange: [new Date(start), new Date(end)],
    }
  },
  
  watch: {
    livePeriodic() {
      this.injectLiveData()
      this.renderChart()
    },
    liveLocal() {
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

    // Method: Handle date range changes
    async onDateRangeChange(value) {
      const [startRaw, endRaw] = value || this.dateRange || []
      const normalized = validateAndNormalizeRange(startRaw, endRaw)
      if (!normalized.ok) {
        this.dateError = normalized.error
        return
      }

      const { startUtc: start, endUtc: end } = normalized
      this.dateError = ''
      this.lastValidRange = [new Date(start), new Date(end)]
      await this.loadData()
    },

    async loadData() {
      const [startRaw, endRaw] = this.dateRange || []
      const normalized = validateAndNormalizeRange(startRaw, endRaw)
      if (!normalized.ok) {
        this.dateError = normalized.error
        return
      }

      const { startUtc: start, endUtc: end } = normalized
      this.dateError = ''

      try {
        this.fetchError = ''
        const res = await axios.get('/api/timeseries', {
          params: {
            chart: 'ServiceLevelChart',
            start: this.toIso(start),
            end: this.toIso(end),
          }
        })
        this.seriesResolution = res?.data?.meta?.resolution ?? '1h'
        this.series = normalizeSeriesRows(res?.data?.data, SERVICE_LEVEL_SERIES_KEYS)
        this.injectLiveData()
        this.renderChart()
      } catch (e) {
        console.error('Timeseries fetch failed:', e)
        this.series = []
        this.fetchError = e?.response?.status === 422 ? 'Invalid date range' : 'Failed to load data'
        this.injectLiveData()
        this.renderChart()
      }
    },

    injectLiveData() {
      this.series = this.series.filter((x) => x.timestamp !== null)
      this.series.push(buildLiveSeriesRow({
        periodical_calls: this.livePeriodic,
        local_checks: this.liveLocal,
      }))
    },

    renderChart() {
      const ctx = this.$refs.chart.getContext('2d')

      const periodicGradient = ctx.createLinearGradient(0, 0, 0, 400)
      periodicGradient.addColorStop(0, 'rgba(214,15,18,0.45)')
      periodicGradient.addColorStop(0, 'rgba(214,15,18,0)')

      const localGradient = ctx.createLinearGradient(0, 0, 0, 400)
      localGradient.addColorStop(0, 'rgba(193,117,121,0.45)')
      localGradient.addColorStop(0, 'rgba(193,117,121,0)')

      const isSingleDay = this.toYmd(new Date(this.lastValidRange[0])) === this.toYmd(new Date(this.lastValidRange[1]))
      const labels = this.series.map(item => this.buildLabel(item.timestamp, this.seriesResolution, isSingleDay))

      const periodic = this.series.map(x => x.periodical_calls ?? 0)
      const local = this.series.map(x => x.local_checks ?? 0)

      if (this._chart) this._chart.destroy()

      this._chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Periodic calls',
              data: periodic,
              borderColor: '#d60f12',
              backgroundColor: periodicGradient,
              fill: true,
              tension: 0,
              pointRadius: 4,
              pointBackgroundColor: '#ff7a18'
            },
            {
              label: 'Local checks',
              data: local,
              borderColor: '#c17579',
              backgroundColor: localGradient,
              fill: true,
              tension: 0,
              pointRadius: 4,
              pointBackgroundColor: '#e11d48'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          tooltips: { enabled: true },
          title: {
            display: true,
            text: 'Service Level',
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
      })
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
}

canvas {
  width: 100% !important;
  height: 100% !important;
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

.shift-right-popup.mx-datepicker-popup {
  left: 1px !important;
}

.date-error {
  margin-top: 4px;
  font-size: 12px;
  color: #b91c1c;
}
</style>
