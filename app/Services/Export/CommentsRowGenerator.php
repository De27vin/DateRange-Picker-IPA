<?php

namespace App\Services\Export;

use App\Models\User;
use App\Services\SearchDeviceService;
use App\Traits\SearchFiltersTrait;
use Illuminate\Support\Arr;

class CommentsRowGenerator implements RowGeneratorInterface
{
    use SearchFiltersTrait;

    public function requiredParams(): array
    {
        return ['filters', 'identifiers'];
    }

    public function getHeader(array $params): array
    {
        $headerLabels = ['device_id' => trans('Device ID')];

        foreach ($params['identifiers'] as $identifier => $enabled) {
            if ($enabled) {
                $headerLabels = Arr::add($headerLabels, $identifier, ucfirst($identifier));
            }
        }

        $headerLabels = Arr::add($headerLabels, 'author', 'Author');
        $headerLabels = Arr::add($headerLabels, 'date', 'Date');
        $headerLabels = Arr::add($headerLabels, 'comment', 'Comment');

        return $headerLabels;
    }


    public function generate(array $params, string $progressFile): \Generator
    {
        $header        = $this->getHeader($params);
        $searchService = new SearchDeviceService();
        $filters       = $params['filters'];

        $devices = $params['exportSites']
            ? $this->getDevicesFromSites($filters, $searchService)
            : $this->getDevices($filters, $searchService);

        $total     = $this->countItems($devices);
        $processed = 0;

        foreach ($devices as $device) {
            yield from $this->generateRowsForDevice($header, $device);

            $processed++;
            if ($total > 0) {
                @file_put_contents($progressFile, (string) max(1, min(99, (int) round(($processed / $total) * 99))));
            }
        }
    }

    private function getDevicesFromSites(array $filters, SearchDeviceService $service): array
    {
        $result = [];
        $sites  = $service->searchDeviceSites($filters);

        foreach ($sites as $site) {
            if ($site->devices->isEmpty()) {
                continue;
            }

            foreach ($site->devices as $device) {
                if ($this->shouldIncludeDevice($device, $filters)) {
                    $result[] = $device;
                }
            }
        }

        return $result;
    }

    private function getDevices(array $filters, SearchDeviceService $service)
    {
        $devices = $service->searchDevices($filters);

        if (empty($filters['search_tabs']) || in_array('all', $filters['search_tabs'])) {
            return $devices;
        }

        return $devices->filter(fn ($device) => $this->shouldIncludeDevice($device, $filters));
    }

    private function shouldIncludeDevice($device, array $filters): bool
    {
        return empty($filters['search_tabs'])
            || in_array('all', $filters['search_tabs'])
            || ($device->device_enabled && in_array('enabled', $filters['search_tabs']))
            || (!$device->device_enabled && in_array('disabled', $filters['search_tabs']));
    }

    private function generateRowsForDevice(array $header, $device): array
    {
        $rows = [];

        foreach ($device['device_comments'] as $comment) {
            $index = 0;
            $row   = [];

            foreach ($header as $key => $label) {
                if ($key === 'device_id') {
                    $row[$index] = $device['device_id'];
                } elseif ($key === 'equipment') {
                    $row[$index] = $device['device_equipment'];
                } elseif ($key === 'identity') {
                    $row[$index] = $device['device_identity'];
                } elseif ($key === 'pin') {
                    $row[$index] = $device['device_pin'];
                } elseif ($key === 'module') {
                    $row[$index] = $device['device_module'];
                } elseif ($key === 'numbers') {
                    $row[$index] = implode('|', $device['device_site']['numbers']->pluck('number_value')->toArray());
                } elseif ($key === 'site') {
                    $row[$index] = $device['device_site']['ds_name'];
                } elseif ($key === 'author') {
                    $user        = User::where('user_id', $comment['dc_user_id'])->first();
                    $row[$index] = $user ? $user->name : '';
                } elseif ($key === 'date') {
                    $row[$index] = toUserDateTime($comment['dc_created']);
                } elseif ($key === 'comment') {
                    $row[$index] = $comment['dc_text'];
                } else {
                    $row[$index] = '';
                }

                $index++;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param iterable $items
     */
    private function countItems($items): int
    {
        if (is_array($items)) {
            return count($items);
        }

        if (is_object($items) && method_exists($items, 'count')) {
            return (int) $items->count();
        }

        $count = 0;
        foreach ($items as $_) {
            $count++;
        }

        return $count;
    }
}
