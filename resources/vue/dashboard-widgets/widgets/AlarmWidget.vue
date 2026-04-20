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
    inbound() {
      return Math.max(0, Number(this.summary.inbound_calls || 0))
    },
    active() {
      return Math.max(0, Number(this.summary.active_alarms || 0))
    },
    snippetPercent() {
      return 4
    },
    sectionPercentages() {
      const total = this.inbound + this.active

      if (total <= 0) {
        return {
          inbound: 50,
          active: 50,
        }
      }

      if (this.inbound <= 0) {
        return {
          inbound: this.snippetPercent,
          active: 100 - this.snippetPercent,
        }
      }

      if (this.active <= 0) {
        return {
          inbound: 100 - this.snippetPercent,
          active: this.snippetPercent,
        }
      }

      return {
        inbound: (this.inbound / total) * 100,
        active: (this.active / total) * 100,
      }
    },
    maxGaugeValue() {
      return 100
    },
    sections() {
      const inboundPercent = this.sectionPercentages.inbound

      return [
        { from: 0, to: inboundPercent, color: '#4b78a8' },
        { from: inboundPercent, to: 100, color: '#b42318' },
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
