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
  return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), 23, 59, 59, 999))
}

export function validateAndNormalizeRange(rawStart, rawEnd) {
  const startUtc = normalizeStartUtc(rawStart)
  const endUtc = normalizeEndUtc(rawEnd)

  if (!startUtc || !endUtc) {
    return { ok: false, error: 'Start and end date are required.' }
  }

  if (endUtc.getTime() < startUtc.getTime()) {
    return { ok: false, error: 'End date must be the same day or after start date.' }
  }

  if ((endUtc.getTime() - startUtc.getTime()) > MAX_RANGE_MS) {
    return { ok: false, error: 'Date range must be 365 days or less.' }
  }

  const nowUtc = new Date()
  if (endUtc.getTime() > nowUtc.getTime()) {
    return { ok: false, error: 'Future ranges are not allowed.' }
  }

  return { ok: true, startUtc, endUtc }
}

export function disableFutureUtc(day) {
  const endUtc = normalizeEndUtc(day)
  if (!endUtc) return false
  return endUtc.getTime() > Date.now()
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
