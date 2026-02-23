function toDate(point) {
  const raw = point.ts ?? point.timestamp
  return raw ? new Date(raw) : null
}

function clamp0_100(n) {
  const x = Number(n)
  if (!Number.isFinite(x)) return 0
  return Math.max(0, Math.min(100, x))
}

function startOfDayUtc(d) {
  const x = new Date(d)
  x.setUTCHours(0, 0, 0, 0)
  return x
}

function startOfHourUtc(d) {
  const x = new Date(d)
  x.setUTCMinutes(0, 0, 0)
  return x
}

function startOf6HourBucketUtc(d) {
  const x = startOfHourUtc(d)
  const h = x.getUTCHours()
  const bucketH = Math.floor(h / 6) * 6
  x.setUTCHours(bucketH, 0, 0, 0)
  return x
}

// Monday-based week start (UTC)
function startOfWeekMondayUtc(d) {
  const x = startOfDayUtc(d)
  const day = x.getUTCDay() // 0=Sun, 1=Mon, ... , 6=Sat
  const diff = (day === 0) ? -6 : (1 - day) // shift back to Monday
  x.setUTCDate(x.getUTCDate() + diff)
  return x
}

function isoLikeKeyUtc(d) {
  // key for map grouping, stable, sortable (UTC)
  const y = d.getUTCFullYear()
  const m = String(d.getUTCMonth() + 1).padStart(2, '0')
  const day = String(d.getUTCDate()).padStart(2, '0')
  const h = String(d.getUTCHours()).padStart(2, '0')
  return `${y}-${m}-${day}T${h}:00`
}

// Granularity selection based on range (inclusive days)
export function pickResolution(rangeDays) {
  if (rangeDays <= 2) return '1h'
  if (rangeDays <= 14) return '6h'
  if (rangeDays <= 60) return '1d'
  return '1w' // up to 365
}

export function aggregateTimeseries(points, { start, end } = {}) {
  if (!Array.isArray(points) || points.length === 0) return []

  // IMPORTANT: interpret start/end as UTC day boundaries to avoid timezone drift.
  // If start/end are 'YYYY-MM-DD', JS parses them as UTC in modern engines.
  const s = start ? startOfDayUtc(new Date(start)) : null
  const e = end ? startOfDayUtc(new Date(end)) : null

  // inclusive days
  let rangeDays = 0
  if (s && e) {
    const msPerDay = 24 * 60 * 60 * 1000
    rangeDays = Math.floor((e.getTime() - s.getTime()) / msPerDay) + 1
  }

  const resolution = pickResolution(rangeDays)

  const bucketStartFn =
    resolution === '1h' ? startOfHourUtc :
    resolution === '6h' ? startOf6HourBucketUtc :
    resolution === '1d' ? startOfDayUtc :
    startOfWeekMondayUtc

  const map = new Map()

  for (const p of points) {
    const d = toDate(p)
    if (!d || Number.isNaN(d.getTime())) continue

    // filter strictly within range (UTC inclusive)
    if (s && d < s) continue
    if (e) {
      const endInclusive = new Date(e)
      endInclusive.setUTCHours(23, 59, 59, 999)
      if (d > endInclusive) continue
    }

    const bucketStart = bucketStartFn(d)
    const key = isoLikeKeyUtc(bucketStart)

    const value = clamp0_100(p.value ?? p.enabled)

    const entry = map.get(key)
    if (!entry) {
      map.set(key, {
        ts: bucketStart.toISOString(), // UTC ISO
        sum: value,
        count: 1,
      })
    } else {
      entry.sum += value
      entry.count += 1
    }
  }

  return Array.from(map.values())
    .sort((a, b) => new Date(a.ts).getTime() - new Date(b.ts).getTime())
    .map(x => ({
      ts: x.ts,
      value: Math.round(x.sum / x.count),
    }))
}