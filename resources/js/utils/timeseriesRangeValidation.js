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
  return new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0, 0)
}

export function normalizeEndUtc(raw) {
  const date = toDate(raw)
  if (!date) return null
  return new Date(date.getFullYear(), date.getMonth(), date.getDate(), 23, 0, 0, 0)
}

function startOfLocalDay(date) {
  return new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0, 0)
}

function isSameLocalDay(a, b) {
  return a.getFullYear() === b.getFullYear()
    && a.getMonth() === b.getMonth()
    && a.getDate() === b.getDate()
}

function floorLocalHour(date) {
  const floored = new Date(date)
  floored.setMinutes(0, 0, 0)

  return floored
}

export function validateAndNormalizeRange(rawStart, rawEnd) {
  const startUtc = normalizeStartUtc(rawStart)
  const requestedEndUtc = normalizeEndUtc(rawEnd)

  if (!startUtc || !requestedEndUtc) {
    return { ok: false, error: 'Start and end date are required.' }
  }

  const now = new Date()
  const todayStart = startOfLocalDay(now)
  const requestedEndStart = startOfLocalDay(requestedEndUtc)

  // Future local calendar days stay blocked, today is allowed.
  if (requestedEndStart.getTime() > todayStart.getTime()) {
    return { ok: false, error: 'Future ranges are not allowed.' }
  }

  let endUtc = requestedEndUtc
  if (isSameLocalDay(requestedEndUtc, now)) {
    endUtc = floorLocalHour(now)
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

  const selectedStartUtc = startOfLocalDay(selected)
  const todayStartUtc = startOfLocalDay(new Date())

  return selectedStartUtc.getTime() > todayStartUtc.getTime()
}

export function daysInRangeUtc(startUtc, endUtc) {
  return Math.floor((endUtc.getTime() - startUtc.getTime()) / MS_PER_DAY) + 1
}

export function toIso8601Utc(date) {
  return date.toISOString()
}

export function toYmdUtc(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}
