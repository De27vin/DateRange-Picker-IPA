const STORAGE_PREFIX = 'dashboard_widget_settings_v2:'

export const DASHBOARD_WIDGET_ERRORS = {
  INVALID_DATE_ORDER: 'Start date must be before or the same as the end date.',
  DATE_RANGE_TOO_LARGE: 'Date range cannot exceed 365 days.',
  INVALID_THRESHOLD_ORDER: 'Red area must be smaller than or the same as orange area.',
}

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

export function validateDateRange(range) {
  const start = range?.start
  const end = range?.end

  if (!start || !end) {
    return null
  }

  if (start > end) {
    return DASHBOARD_WIDGET_ERRORS.INVALID_DATE_ORDER
  }

  const startDate = new Date(`${start}T00:00:00Z`)
  const endDate = new Date(`${end}T00:00:00Z`)

  if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) {
    return DASHBOARD_WIDGET_ERRORS.INVALID_DATE_ORDER
  }

  const diffDays = Math.floor((endDate.getTime() - startDate.getTime()) / 86400000)
  if (diffDays > 365) {
    return DASHBOARD_WIDGET_ERRORS.DATE_RANGE_TOO_LARGE
  }

  return null
}

export function validateServiceThresholds(thresholds) {
  const redMax = Number(thresholds?.redMax)
  const orangeMax = Number(thresholds?.orangeMax)

  if (Number.isNaN(redMax) || Number.isNaN(orangeMax)) {
    return null
  }

  if (redMax > orangeMax) {
    return DASHBOARD_WIDGET_ERRORS.INVALID_THRESHOLD_ORDER
  }

  return null
}
