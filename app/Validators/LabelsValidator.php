<?php
namespace App\Validators;

use App\Models\DeviceLabel;
use App\Models\DeviceSite;

class LabelsValidator
{
    public function validate(array $labels, DeviceSite $site): array
    {
        $errors = [];

        foreach ($labels as $label) {
            $labelExists = DeviceLabel::where('dl_id', $label['dl_id'])
                ->where('dl_account_id', $site->ds_account_id)
                ->exists();

            if (!$labelExists) {
                $errors[] = trans('validation.device_site.labels.invalid');
                break;
            }
        }

        return $errors;
    }
}