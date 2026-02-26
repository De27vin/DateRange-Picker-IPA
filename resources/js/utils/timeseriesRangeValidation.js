const MS_PER_DAY = 24 * 60 * 60 * 1000
export const MAX_RANGE_MS = 365 * MS_PER_DAY

function toDate(value) {
  if (!value) return null
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? null : date
}

export function normalizeStartUtc(raw) {
  const date = toDate(raw)
  if (!date) return null
  return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0, 0))
}

export function normalizeEndUtc(raw) {
  const date = toDate(raw)
  if (!date) return null
  return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), 23, 0, 0, 0))
}

function startOfUtcDay(date) {
  return new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), 0, 0, 0, 0))
}

function isSameUtcDay(a, b) {
  return a.getUTCFullYear() === b.getUTCFullYear()
    && a.getUTCMonth() === b.getUTCMonth()
    && a.getUTCDate() === b.getUTCDate()
}

function floorUtcHour(date) {
  return new Date(Date.UTC(
    date.getUTCFullYear(),
    date.getUTCMonth(),
    date.getUTCDate(),
    date.getUTCHours(),
    0,
    0,
    0
  ))
}

export function validateAndNormalizeRange(rawStart, rawEnd) {
  const startUtc = normalizeStartUtc(rawStart)
  const requestedEndUtc = normalizeEndUtc(rawEnd)

  if (!startUtc || !requestedEndUtc) {
    return { ok: false, error: 'Start and end date are required.' }
  }

  const nowUtc = new Date()
  const todayStartUtc = startOfUtcDay(nowUtc)
  const requestedEndStartUtc = startOfUtcDay(requestedEndUtc)

  // Future days stay blocked, today is allowed.
  if (requestedEndStartUtc.getTime() > todayStartUtc.getTime()) {
    return { ok: false, error: 'Future ranges are not allowed.' }
  }

  let endUtc = requestedEndUtc
  if (isSameUtcDay(requestedEndUtc, nowUtc)) {
    endUtc = floorUtcHour(nowUtc)
  }

  if (endUtc.getTime() < startUtc.getTime()) {
    return { ok: false, error: 'End date must be the same day or after start date.' }
  }

  if ((endUtc.getTime() - startUtc.getTime()) > MAX_RANGE_MS) {
    return { ok: false, error: 'Date range must be 365 days or less.' }
  }

  return { ok: true, startUtc, endUtc }
}

export function disableFutureUtc(day) {
  const selected = toDate(day)
  if (!selected) return false

  const selectedStartUtc = new Date(Date.UTC(
    selected.getFullYear(),
    selected.getMonth(),
    selected.getDate(),
    0,
    0,
    0,
    0
  ))
  const todayStartUtc = startOfUtcDay(new Date())

  return selectedStartUtc.getTime() > todayStartUtc.getTime()
}

export function daysInRangeUtc(startUtc, endUtc) {
  return Math.floor((endUtc.getTime() - startUtc.getTime()) / MS_PER_DAY) + 1
}

export function toIso8601Utc(date) {
  return date.toISOString()
}

export function toYmdUtc(date) {
  return date.toISOString().slice(0, 10)
}
