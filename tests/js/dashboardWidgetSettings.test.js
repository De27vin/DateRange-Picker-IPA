import {
  DASHBOARD_WIDGET_ERRORS,
  resolveRollingRange,
  sanitizeRollingRange,
  validateDateRange,
  validateRollingRange,
  validateServiceThresholds,
} from '../../resources/js/utils/dashboardWidgetSettings.js'

function assert(condition, message) {
  if (!condition) {
    throw new Error(message)
  }
}

console.log('Running dashboard widget settings tests...')

{
  const error = validateDateRange({
    start: '2026-04-10',
    end: '2026-04-09',
  })

  assert(
    error === DASHBOARD_WIDGET_ERRORS.INVALID_DATE_ORDER,
    'start date after end date should fail validation'
  )
}

{
  const error = validateDateRange({
    start: '2025-01-01',
    end: '2026-01-02',
  })

  assert(
    error === DASHBOARD_WIDGET_ERRORS.DATE_RANGE_TOO_LARGE,
    'date range over 365 days should fail validation'
  )
}

{
  const error = validateDateRange({
    start: '2025-01-01',
    end: '2026-01-01',
  })

  assert(error === null, 'date range of exactly 365 days should be allowed')
}

{
  const error = validateRollingRange({
    amount: 0,
    unit: 'months',
  })

  assert(
    error === DASHBOARD_WIDGET_ERRORS.INVALID_RANGE_AMOUNT,
    'rolling range amount below 1 should fail validation'
  )
}

{
  const error = validateRollingRange({
    amount: 2,
    unit: 'years',
  })

  assert(
    error === DASHBOARD_WIDGET_ERRORS.INVALID_RANGE_AMOUNT,
    'rolling range should not allow ranges larger than 365 days'
  )
}

{
  const error = validateRollingRange({
    amount: 6,
    unit: 'months',
  })

  assert(error === null, 'valid rolling range should pass validation')
}

{
  const range = resolveRollingRange(
    { amount: 3, unit: 'months' },
    new Date('2026-04-30T12:00:00Z')
  )

  assert(range.start === '2026-01-30', '3 months back should resolve to 2026-01-30')
  assert(range.end === '2026-04-30', 'rolling range end should resolve to today')
}

{
  const range = resolveRollingRange(
    { amount: 1, unit: 'months' },
    new Date('2026-03-31T12:00:00Z')
  )

  assert(range.start === '2026-02-28', 'month subtraction should clamp to the target month end')
}

{
  const range = sanitizeRollingRange({ start: '2026-01-01', end: '2026-04-01' }, { amount: 3, unit: 'months' })

  assert(range.amount === 3 && range.unit === 'months', 'legacy fixed date ranges should fall back to rolling defaults')
}

{
  const error = validateServiceThresholds({
    redMax: 85,
    orangeMax: 80,
  })

  assert(
    error === DASHBOARD_WIDGET_ERRORS.INVALID_THRESHOLD_ORDER,
    'red threshold above orange threshold should fail validation'
  )
}

{
  const error = validateServiceThresholds({
    redMax: 85,
    orangeMax: 85,
  })

  assert(error === null, 'equal red and orange thresholds should be allowed')
}

console.log('All dashboard widget settings tests passed.')
