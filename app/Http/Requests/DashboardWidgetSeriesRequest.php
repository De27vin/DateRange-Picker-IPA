<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardWidgetSeriesRequest extends FormRequest
{
    private const WIDGETS = [
        'equipment',
        'overdues',
        'alerts',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'widget' => ['required', 'string', Rule::in(self::WIDGETS)],
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function startUtc(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', (string) $this->validated('start'), 'UTC')
            ->utc()
            ->startOfDay();
    }

    public function endUtc(): CarbonImmutable
    {
        $requested = CarbonImmutable::createFromFormat('Y-m-d', (string) $this->validated('end'), 'UTC')
            ->utc()
            ->endOfDay()
            ->startOfHour();

        $nowFloorHour = CarbonImmutable::now('UTC')->startOfHour();

        return $requested->greaterThan($nowFloorHour) ? $nowFloorHour : $requested;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startUtc = $this->startUtc();
            $endUtc = $this->endUtc();

            if ($endUtc->lessThan($startUtc)) {
                $validator->errors()->add('end', 'The end date must be the same as or after start.');
            }

            if ($endUtc->diffInDays($startUtc) > 365) {
                $validator->errors()->add('end', 'The selected range must be 365 days or less.');
            }
        });
    }
}
