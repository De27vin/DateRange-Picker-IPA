const STORAGE_PREFIX = 'dashboard_widget_settings_v2:'

export const DASHBOARD_WIDGET_ERRORS = {
  INVALID_DATE_ORDER: 'Start date must be before or the same as the end date.',
  DATE_RANGE_TOO_LARGE: 'Date range cannot exceed 365 days.',
  INVALID_RANGE_AMOUNT: 'Range amount must be between 1 and 365.',
  INVALID_THRESHOLD_ORDER: 'Red area must be smaller than or the same as orange area.',
}

export const SYSTEM_DASHBOARD_WIDGET_DEFAULTS = {
  ranges: {
    equipment: { amount: 3, unit: 'months' },
    overdues: { amount: 3, unit: 'months' },
    alerts: { amount: 3, unit: 'months' },
  },
  serviceThresholds: {
    redMax: 75,
    orangeMax: 90,
  },
}

export const RANGE_UNITS = ['days', 'weeks', 'months', 'years']

const RANGE_UNIT_MAX = {
  days: 365,
  weeks: 52,
  months: 12,
  years: 1,
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

export function sanitizeRollingRange(range, fallback = SYSTEM_DASHBOARD_WIDGET_DEFAULTS.ranges.equipment) {
  const fallbackAmount = Number(fallback?.amount || 3)
  const fallbackUnit = fallback?.unit || 'months'
  const amount = Number(range?.amount)
  const unit = range?.unit

  const safeUnit = RANGE_UNITS.includes(unit) ? unit : fallbackUnit

  return {
    amount: Number.isFinite(amount) && amount >= 1
      ? Math.min(RANGE_UNIT_MAX[safeUnit], Math.round(amount))
      : fallbackAmount,
    unit: safeUnit,
  }
}

export function validateRollingRange(range) {
  const amount = Number(range?.amount)

  if (!RANGE_UNITS.includes(range?.unit)) {
    return DASHBOARD_WIDGET_ERRORS.INVALID_RANGE_AMOUNT
  }

  if (!Number.isFinite(amount) || amount < 1 || amount > RANGE_UNIT_MAX[range.unit]) {
    return DASHBOARD_WIDGET_ERRORS.INVALID_RANGE_AMOUNT
  }

  return null
}

function subtractMonthsClamped(date, months) {
  const year = date.getUTCFullYear()
  const month = date.getUTCMonth()
  const day = date.getUTCDate()
  const target = new Date(Date.UTC(year, month - months, 1))
  const lastDay = new Date(Date.UTC(target.getUTCFullYear(), target.getUTCMonth() + 1, 0)).getUTCDate()
  target.setUTCDate(Math.min(day, lastDay))

  return target
}

export function resolveRollingRange(range, now = new Date()) {
  const safeRange = sanitizeRollingRange(range)
  const end = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate()))
  let start = new Date(end.getTime())

  if (safeRange.unit === 'days') {
    start.setUTCDate(start.getUTCDate() - safeRange.amount)
  } else if (safeRange.unit === 'weeks') {
    start.setUTCDate(start.getUTCDate() - (safeRange.amount * 7))
  } else if (safeRange.unit === 'months') {
    start = subtractMonthsClamped(start, safeRange.amount)
  } else if (safeRange.unit === 'years') {
    start = subtractMonthsClamped(start, safeRange.amount * 12)
  }

  return {
    start: start.toISOString().slice(0, 10),
    end: end.toISOString().slice(0, 10),
  }
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
