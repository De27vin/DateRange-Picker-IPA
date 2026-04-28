<template>
  <WidgetCard title="Service Level" subtitle="Threshold-based interpretation." configurable :settings-error="errorMessage" @toggle-settings="settingsOpen = !settingsOpen">
    <template v-if="settingsOpen" #settings>
      <div class="settings-panel">
        <div class="settings-panel__grid">
          <label class="settings-field">
            <span>Red max</span>
            <input v-model.number="draftThresholds.redMax" type="number" min="0" max="100">
          </label>
          <label class="settings-field">
            <span>Orange max</span>
            <input v-model.number="draftThresholds.orangeMax" type="number" min="0" max="100">
          </label>
        </div>
        <div class="settings-panel__actions">
          <button type="button" class="settings-button settings-button--ghost" @click="cancelSettings">Cancel</button>
          <button type="button" class="settings-button settings-button--primary" @click="applyThresholds">Apply</button>
        </div>
      </div>
    </template>

    <div class="widget-layout">
      <div class="compact-widget__top">
        <div class="gauge-preview">
          <div class="gauge-preview__item">
            <DashboardGauge :sections="sections" :value="summary.automated_checks" :show-needle="true" />
          </div>
          <div class="gauge-preview__item">
            <DashboardGauge :sections="sections" :value="summary.physical_checks" :show-needle="true" />
          </div>
        </div>
      </div>

      <div class="threshold-legend">
        <span><i class="legend-dot" style="background:#dc2626"></i> 0-{{ safeThresholds.redMax }}%</span>
        <span><i class="legend-dot" style="background:#f59e0b"></i> {{ safeThresholds.redMax }}-{{ safeThresholds.orangeMax }}%</span>
        <span><i class="legend-dot" style="background:#16a34a"></i> {{ safeThresholds.orangeMax }}-100%</span>
      </div>

      <div class="compact-metrics">
        <div class="compact-metrics__labels">
          <div>Automated checks</div>
          <div>Physical checks</div>
        </div>
        <div class="compact-metrics__values">
          <div>{{ summary.automated_checks }}%</div>
          <div>{{ summary.physical_checks }}%</div>
        </div>
      </div>
    </div>
  </WidgetCard>
</template>

<script>
import WidgetCard from '../components/WidgetCard.vue'
import DashboardGauge from '../components/DashboardGauge.vue'

export default {
  name: 'ServiceLevelWidget',
  components: {
    WidgetCard,
    DashboardGauge,
  },
  props: {
    summary: {
      type: Object,
      required: true,
    },
    thresholds: {
      type: Object,
      required: true,
    },
    errorMessage: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      settingsOpen: false,
      draftThresholds: { ...this.thresholds },
    }
  },
  watch: {
    thresholds: {
      deep: true,
      handler(nextValue) {
        this.draftThresholds = { ...nextValue }
      },
    },
  },
  computed: {
    safeThresholds() {
      const redMax = Math.max(0, Math.min(100, Number(this.thresholds.redMax || 75)))
      const orangeMax = Math.max(redMax, Math.min(100, Number(this.thresholds.orangeMax || 90)))

      return { redMax, orangeMax }
    },
    sections() {
      return [
        { from: 0, to: this.safeThresholds.redMax, color: '#dc2626' },
        { from: this.safeThresholds.redMax, to: this.safeThresholds.orangeMax, color: '#f59e0b' },
        { from: this.safeThresholds.orangeMax, to: 100, color: '#16a34a' },
      ]
    },
  },
  methods: {
    cancelSettings() {
      this.settingsOpen = false
      this.draftThresholds = { ...this.thresholds }
    },
    applyThresholds() {
      this.settingsOpen = false
      this.$emit('update-thresholds', { ...this.draftThresholds })
    },
  },
}
</script>

<style scoped>
.settings-panel {
  padding: 0.9rem;
  border-radius: 1rem;
  background: #f8fbff;
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.settings-panel__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}

.settings-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  color: #516273;
  font-size: 0.8rem;
  font-weight: 600;
}

.settings-field input {
  min-height: 2.35rem;
  padding: 0.45rem 0.65rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.32);
  background: #ffffff;
  color: #12243d;
}

.settings-panel__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  margin-top: 0.8rem;
}

.settings-button {
  min-width: 5.5rem;
  min-height: 2.25rem;
  padding: 0 0.85rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
}

.settings-button--ghost {
  border: 1px solid rgba(148, 163, 184, 0.34);
  color: #516273;
  background: #ffffff;
}

.settings-button--primary {
  border: 0;
  color: #ffffff;
  background: linear-gradient(135deg, #355c8c, #4b78a8);
}

.widget-layout {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  height: 100%;
}

.compact-widget__top {
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  gap: 0.35rem;
}

.gauge-preview {
  width: 11rem;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.35rem;
  align-items: flex-end;
}

.gauge-preview__item {
  display: flex;
  align-items: center;
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

.threshold-legend {
  display: flex;
  justify-content: flex-end;
  gap: 0.7rem;
  flex-wrap: wrap;
  margin-top: 0.05rem;
  color: #64748b;
  font-size: 0.68rem;
  font-weight: 600;
}

.threshold-legend span {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.legend-dot {
  width: 0.58rem;
  height: 0.58rem;
  border-radius: 999px;
  display: inline-block;
}
</style>
