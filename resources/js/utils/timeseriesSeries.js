function clamp0To100(value) {
  const numeric = Number(value)
  if (!Number.isFinite(numeric)) {
    return 0
  }

  return Math.max(0, Math.min(100, Math.round(numeric)))
}

export function normalizeSeriesRow(row, keys) {
  const base = {
    timestamp: row?.ts ?? null,
  }

  const rowSeries = row?.series ?? {}
  keys.forEach((key) => {
    base[key] = clamp0To100(rowSeries[key])
  })

  return base
}

export function normalizeSeriesRows(rows, keys) {
  return (rows ?? [])
    .slice()
    .sort((a, b) => String(a?.ts ?? '').localeCompare(String(b?.ts ?? '')))
    .map((row) => normalizeSeriesRow(row, keys))
}

export function buildLiveSeriesRow(valuesByKey) {
  const row = { timestamp: null }

  Object.entries(valuesByKey).forEach(([key, value]) => {
    row[key] = clamp0To100(value)
  })

  return row
}
