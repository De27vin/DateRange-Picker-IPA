<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TimeseriesRequest extends FormRequest
{
    private const CHARTS = [
        'EquipmentChart',
        'AlarmChart',
        'AlertsChart',
        'ServiceLevelChart',
    ];

    private const ISO_8601_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2}(?:\.\d{1,6})?)?(?:Z|[+\-]\d{2}:\d{2})$/';
    private const LEGACY_DATE_REGEX = '/^\d{4}-\d{2}-\d{2}$/';
    private const MAX_RANGE_DAYS = 365;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chart' => [
                'required',
                'string',
                Rule::in(self::CHARTS),
            ],
            'start' => ['required', 'string'],
            'end'   => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $start = $this->input('start');
            $end   = $this->input('end');

            if (!$start || !$end) {
                return;
            }

            $s = $this->parseDateTimeInput($start, false);
            $e = $this->parseDateTimeInput($end, true);

            if (!$s) {
                $v->errors()->add('start', 'The start field must be a valid ISO 8601 datetime (for example 2026-01-24T00:00:00Z).');
            }
            if (!$e) {
                $v->errors()->add('end', 'The end field must be a valid ISO 8601 datetime (for example 2026-01-24T23:00:00Z).');
            }
            if (!$s || !$e) {
                return;
            }

            $s = $s->utc();
            $e = $this->clampEndToNowFloorHourUtc($e->utc());

            if ($e->lessThanOrEqualTo($s)) {
                $v->errors()->add('end', 'The end field must be after start.');
            }

            $rangeSeconds = $e->diffInSeconds($s);
            $maxSeconds = self::MAX_RANGE_DAYS * 24 * 60 * 60;
            if ($rangeSeconds > $maxSeconds) {
                $v->errors()->add('end', 'The selected range must be 365 days or less.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'chart.required' => 'The chart field is required.',
            'chart.string' => 'The chart field must be a string.',
            'chart.in' => 'The selected chart is invalid. Allowed values: ' . implode(', ', self::CHARTS) . '.',
            'start.required' => 'The start field is required.',
            'start.string' => 'The start field must be a string.',
            'end.required' => 'The end field is required.',
            'end.string' => 'The end field must be a string.',
        ];
    }

    public function startDayUtc(): CarbonImmutable
    {
        return $this->parseDateTimeInput((string) $this->input('start'), false)
            ->utc()
            ->startOfDay();
    }

    public function endDayUtc(): CarbonImmutable
    {
        return $this->endUtc()->startOfDay();
    }

    public function startUtc(): CarbonImmutable
    {
        return $this->parseDateTimeInput((string) $this->input('start'), false)
            ->utc()
            ->startOfHour();
    }

    public function endUtc(): CarbonImmutable
    {
        $parsed = $this->parseDateTimeInput((string) $this->input('end'), true)
            ->utc()
            ->startOfHour();

        return $this->clampEndToNowFloorHourUtc($parsed);
    }

    public function endWasClamped(): bool
    {
        $parsed = $this->parseDateTimeInput((string) $this->input('end'), true);
        if (!$parsed) {
            return false;
        }

        $requested = $parsed->utc()->startOfHour();

        return $requested->greaterThan($this->nowFloorHourUtc());
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        $requestId = $this->attributes->get('request_id') ?: $this->header('X-Request-Id');

        Log::warning('timeseries.validation_failed', [
            'event' => 'timeseries.validation_failed',
            'request_id' => $requestId,
            'path' => $this->getPathInfo(),
            'invalid_fields' => array_keys($validator->errors()->toArray()),
            'status' => 422,
        ]);

        parent::failedValidation($validator);
    }

    private function parseDateTimeInput(string $value, bool $isEnd): ?CarbonImmutable
    {
        $trimmed = trim($value);

        if (preg_match(self::LEGACY_DATE_REGEX, $trimmed) === 1) {
            try {
                $legacy = CarbonImmutable::createFromFormat('Y-m-d', $trimmed, 'UTC');
                return $isEnd ? $legacy->endOfDay() : $legacy->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        if (preg_match(self::ISO_8601_REGEX, $trimmed) !== 1) {
            return null;
        }

        try {
            return CarbonImmutable::parse($trimmed);
        } catch (\Throwable) {
            return null;
        }
    }

    private function clampEndToNowFloorHourUtc(CarbonImmutable $end): CarbonImmutable
    {
        $nowFloorHourUtc = $this->nowFloorHourUtc();
        if ($end->greaterThan($nowFloorHourUtc)) {
            return $nowFloorHourUtc;
        }

        return $end;
    }

    private function nowFloorHourUtc(): CarbonImmutable
    {
        return CarbonImmutable::now('UTC')->startOfHour();
    }
}
