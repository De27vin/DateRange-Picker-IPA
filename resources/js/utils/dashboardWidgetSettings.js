const STORAGE_PREFIX = 'dashboard_widget_settings_v2:'

export function loadWidgetSettings(key, fallback) {
  if (typeof window === 'undefined' || !window.localStorage) {
    return fallback
  }

  try {
    const raw = window.localStorage.getItem(`${STORAGE_PREFIX}${key}`)
    if (!raw) return fallback
    return { ...fallback, ...JSON.parse(raw) }
  } catch (error) {
    return fallback
  }
}

export function saveWidgetSettings(key, value) {
  if (typeof window === 'undefined' || !window.localStorage) {
    return
  }

  try {
    window.localStorage.setItem(`${STORAGE_PREFIX}${key}`, JSON.stringify(value))
  } catch (error) {
    // Ignore localStorage failures in restricted browsers.
  }
}

export function todayYmd() {
  const now = new Date()
  return now.toISOString().slice(0, 10)
}

export function daysAgoYmd(days) {
  const date = new Date()
  date.setUTCDate(date.getUTCDate() - days)
  return date.toISOString().slice(0, 10)
}

export function clampDateRange(range, fallback) {
  const start = range?.start || fallback.start
  const end = range?.end || fallback.end

  if (start > end) {
    return fallback
  }

  return { start, end }
}
