<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\CarbonImmutable;

class TimeseriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chart' => [
                'required',
                Rule::in(['EquipmentChart', 'AlarmChart', 'AlertsChart', 'ServiceLevelChart']),
            ],
            'start' => ['required', 'date_format:Y-m-d'],
            'end'   => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $start = $this->input('start');
            $end   = $this->input('end');

            if (!$start || !$end) return;

            try {
                $s = CarbonImmutable::createFromFormat('Y-m-d', $start)->startOfDay();
                $e = CarbonImmutable::createFromFormat('Y-m-d', $end)->startOfDay();
            } catch (\Throwable) {
                return;
            }

            if ($e->lt($s)) {
                $v->errors()->add('end', 'end must be the same day or after start.');
            }

            // Range Limit, limited to 365 days to prevent performance issues
            $days = $s->diffInDays($e) + 1; // end inklusive
            if ($days > 365) {
                $v->errors()->add('end', 'Range too large (max 365 days).');
            }
        });
    }
}