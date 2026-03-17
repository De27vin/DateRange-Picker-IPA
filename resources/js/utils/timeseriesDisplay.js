function formatUtcParts(date) {
  const dd = String(date.getUTCDate()).padStart(2, '0')
  const mm = String(date.getUTCMonth() + 1).padStart(2, '0')
  const yyyy = String(date.getUTCFullYear())
  const hh = String(date.getUTCHours()).padStart(2, '0')
  const min = String(date.getUTCMinutes()).padStart(2, '0')

  return {
    date: `${dd}.${mm}.${yyyy}`,
    shortDate: `${dd}.${mm}`,
    time: `${hh}:${min}`,
  }
}

function buildFormatters(options = {}) {
  const locale = options.locale
  const timeZone = options.timeZone

  return {
    date: new Intl.DateTimeFormat(locale, {
      day: '2-digit',
      month: '2-digit',
      timeZone,
    }),
    dateWithYear: new Intl.DateTimeFormat(locale, {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      timeZone,
    }),
    time: new Intl.DateTimeFormat(locale, {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false,
      timeZone,
    }),
  }
}

export function formatChartLabel(ts, resolution, isSingleDay, options = {}) {
  if (ts === null) return 'Live'

  const date = new Date(ts)
  if (Number.isNaN(date.getTime())) return ''

  const displayMode = options.displayMode ?? 'local'

  // Note: '1d' and '1w' labels are display-only. The underlying buckets remain UTC-based.
  if (displayMode === 'utc') {
    const parts = formatUtcParts(date)
    if (resolution === '1d' || resolution === '1w') return parts.shortDate
    if (isSingleDay && (resolution === '1h' || resolution === '6h')) return parts.time
    return `${parts.shortDate} ${parts.time}`
  }

  const formatters = buildFormatters(options)
  if (resolution === '1d' || resolution === '1w') return formatters.date.format(date)
  if (isSingleDay && (resolution === '1h' || resolution === '6h')) return formatters.time.format(date)
  return `${formatters.date.format(date)} ${formatters.time.format(date)}`
}

export function formatChartTooltip(ts, options = {}) {
  if (ts === null) return 'Live'

  const date = new Date(ts)
  if (Number.isNaN(date.getTime())) return ''

  const displayMode = options.displayMode ?? 'local'
  if (displayMode === 'utc') {
    const parts = formatUtcParts(date)
    return `${parts.date} ${parts.time} UTC`
  }

  const formatters = buildFormatters(options)
  return `${formatters.dateWithYear.format(date)} ${formatters.time.format(date)}`
}
