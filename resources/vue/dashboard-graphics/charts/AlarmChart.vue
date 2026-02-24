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
import { normalizeHourlyTimeseries } from '../../../../utils/timeseries'
import DatePicker from 'vue2-datepicker'
import 'vue2-datepicker/index.css'

// Plugin to display value labels above data points
const valueLabelPlugin = {
  afterDatasetsDraw(chart) {
    const ctx = chart.ctx
    ctx.save()
    ctx.font = '12px sans-serif'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'bottom'
    ctx.fillStyle = '#354055'

    chart.data.datasets.forEach((dataset, datasetIndex) => {
      const meta = chart.getDatasetMeta(datasetIndex)
      if (meta.hidden) return

      meta.data.forEach((point, index) => {
        const value = dataset.data[index]
        if (value === null || value === undefined) return

        ctx.fillText(value, point._model.x, point._model.y - 6)
      })
    })

    ctx.restore()
  }
}

export default {
  name: 'AlarmChart',
  components: { DatePicker },

  props: {
    liveInbound: Number,
    liveActive: Number
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
      chartData: [],

      // resolution
      timeResolution: "hourly",
      showResolutionMenu: false,

      // date range
      dateRange: [start, end],
      dateError: '',
      lastValidRange: [new Date(start), new Date(end)],

      // data
      fullSeries: []
    }
  },

  computed: {
    resolutionLabel() {
      if (this.timeResolution === 'hourly') return 'Hourly'
      if (this.timeResolution === '6h') return '6 Hours'
      if (this.timeResolution === 'daily') return 'Daily'
    }
  },

  async mounted() {
    await this.loadData()
    this.chartData = this.resolveChartData()
    this.injectLiveData()
    this.renderChart()
  },

  methods: {

    // Method: Disable future dates in date picker
    disableFuture(date) {
      const now = new Date()
      now.setHours(23, 59, 59, 999)
      return date > now
    },

    // Method: Handle date range changes
    onDateRangeChange(value) {
      const [startRaw, endRaw] = value || this.dateRange || []
      if (!startRaw || !endRaw) {
        this.dateError = ''
        return
      }

      const start = new Date(startRaw)
      start.setHours(0, 0, 0, 0)
      const end = new Date(endRaw)
      end.setHours(23, 0, 0, 0)

      if (start > end) {
        this.dateError = 'Start date must be before end date.'
        this.dateRange = [new Date(this.lastValidRange[0]), new Date(this.lastValidRange[1])]
        return
      }

      const diffDays = Math.floor((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
      if (diffDays > 365) {
        this.dateError = 'Date range must be 365 days or less.'
        this.dateRange = [new Date(this.lastValidRange[0]), new Date(this.lastValidRange[1])]
        return
      }

      this.dateError = ''
      this.lastValidRange = [new Date(start), new Date(end)]
    },

    async loadData() {
      try {
        const res = await axios.get('/api/timeseries?hours=500')
        this.fullSeries = res.data

        // Normalize timeseries to ensure hourly data is consistent
        const normalized = normalizeHourlyTimeseries(res.data.data ?? [], {
          dedupe: 'last',
          fill: 'carry',
          min: 0,
          max: 100,
        });

      } catch (e) {
        console.error('Timeseries fetch failed:', e)
        this.fullSeries = []
      }
    },

    injectLiveData() {
      // Remove existing live value if present
      this.chartData = this.chartData.filter(x => x.timestamp !== null);

      // Add new live point
      this.chartData.push({
        inbound_calls: Number(this.liveInbound) || 0,
        active_alarms: Number(this.liveActive) || 0,
        timestamp: null
      })
    },

    // Method: Set time span resolution
    setResolution(resolution) {
      this.timeResolution = resolution
      this.showResolutionMenu = false

      // Generate historical data
      this.chartData = this.resolveChartData()

      // Add live value
      this.injectLiveData()

      // Re-render chart with new data
      this.renderChart()
    },

    // Method: Returns chart data based on selected time resolution
    resolveChartData() {
      const now = new Date();
      let result = [];

      if (this.timeResolution === 'hourly') {
        result = this.getLastFullHours(1);
      } else if (this.timeResolution === '6h') {
        result = this.getLastFullHours(6);
      } else if (this.timeResolution === 'daily') {
        result = this.getLastDays();
      }

      return result;
    },

    // Method: Get last full hours from timeseries
    getLastFullHours(step) {
      const result = []
      const now = new Date()
      now.setMinutes(0, 0, 0)

      for (let i = 0; i <= 5; i++) {
        const target = new Date(now)
        target.setHours(now.getHours() - i * step)

        const match = this.fullSeries.find(row => {
          const t = new Date(row.timestamp)
          return (
            t.getFullYear() === target.getFullYear() &&
            t.getMonth() === target.getMonth() &&
            t.getDate() === target.getDate() &&
            t.getHours() === target.getHours()
          )
        })

        if (match) result.unshift(match)
      }

      return result
    },

    // Method: Get last days from timeseries
    getLastDays() {
      const result = []
      const now = new Date()

      for (let i = 1; i <= 5; i++) {
        const target = new Date(now)
        target.setDate(now.getDate() - i)
        target.setHours(23, 0, 0, 0)

        const match = this.fullSeries.find(row => {
          const t = new Date(row.timestamp)
          return (
            t.getFullYear() === target.getFullYear() &&
            t.getMonth() === target.getMonth() &&
            t.getDate() === target.getDate() &&
            t.getHours() === 23
          )
        })

        if (match) result.unshift(match)
      }

      return result
    },

    renderChart() {
      const ctx = this.$refs.chart.getContext('2d')

      const inboundGradient = ctx.createLinearGradient(0, 0, 0, 400)
      inboundGradient.addColorStop(0, 'rgba(193,117,121,0.45)')
      inboundGradient.addColorStop(1, 'rgba(193,117,121,0)')

      const activeGradient = ctx.createLinearGradient(0, 0, 0, 400)
      activeGradient.addColorStop(0, 'rgba(162,35,42,0.55)')
      activeGradient.addColorStop(1, 'rgba(162,35,42,0)')

      const labels = this.chartData.map(item => {
        if (!item.timestamp) return 'Live';
        const d = new Date(item.timestamp);

        if (this.timeResolution === 'daily') return d.toLocaleDateString();
        return `${d.getHours().toString().padStart(2, '0')}:00`
      });

      const inbound = this.chartData.map(x => x.inbound_calls ?? 0)
      const active = this.chartData.map(x => x.active_alarms ?? 0)

      if (this._chart) this._chart.destroy()

      this._chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Inbound calls',
              data: inbound,
              borderColor: '#c17579',
              backgroundColor: inboundGradient,
              fill: true,
              tension: 0,
              pointRadius: 4,
              pointBackgroundColor: '#ff7a18'
            },
            {
              label: 'Active alarms',
              data: active,
              borderColor: '#a2232a',
              backgroundColor: activeGradient,
              fill: true,
              tension: 0,
              pointRadius: 4,
              pointBackgroundColor: '#e11d48'
            }
          ]
        },
        plugins: [valueLabelPlugin],
        options: {
          responsive: true,
          maintainAspectRatio: false,
          tooltips: { enabled: false },
          title: {
            display: true,
            text: 'Alarms',
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
