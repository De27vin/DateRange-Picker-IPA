<template>
  <div class="dashboard-widgets">
    <div class="dashboard-widgets__grid">
      <EquipmentWidget
        :summary="summary.equipment"
        :series="series.equipment"
        :range="settings.equipmentRange"
        @update-range="updateRange('equipmentRange', 'equipment', $event)"
      />

      <AlarmWidget :summary="summary.alarms" />

      <OverduesWidget
        :summary="summary.overdues"
        :series="series.overdues"
        :range="settings.overduesRange"
        @update-range="updateRange('overduesRange', 'overdues', $event)"
      />

      <AlertsWidget
        :summary="summary.alerts"
        :series="series.alerts"
        :range="settings.alertsRange"
        @update-range="updateRange('alertsRange', 'alerts', $event)"
      />

      <ServiceLevelWidget
        :summary="summary.service_level"
        :thresholds="settings.serviceThresholds"
        @update-thresholds="updateThresholds"
      />
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import EquipmentWidget from './widgets/EquipmentWidget.vue'
import AlarmWidget from './widgets/AlarmWidget.vue'
import OverduesWidget from './widgets/OverduesWidget.vue'
import AlertsWidget from './widgets/AlertsWidget.vue'
import ServiceLevelWidget from './widgets/ServiceLevelWidget.vue'
import {
  clampDateRange,
  daysAgoYmd,
  loadWidgetSettings,
  saveWidgetSettings,
  todayYmd,
} from '../../js/utils/dashboardWidgetSettings'

const DEFAULT_SUMMARY = {
  equipment: { active: 0, inactive: 0 },
  alarms: { inbound_calls: 0, active_alarms: 0 },
  overdues: { periodic_calls: 0, local_checks: 0 },
  alerts: { critical: 0, non_critical: 0 },
  service_level: { automated_checks: 0, physical_checks: 0 },
}

export default {
  name: 'DashboardWidgetsPage',
  components: {
    EquipmentWidget,
    AlarmWidget,
    OverduesWidget,
    AlertsWidget,
    ServiceLevelWidget,
  },
  data() {
    const equipmentRange = loadWidgetSettings('equipmentRange', {
      start: daysAgoYmd(120),
      end: todayYmd(),
    })
    const overduesRange = loadWidgetSettings('overduesRange', {
      start: daysAgoYmd(60),
      end: todayYmd(),
    })
    const alertsRange = loadWidgetSettings('alertsRange', {
      start: daysAgoYmd(120),
      end: todayYmd(),
    })

    return {
      summary: { ...DEFAULT_SUMMARY },
      series: {
        equipment: [],
        overdues: [],
        alerts: [],
      },
      settings: {
        equipmentRange,
        overduesRange,
        alertsRange,
        serviceThresholds: loadWidgetSettings('serviceThresholds', {
          redMax: 75,
          orangeMax: 90,
        }),
      },
      pollHandle: null,
    }
  },
  async mounted() {
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
    async fetchSeries(widget) {
      const rangeMap = {
        equipment: this.settings.equipmentRange,
        overdues: this.settings.overduesRange,
        alerts: this.settings.alertsRange,
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
    async updateRange(settingKey, widget, nextRange) {
      const fallback = this.settings[settingKey]
      const clamped = clampDateRange(nextRange, fallback)
      this.$set(this.settings, settingKey, clamped)
      saveWidgetSettings(settingKey, clamped)
      await this.fetchSeries(widget)
    },
    updateThresholds(nextThresholds) {
      const redMax = Math.max(0, Math.min(100, Number(nextThresholds.redMax || 75)))
      const orangeMax = Math.max(redMax, Math.min(100, Number(nextThresholds.orangeMax || 90)))
      const thresholds = { redMax, orangeMax }

      this.$set(this.settings, 'serviceThresholds', thresholds)
      saveWidgetSettings('serviceThresholds', thresholds)
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
