<template>
  <div class="dashboard-widgets">
    <div class="dashboard-widgets__grid">
      <EquipmentWidget
        :summary="summary.equipment"
        :series="series.equipment"
      />

      <AlarmWidget :summary="summary.alarms" />

      <OverduesWidget
        :summary="summary.overdues"
        :series="series.overdues"
      />

      <AlertsWidget
        :summary="summary.alerts"
        :series="series.alerts"
      />

      <ServiceLevelWidget
        :summary="summary.service_level"
        :thresholds="settings.serviceThresholds"
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
      pollHandle: null,
    }
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
