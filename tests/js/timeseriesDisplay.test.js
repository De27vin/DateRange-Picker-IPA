import { formatChartLabel } from '../../resources/js/utils/timeseriesDisplay.js'

function assert(condition, message) {
  if (!condition) {
    throw new Error(message)
  }
}

console.log('Running timeseries display tests...')

{
  const out = formatChartLabel(null, '1h', true)
  assert(out === 'Live', 'null timestamp should render as Live')
}

{
  const out = formatChartLabel('2026-01-24T13:45:00.000Z', '1h', true, {
    displayMode: 'utc',
  })
  assert(out === '13:45', 'UTC single-day hourly label should only show time')
}

{
  const out = formatChartLabel('2026-01-24T13:45:00.000Z', '1h', false, {
    displayMode: 'utc',
  })
  assert(out === '24.01 13:45', 'UTC multi-day hourly label should show date and time')
}

{
  const out = formatChartLabel('2026-01-24T13:45:00.000Z', '1d', false, {
    displayMode: 'utc',
  })
  assert(out === '24.01', 'UTC daily label should only show date')
}

{
  const out = formatChartLabel('2026-01-24T13:45:00.000Z', '1h', false, {
    displayMode: 'local',
    locale: 'en-GB',
    timeZone: 'Europe/Zurich',
  })
  assert(out === '24/01 14:45', 'Local mode should format in the requested local timezone')
}

{
  const out = formatChartLabel('2026-01-24T23:45:00.000Z', '1d', false, {
    displayMode: 'local',
    locale: 'en-GB',
    timeZone: 'Europe/Zurich',
  })
  assert(out === '25/01', 'Local daily label should reflect local calendar date for display')
}

console.log('All timeseries display tests passed.')
