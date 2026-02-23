<template>
  <div class="chart-container">
    <!-- Time span button -->
    <div class="resolution-selector">
      <button class="timespan-btn" @click="showResolutionMenu = !showResolutionMenu">
        {{ resolutionLabel }}
      </button>

      <div v-if="showResolutionMenu" class="resolution-menu">
        <div @click="setResolution('hourly')">Hourly</div>
        <div @click="setResolution('6h')">6 Hours</div>
        <div @click="setResolution('daily')">Daily</div>
      </div>
    </div>

    <canvas ref="chart"></canvas>
  </div>
</template>

<script>
import axios from 'axios'
import Chart from 'chart.js'
import { normalizeHourlyTimeseries } from '../../../../utils/timeseries'

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
  name: 'ServiceLevelChart',

  props: {
    livePeriodic: Number,
    liveLocal: Number
  },

  data() {
    return {
      _chart: null,
      chartData: [],

      // Time span selection
      timeResolution: "hourly",
      showResolutionMenu: false,
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
    async loadData() {
      try {
        const res = await axios.get('/api/timeseries?hours=500')
        this.fullSeries = res.data;

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
        periodic_checks: Number(this.livePeriodic) || 0,
        local_checks: Number(this.liveLocal) || 0,
        timestamp: null
      })
    },

    // Method: Set time span resolution
    setResolution(resolution) {
      this.timeResolution = resolution
      this.showResolutionMenu = false

      // Generate historical data
      this.chartData = this.resolveChartData();

      // Add live value
      this.injectLiveData();

      // Re-render chart with new data
      this.renderChart();

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

      const periodicGradient = ctx.createLinearGradient(0, 0, 0, 400)
      periodicGradient.addColorStop(0, 'rgba(214,15,18,0.45)')
      periodicGradient.addColorStop(1, 'rgba(214,15,18,0)')

      const localGradient = ctx.createLinearGradient(0, 0, 0, 400)
      localGradient.addColorStop(0, 'rgba(193,117,121,0.45)')
      localGradient.addColorStop(1, 'rgba(193,117,121,0)')

      const labels = this.chartData.map(item => {
        if (!item.timestamp) return 'Live';
        const d = new Date(item.timestamp);

        if (this.timeResolution === 'daily') return d.toLocaleDateString();
        return `${d.getHours().toString().padStart(2, '0')}:00`
      });

      const periodic = this.chartData.map(x => x.periodic_checks ?? 0)
      const local = this.chartData.map(x => x.local_checks ?? 0)

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
        plugins: [valueLabelPlugin],
        options: {
          responsive: true,
          maintainAspectRatio: false,
          tooltips: { enabled: false },
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
  overflow: hidden;
}

canvas {
  width: 100% !important;
  height: 100% !important;
}

/* Time Span Button Styles */
.timespan-btn {
  position: absolute;
  top: 0;
  left: 0;
  margin: 0;
  background: rgba(24, 44, 81, 0.15);
  border: 1px solid rgba(53, 64, 85, 0.4);
  color: #354055;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
}

.timespan-btn:hover {
  background: rgba(24, 44, 81, 0.25);
}

.resolution-selector {
  position: absolute;
  top: 12px;
  left: 12px;
  margin: 0;
  padding: 0;
  z-index: 20;
  display: block;
}

.resolution-menu {
  margin-top: 40px;
  border: 1px solid rgba(53, 64, 85, 0.4);
  border-radius: 6px;
  overflow: hidden;
  min-width: 120px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.resolution-menu>div {
  padding: 8px 12px;
  font-size: 13px;
  color: #354055;
  cursor: pointer;
}

.resolution-menu>div:hover {
  background: rgba(24, 44, 81, 0.25);
}
</style>
