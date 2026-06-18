<?php

namespace App\Services\Export;

use App\Models\DeviceGateway;

class GatewaysRowGenerator implements RowGeneratorInterface
{
    public function requiredParams(): array
    {
        return [];  // tab and search are optional — defaults apply
    }

    public function getHeader(array $params): array
    {
        return [
            __('ID'),
            __('Gateway type'),
            __('Mac address'),
            __('Password'),
            __('Connected site'),
            __('Pstn'),
            __('Sim'),
            __('Sip'),
            __('Pbx'),
            __('Expires'),
            __('Enabled'),
        ];
    }

    public function generate(array $params, string $progressFile): \Generator
    {
        $tab    = $params['tab'] ?? 'enabled';
        $search = $params['search'] ?? '';

        $data      = $this->getGatewayData($tab, $search);
        $total     = $data->count();
        $processed = 0;

        foreach ($data as $gateway) {
            yield [
                $gateway->dg_id ?? '',
                $gateway->device->module->module_desc ?? $gateway->device->module->module_name ?? '',
                $gateway->dg_mac ?? '',
                $gateway->dg_sippwd ?? '',
                $gateway->device->device_site->ds_name ?? '',
                $gateway->device->device_site->pstn->number_value ?? '',
                $gateway->device->device_site->sim->number_value ?? '',
                $gateway->device->device_site->sip->number_value ?? '',
                $gateway->device->device_site->pbx->number_value ?? '',
                $gateway->dg_expires ?? '',
                $gateway->device->device_enabled ?? '0',
            ];

            $processed++;
            if ($total > 0) {
                @file_put_contents($progressFile, (string) max(1, min(99, (int) round(($processed / $total) * 99))));
            }
        }
    }

    private function getGatewayData(string $tab, string $search)
    {
        $query = DeviceGateway::query()
            ->with([
                'device.module',
                'device.device_site.numbers',
            ]);

        if (!empty($search)) {
            $search = strtolower($search);
            $query->where(function ($builder) use ($search) {
                $builder->where('dg_mac', 'like', '%' . $search . '%')
                    ->orWhereHas('type', function ($q) use ($search) {
                        $q->where('dgt_type', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('device_site', function ($q) use ($search) {
                        $q->where('ds_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('numbers', function ($q) use ($search) {
                        $q->where('number_value', 'like', '%' . $search . '%');
                    });
            });
        }

        return match ($tab) {
            'disabled'   => $query->forAccount()->disabled()->orderBy('dg_mac')->get(),
            'assigned'   => $query->forAccount()->assigned()->orderBy('dg_mac')->get(),
            'unassigned' => $query->forAccount()->unassigned()->orderBy('dg_mac')->get(),
            default      => $query->forAccount()->enabled()->orderBy('dg_mac')->get(),
        };
    }
}
