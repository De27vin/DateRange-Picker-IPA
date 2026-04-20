<template>
  <svg class="dashboard-gauge" viewBox="0 0 200 120" aria-hidden="true">
    <path
      v-for="section in normalizedSections"
      :key="section.key"
      :d="describeArc(section.from, section.to)"
      fill="none"
      :stroke="section.color"
      stroke-width="16"
      stroke-linecap="round"
    />

    <path
      d="M 24 100 A 76 76 0 0 1 176 100"
      fill="none"
      stroke="rgba(148, 163, 184, 0.18)"
      stroke-width="4"
      stroke-linecap="round"
    />

    <line
      v-if="showNeedle"
      :x1="100"
      :y1="100"
      :x2="needlePoint.x"
      :y2="needlePoint.y"
      stroke="#12243d"
      stroke-width="4"
      stroke-linecap="round"
    />
    <circle v-if="showNeedle" cx="100" cy="100" r="6" fill="#12243d" />
  </svg>
</template>

<script>
export default {
  name: 'DashboardGauge',
  props: {
    sections: {
      type: Array,
      required: true,
    },
    max: {
      type: Number,
      default: 100,
    },
    value: {
      type: Number,
      default: 0,
    },
    showNeedle: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    normalizedSections() {
      return this.sections.map((section, index) => ({
        ...section,
        key: `${index}-${section.from}-${section.to}-${section.color}`,
      }))
    },
    needlePoint() {
      const clamped = Math.max(0, Math.min(this.max, Number(this.value) || 0))
      const angle = Math.PI - ((clamped / this.max) * Math.PI)
      const radius = 60

      return {
        x: 100 + (Math.cos(angle) * radius),
        y: 100 - (Math.sin(angle) * radius),
      }
    },
  },
  methods: {
    polarToCartesian(angle) {
      return {
        x: 100 + (Math.cos(angle) * 76),
        y: 100 - (Math.sin(angle) * 76),
      }
    },
    describeArc(fromValue, toValue) {
      const safeFrom = Math.max(0, Math.min(this.max, Number(fromValue) || 0))
      let safeTo = Math.max(0, Math.min(this.max, Number(toValue) || 0))

      if (safeTo <= safeFrom) {
        safeTo = Math.min(this.max, safeFrom + Math.max(this.max * 0.01, 0.5))
      }

      const startAngle = Math.PI - ((safeFrom / this.max) * Math.PI)
      const endAngle = Math.PI - ((safeTo / this.max) * Math.PI)
      const start = this.polarToCartesian(startAngle)
      const end = this.polarToCartesian(endAngle)

      return `M ${start.x} ${start.y} A 76 76 0 0 1 ${end.x} ${end.y}`
    },
  },
}
</script>

<style scoped>
.dashboard-gauge {
  width: 100%;
  max-width: 8rem;
  height: auto;
  overflow: visible;
}
</style>
