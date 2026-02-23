import { aggregateTimeseries, pickResolution } from '../../resources/js/utils/timeseriesAggregation.js'

function assert(condition, message) {
  if (!condition) {
    throw new Error(message)
  }
}

function p(ts, value) {
  return { ts, value }
}

console.log('Running aggregation tests...')

// pick resolution tests
assert(pickResolution(1) === '1h', 'Resolution 1 day should be 1h')
assert(pickResolution(3) === '6h', 'Resolution 3 days should be 6h')
assert(pickResolution(20) === '1d', 'Resolution 20 days should be 1d')
assert(pickResolution(100) === '1w', 'Resolution 100 days should be 1w')

// clamp and average test
{
  const points = [
    p('2026-01-10T00:10:00.000Z', -50),
    p('2026-01-10T00:20:00.000Z', 200),
  ]

  const out = aggregateTimeseries(points, {
    start: '2026-01-10',
    end: '2026-01-10',
  })

  assert(out.length === 1, 'Should produce 1 bucket')
  assert(out[0].value === 50, 'Clamp + avg should result in 50')
}

// 6h bucket test
{
  const points = [
    p('2026-01-10T06:10:00.000Z', 10),
    p('2026-01-10T11:59:00.000Z', 14), 
  ]

  const out = aggregateTimeseries(points, {
    start: '2026-01-10',
    end: '2026-01-12',
  })

  assert(out.length === 1, 'Should group into one 6h bucket')
  assert(out[0].value === 12, 'Average should be 12')
}

// sorting test
{
  const points = [
    p('2026-01-10T12:00:00.000Z', 1),
    p('2026-01-10T00:00:00.000Z', 2),
  ]

  const out = aggregateTimeseries(points, {
    start: '2026-01-10',
    end: '2026-01-10',
  })

  assert(new Date(out[0].ts) < new Date(out[1].ts), 'Output must be sorted')
}

console.log('All aggregation tests passed.')