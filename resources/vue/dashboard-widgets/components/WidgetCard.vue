<template>
  <section class="widget-card">
    <header class="widget-card__header">
      <div>
        <h3 class="widget-card__title">{{ title }}</h3>
        <p v-if="subtitle" class="widget-card__subtitle">{{ subtitle }}</p>
      </div>

      <div v-if="configurable" class="widget-card__settings-control">
        <button
          type="button"
          class="widget-card__settings-button"
          @click="$emit('toggle-settings')"
        >
          <i class="f7-icons">gear_alt</i>
        </button>
        <p v-if="settingsError" class="widget-card__settings-error">{{ settingsError }}</p>
      </div>
    </header>

    <div v-if="$slots.settings" class="widget-card__settings">
      <slot name="settings" />
    </div>

    <div class="widget-card__body">
      <slot />
    </div>
  </section>
</template>

<script>
export default {
  name: 'WidgetCard',
  props: {
    title: {
      type: String,
      required: true,
    },
    subtitle: {
      type: String,
      default: '',
    },
    configurable: {
      type: Boolean,
      default: false,
    },
    settingsError: {
      type: String,
      default: '',
    },
  },
}
</script>

<style scoped>
.widget-card {
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 13rem;
  padding: 0.95rem 1rem 0.9rem;
  border-radius: 1rem;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 249, 252, 0.96)),
    #ffffff;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
}

.widget-card__header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
}

.widget-card__settings-control {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.35rem;
  max-width: 12rem;
}

.widget-card__title {
  margin: 0;
  color: #12243d;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.15;
}

.widget-card__subtitle {
  margin: 0.2rem 0 0;
  color: #64748b;
  font-size: 0.73rem;
  line-height: 1.25;
}

.widget-card__settings-button {
  width: 2.25rem;
  height: 2.25rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(148, 163, 184, 0.26);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.86);
  color: #355c8c;
  transition: background-color 120ms ease, transform 120ms ease;
}

.widget-card__settings-button:hover {
  background: #eef4fb;
  transform: translateY(-1px);
}

.widget-card__settings-error {
  margin: 0;
  color: #b42318;
  font-size: 0.7rem;
  line-height: 1.25;
  text-align: right;
}

.widget-card__settings {
  margin-top: 0.65rem;
}

.widget-card__body {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  margin-top: 0.6rem;
}
</style>
