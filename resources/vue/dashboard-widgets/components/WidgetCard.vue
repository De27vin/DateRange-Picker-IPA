<template>
  <section class="widget-card">
    <header class="widget-card__header">
      <div>
        <h3 class="widget-card__title">{{ title }}</h3>
        <p v-if="subtitle" class="widget-card__subtitle">{{ subtitle }}</p>
      </div>

    </header>

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

.widget-card__body {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  margin-top: 0.6rem;
}
</style>
