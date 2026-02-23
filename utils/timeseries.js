export function normalizeHourlyTimeseries(input, options = {}) {

  const min = options.min ?? 0;
  const max = options.max ?? 100;
  const fill = options.fill ?? "null";

  // If multiple values exist for the same hour, the last one will be used
  const byHour = {};
  const rows = Array.isArray(input) ? input : [];

  // Group values by hour, applying min, max and deduplication
  for (let i = 0; i < rows.length; i++) {

    const row = rows[i];
    const d = new Date(row.ts);
    if (Number.isNaN(d.getTime())) continue;

    d.setUTCMinutes(0, 0, 0);
    const hourKey = d.toISOString();

    let value = Number(row.value);
    if (Number.isNaN(value)) continue;

    value = Math.round(value);
    if (value < min) value = min;
    if (value > max) value = max;

    byHour[hourKey] = value;
  }

  const keys = Object.keys(byHour).sort();
  if (keys.length === 0) return [];

  // Fill any missing hours between first and last timestamp.
  const out = [];
  let current = new Date(keys[0]);
  const end = new Date(keys[keys.length - 1]);
  let lastValue = null;

  while (current <= end) {
    const key = current.toISOString();

    if (Object.prototype.hasOwnProperty.call(byHour, key)) {
      const value = byHour[key];
      out.push({ ts: key, value });
      lastValue = value;
    } else {
      let value = null;
      if (fill === "zero") value = 0;
      if (fill === "forward-fill") value = lastValue;
      out.push({ ts: key, value });
    }

    current = new Date(current.getTime() + 60 * 60 * 1000);
  }

  return out;
}

