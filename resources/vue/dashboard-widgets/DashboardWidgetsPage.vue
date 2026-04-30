<template>
  <div class="dashboard-widgets">
    <div class="dashboard-widgets__grid">
      <EquipmentWidget
        :summary="summary.equipment"
        :series="series.equipment"
        :range="settings.equipmentRange"
        :default-range="defaultSettings.equipmentRange"
        :error-message="errors.equipment"
        @update-range="updateRange('equipmentRange', 'equipment', $event)"
        @reset-range="resetRange('equipmentRange', 'equipment')"
      />

      <AlarmWidget :summary="summary.alarms" />

      <OverduesWidget
        :summary="summary.overdues"
        :series="series.overdues"
        :range="settings.overduesRange"
        :default-range="defaultSettings.overduesRange"
        :error-message="errors.overdues"
        @update-range="updateRange('overduesRange', 'overdues', $event)"
        @reset-range="resetRange('overduesRange', 'overdues')"
      />

      <AlertsWidget
        :summary="summary.alerts"
        :series="series.alerts"
        :range="settings.alertsRange"
        :default-range="defaultSettings.alertsRange"
        :error-message="errors.alerts"
        @update-range="updateRange('alertsRange', 'alerts', $event)"
        @reset-range="resetRange('alertsRange', 'alerts')"
      />

      <ServiceLevelWidget
        :summary="summary.service_level"
        :thresholds="settings.serviceThresholds"
        :default-thresholds="defaultSettings.serviceThresholds"
        :error-message="errors.serviceLevel"
        @update-thresholds="updateThresholds"
        @reset-thresholds="resetThresholds"
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
  loadWidgetSettings,
  resolveRollingRange,
  saveWidgetSettings,
  sanitizeRollingRange,
  SYSTEM_DASHBOARD_WIDGET_DEFAULTS,
  validateRollingRange,
  validateServiceThresholds,
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
      defaultSettings: systemDefaults,
      settings: {
        ...systemDefaults,
      },
      errors: {
        equipment: '',
        overdues: '',
        alerts: '',
        serviceLevel: '',
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

        this.defaultSettings = accountDefaults
        this.settings = {
          equipmentRange: loadWidgetSettings('equipmentRange', accountDefaults.equipmentRange),
          overduesRange: loadWidgetSettings('overduesRange', accountDefaults.overduesRange),
          alertsRange: loadWidgetSettings('alertsRange', accountDefaults.alertsRange),
          serviceThresholds: loadWidgetSettings('serviceThresholds', accountDefaults.serviceThresholds),
        }
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
    async updateRange(settingKey, widget, nextRange) {
      const error = validateRollingRange(nextRange)
      if (error) {
        this.$set(this.errors, widget, error)
        return
      }

      this.$set(this.errors, widget, '')
      const clamped = sanitizeRollingRange(nextRange, this.settings[settingKey])
      this.$set(this.settings, settingKey, clamped)
      saveWidgetSettings(settingKey, clamped)
      await this.fetchSeries(widget)
    },
    async resetRange(settingKey, widget) {
      const defaultRange = this.defaultSettings[settingKey]
      this.$set(this.errors, widget, '')
      this.$set(this.settings, settingKey, defaultRange)
      saveWidgetSettings(settingKey, defaultRange)
      await this.fetchSeries(widget)
    },
    updateThresholds(nextThresholds) {
      const error = validateServiceThresholds(nextThresholds)
      if (error) {
        this.$set(this.errors, 'serviceLevel', error)
        return
      }

      const redMax = Math.max(0, Math.min(100, Number(nextThresholds.redMax || 75)))
      const orangeMax = Math.max(redMax, Math.min(100, Number(nextThresholds.orangeMax || 90)))
      const thresholds = { redMax, orangeMax }

      this.$set(this.errors, 'serviceLevel', '')
      this.$set(this.settings, 'serviceThresholds', thresholds)
      saveWidgetSettings('serviceThresholds', thresholds)
    },
    resetThresholds() {
      this.$set(this.errors, 'serviceLevel', '')
      this.$set(this.settings, 'serviceThresholds', { ...this.defaultSettings.serviceThresholds })
      saveWidgetSettings('serviceThresholds', this.defaultSettings.serviceThresholds)
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
