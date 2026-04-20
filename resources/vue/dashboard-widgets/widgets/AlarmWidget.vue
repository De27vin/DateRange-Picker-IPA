<template>
  <WidgetCard title="Alarms" subtitle="Current state only.">
    <div class="alarm-widget compact-widget">
      <div class="compact-widget__top">
        <div class="alarm-widget__preview">
          <DashboardGauge :sections="sections" :max="maxGaugeValue" />
        </div>
      </div>

      <div class="compact-metrics">
        <div class="compact-metrics__labels">
          <div>Inbound calls</div>
          <div>Active alarms</div>
        </div>
        <div class="compact-metrics__values">
          <div>{{ summary.inbound_calls }}</div>
          <div>{{ summary.active_alarms }}</div>
        </div>
      </div>
    </div>
  </WidgetCard>
</template>

<script>
import WidgetCard from '../components/WidgetCard.vue'
import DashboardGauge from '../components/DashboardGauge.vue'

export default {
  name: 'AlarmWidget',
  components: {
    WidgetCard,
    DashboardGauge,
  },
  props: {
    summary: {
      type: Object,
      required: true,
    },
  },
  computed: {
    maxGaugeValue() {
      return Math.max(1, this.summary.inbound_calls + this.summary.active_alarms)
    },
    sections() {
      const inbound = Number(this.summary.inbound_calls || 0)
      const active = Number(this.summary.active_alarms || 0)

      return [
        { from: 0, to: inbound, color: '#4b78a8' },
        { from: inbound, to: inbound + active, color: '#b42318' },
      ]
    },
  },
}
</script>

<style scoped>
.alarm-widget {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  height: 100%;
}

.compact-widget__top {
  display: flex;
  justify-content: flex-end;
}

.alarm-widget__preview {
  width: 11rem;
  margin-top: -0.1rem;
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
</style>
