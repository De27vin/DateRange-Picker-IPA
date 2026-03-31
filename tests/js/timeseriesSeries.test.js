import { buildLiveSeriesRow, normalizeSeriesRow, normalizeSeriesRows } from '../../resources/js/utils/timeseriesSeries.js'

function assert(condition, message) {
  if (!condition) {
    throw new Error(message)
  }
}

console.log('Running timeseries series tests...')

{
  const row = normalizeSeriesRow({
    ts: '2026-01-24T00:00:00Z',
    series: {
      enabled: 74.4,
      disabled: '26',
    },
  }, ['enabled', 'disabled', 'missing'])

  assert(row.timestamp === '2026-01-24T00:00:00Z', 'timestamp should be preserved')
  assert(row.enabled === 74, 'enabled should be rounded and preserved')
  assert(row.disabled === 26, 'disabled should coerce numeric strings')
  assert(row.missing === 0, 'missing keys should default to zero')
}

{
  const rows = normalizeSeriesRows([
    { ts: '2026-01-24T02:00:00Z', series: { active_alarm: 2 } },
    { ts: '2026-01-24T01:00:00Z', series: { active_alarm: 1 } },
  ], ['active_alarm'])

  assert(rows[0].timestamp === '2026-01-24T01:00:00Z', 'rows should be sorted by timestamp ascending')
  assert(rows[1].active_alarm === 2, 'series values should be mapped after sorting')
}

{
  const live = buildLiveSeriesRow({
    battery_low: 101,
    network_malfunction: -5,
  })

  assert(live.timestamp === null, 'live row timestamp should be null')
  assert(live.battery_low === 100, 'live values should be capped at 100')
  assert(live.network_malfunction === 0, 'live values should floor at zero')
}

console.log('All timeseries series tests passed.')
