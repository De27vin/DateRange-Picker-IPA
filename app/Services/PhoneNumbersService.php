<?php
namespace App\Services;

use App\Models\DeviceSite;
use App\Models\Number;
use App\Models\NumberType;
use Illuminate\Support\Facades\DB;

class PhoneNumbersService
{
    public function checkAvailabilityAndReturn(string $numberString, ?DeviceSite $deviceSite = null): Number|string|false
    {
        $numberString = trim($numberString);
        $number = Number::where('number_value', $numberString)->first();

        if (empty($number)) {
            return $numberString;
        }

        if (empty($number->number_ds_id)) {
            return $number;
        }

        if ($deviceSite && $number->number_ds_id === $deviceSite->ds_id) {
            return $number;
        }

        return false;
    }

    public function updateSitePhoneNumbers(DeviceSite $deviceSite, array $numbers, ?int $accountId = null): array
    {
        $accountId = $accountId ?: (session('account.id') ?: abort());

        $phones = [
            'sip' => [
                'new' => !empty($numbers['sip']) ? $this->checkAvailabilityAndReturn($numbers['sip'], $deviceSite) : null,
                'old' => $deviceSite->sip,
                'state' => null
            ],
            'sim' => [
                'new' => !empty($numbers['sim']) ? $this->checkAvailabilityAndReturn($numbers['sim'], $deviceSite) : null,
                'old' => $deviceSite->sim,
                'state' => null
            ],
            'pbx' => [
                'new' => !empty($numbers['pbx']) ? $this->checkAvailabilityAndReturn($numbers['pbx'], $deviceSite) : null,
                'old' => $deviceSite->pbx,
                'state' => null
            ],
            'pstn' => [
                'new' => !empty($numbers['pstn']) ? $this->checkAvailabilityAndReturn($numbers['pstn'], $deviceSite) : null,
                'old' => $deviceSite->pstn,
                'state' => null
            ],
        ];


        $numberUpdates = [];
        foreach ($phones as $type => $item) {
            // Case 1: Swapping numbers
            if ($item['new'] instanceof Number && $item['new']->number_value != $item['old']?->number_value) {
                $numberUpdates[] = [
                    'number_value' => $item['new']->number_value,
                    'target_type' => $type,
                    'nt_id' => NumberType::where('nt_type', $type)->first()->nt_id
                ];
                $phones[$type]['state'] = 'change';
            }
            // Case 2: Detaching numbers
            elseif ($item['new'] == null && $item['old'] != null) {
                if (!$this->doesNumberForDetachExistsInNumbersToAttach($item['old'], $phones)) {
                    $this->detachNumbersFromSite($deviceSite, [$item['old']->number_value]);
                }
                $phones[$type]['state'] = 'detach';
            }
            // Case 3: Adding new numbers
            elseif ($item['new'] != null && $item['old'] == null) {
                if (is_string($item['new'])) {
                    Number::create([
                        'number_ds_id' => $deviceSite->ds_id,
                        'number_account_id' => $accountId,
                        'number_nt_id' => NumberType::where('nt_type', $type)->first()->nt_id,
                        'number_value' => $item['new'],  // bezpieczne - wiemy że to string
                    ]);
                } elseif ($item['new'] instanceof Number) {
                    $numberUpdates[] = [
                        'number_value' => $item['new']->number_value,
                        'target_type' => $type,
                        'nt_id' => NumberType::where('nt_type', $type)->first()->nt_id
                    ];
                }
                $phones[$type]['state'] = 'new';
            }
            // Case 4: Replacing existing with new string number
            elseif (is_string($item['new']) && $item['old'] instanceof Number) {
                $this->detachNumbersFromSite($deviceSite, [$item['old']->number_value]);
                Number::create([
                    'number_ds_id' => $deviceSite->ds_id,
                    'number_account_id' => $accountId,
                    'number_nt_id' => NumberType::where('nt_type', $type)->first()->nt_id,
                    'number_value' => $item['new'],  // bezpieczne - sprawdziliśmy is_string()
                ]);
                $phones[$type]['state'] = 'change';
            }
            // Case 5: Updating existing number
            elseif ($item['new'] instanceof Number && $item['old'] instanceof Number && $item['new']->number_value === $item['old']->number_value) {
                $numberUpdates[] = [
                    'number_value' => $item['new']->number_value,
                    'target_type' => $type,
                    'nt_id' => NumberType::where('nt_type', $type)->first()->nt_id
                ];
                $phones[$type]['state'] = 'update';
            }
        }

        // Bulk updates section - tu był błąd
        if (!empty($numberUpdates)) {
            $numbersToDetach = [];
            foreach ($phones as $type => $item) {
                if (!$item['old']) {
                    continue;
                }

                if (!$item['new']) {
                    $numbersToDetach[] = $item['old']->number_value;
                    continue;
                }

                $newValue = is_string($item['new']) ? $item['new'] : $item['new']->number_value;
                if ($newValue !== $item['old']->number_value) {
                    $numbersToDetach[] = $item['old']->number_value;
                }
            }

            if (!empty($numbersToDetach)) {
                DB::table('numbers')
                ->whereIn('number_value', $numbersToDetach)
                ->update(['number_ds_id' => null]);
            }

            foreach ($numberUpdates as $update) {
                DB::table('numbers')
                ->where('number_value', $update['number_value'])
                ->update([
                    'number_ds_id' => $deviceSite->ds_id,
                    'number_account_id' => $accountId,
                    'number_nt_id' => $update['nt_id']
                ]);
            }
        }

        $toReload = [];
        foreach ($phones as $type => $item) {
            if ($item['state'] != null && $item['old'] != null) {
                $toReload[] = $item['old']->number_id;
            }
        }

        return $toReload;
    }

    public function detachNumbersFromSite(DeviceSite $deviceSite, array $numbersToDetach = [])
    {
        $numbers = $deviceSite->numbers;
        foreach ($numbers as $number) {
            if (!empty($numbersToDetach) && !in_array($number->number_value, $numbersToDetach, true)) {
                continue;
            }
            $number->number_ds_id = null;
            $number->save();
        }
    }

    private function doesNumberForDetachExistsInNumbersToAttach($oldPhone, array $phones): bool
    {
        if (!is_string($oldPhone)) {
            $oldPhone = $oldPhone->number_value;
        }

        $sourceType = '';
        foreach ($phones as $type => $phone) {
            if ($phone['old'] && $phone['old']->number_value === $oldPhone) {
                $sourceType = $type;
                break;
            }
        }

        foreach ($phones as $type => $phone) {
            if ($type === $sourceType) {
                continue;
            }

            if ($phone['new']) {
                if (is_string($phone['new']) && $phone['new'] === $oldPhone) {
                    return true;
                } elseif ($phone['new'] instanceof Number && $phone['new']->number_value === $oldPhone) {
                    return true;
                }
            }
        }

        return false;
    }
}