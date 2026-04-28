import {
  DASHBOARD_WIDGET_ERRORS,
  validateDateRange,
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
