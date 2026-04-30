<template>
  <div class="settings-panel">
    <div class="settings-panel__grid">
      <label class="settings-panel__field">
        <span>Back</span>
        <input v-model.number="draft.amount" type="number" min="1" max="365">
      </label>

      <label class="settings-panel__field">
        <span>Unit</span>
        <select v-model="draft.unit">
          <option value="days">Days</option>
          <option value="weeks">Weeks</option>
          <option value="months">Months</option>
          <option value="years">Years</option>
        </select>
      </label>
    </div>

    <div class="settings-panel__actions">
      <button type="button" class="settings-panel__button settings-panel__button--reset" @click="$emit('reset')">
        Reset
      </button>
      <button type="button" class="settings-panel__button settings-panel__button--ghost" @click="$emit('cancel')">
        Cancel
      </button>
      <button type="button" class="settings-panel__button settings-panel__button--primary" @click="apply">
        Apply
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DateRangeSettings',
  props: {
    value: {
      type: Object,
      required: true,
    },
    defaultValue: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      draft: { ...this.value },
    }
  },
  watch: {
    value: {
      deep: true,
      handler(nextValue) {
        this.draft = { ...nextValue }
      },
    },
  },
  methods: {
    apply() {
      this.$emit('apply', { ...this.draft })
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

.settings-panel__field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  color: #516273;
  font-size: 0.8rem;
  font-weight: 600;
}

.settings-panel__field input,
.settings-panel__field select {
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

.settings-panel__actions .settings-panel__button--reset {
  margin-right: auto;
}

.settings-panel__button {
  min-width: 5.5rem;
  min-height: 2.25rem;
  padding: 0 0.85rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
}

.settings-panel__button--ghost {
  border: 1px solid rgba(148, 163, 184, 0.34);
  color: #516273;
  background: #ffffff;
}

.settings-panel__button--reset {
  border: 1px solid rgba(220, 38, 38, 0.28);
  color: #b42318;
  background: #fff7f7;
}

.settings-panel__button--primary {
  border: 0;
  color: #ffffff;
  background: linear-gradient(135deg, #355c8c, #4b78a8);
}
</style>
