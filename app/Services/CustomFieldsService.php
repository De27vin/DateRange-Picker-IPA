<?php
namespace App\Services;

use App\Models\CustomFieldConfig;
use App\Models\CustomFieldValue;
use App\Models\Device;
use App\Models\Module;
use App\Traits\TranslationsTrait;
use App\Traits\TrimInputs;

class CustomFieldsService
{
    use TranslationsTrait;
    use TrimInputs;

    public function getAccountCustomFieldsConfig(int $accountId) {
        $profileCustomFields = [];
        if (!empty($this->getProfileData()['custom_fields'])) {
            $profileCustomFields = $this->getProfileData()['custom_fields'];
        }

        $accountCustomConfigs = CustomFieldConfig::where('cfc_account_id', $accountId)
            ->get()->toArray();

        foreach ($accountCustomConfigs as &$customConfig) {
            $customConfig = $customConfig + ($profileCustomFields[$customConfig['cfc_id']] ?? []);
        }

        return $accountCustomConfigs;
    }

    public function getAccountCustomFieldsValues(int $accountId)
    {
        $profileDataFields = [];
        if (!empty($this->getProfileData()['custom_fields'])) {
            $profileDataFields = $this->getProfileData()['custom_fields'];
        }

        $accountCustomConfigs = CustomFieldConfig::where('cfc_account_id', $accountId)->get();
        $accountCustomValues = CustomFieldValue::all()->toArray();

        $resultValues = ['sites' => [], 'devices' => []];
        foreach ($accountCustomValues as $customValue) {

            $customConfig = $accountCustomConfigs->first(
                fn($v, $k) => $v['cfc_id'] === $customValue['cfv_cfc_id'] && $v['cfc_is_device'] == !empty($customValue['cfv_device_id'])
            );
            if (empty($customConfig)) continue;

            if (!empty($profileDataFields[$customValue['cfv_cfc_id']])) {
                $customValue = $customValue + $profileDataFields[$customValue['cfv_cfc_id']];
            } else {
                $customValue = $customValue + ['icon' => 'info_circle', 'dashboard' => true];
            }

            if (!empty($customValue['cfv_device_id'])) {
                $resultValues['devices'][$customValue['cfv_device_id']][$customValue['cfv_id']] = $customValue;
            }

            if (!empty($customValue['cfv_ds_id'])) {
                $resultValues['sites'][$customValue['cfv_ds_id']][$customValue['cfv_id']] = $customValue;
            }
        }

        return $resultValues;
    }


    // todo: rename to getCustomFieldsValues
    public function getCustomFields(int $accountId, int $targetId, bool $isDevice)
    {
        $profileDataFields = [];
        if (!empty($this->getProfileData()['custom_fields'])) {
            $profileDataFields = $this->getProfileData()['custom_fields'];
        }

        $customConfigs = CustomFieldConfig::where([
            ['cfc_account_id', $accountId],
            ['cfc_is_device', $isDevice],
        ])->get()->toArray();

        if ($isDevice) {
            $customValues = CustomFieldValue::where('cfv_device_id', $targetId)->get();
        } else {
            $customValues = CustomFieldValue::where('cfv_ds_id', $targetId)->get();
        }

        $customFields = [];
        foreach ($customConfigs as $config) {
            $customField = [
                'id' => $config['cfc_id'],
                'name' => $config['cfc_name'],
            ];

            if (!empty($profileDataFields[$config['cfc_id']])) {
                $customField = $customField + $profileDataFields[$config['cfc_id']];
            }

            if ($customValue = $customValues->firstWhere('cfv_cfc_id', $config['cfc_id'])) {
                $customField['value'] = $customValue->cfv_value;
            } else {
                $customField['value'] = null;
            }

            $customFields[] = $customField;
        }

        return $customFields;
    }


    // todo: rename to saveCustomFieldsValues
    public function saveCustomFields(int $targetId, array $customFields, bool $isDevice): void
    {
        $customFields = $this->trimStringsInArrayRecursively($customFields);

        // Validate QR code uniqueness for device fields
        if ($isDevice) {
            $this->validateQrCodeUniqueness($targetId, $customFields);
        }

        if ($isDevice) {
            $customValues = CustomFieldValue::where('cfv_device_id', $targetId)->get();
        } else {
            $customValues = CustomFieldValue::where('cfv_ds_id', $targetId)->get();
        }

        foreach ($customFields as $customField) {
            if ($customValue = $customValues->firstWhere('cfv_cfc_id', $customField['id'])) {
                if ($customValue->cfv_value !== $customField['value'] && !empty($customField['value'])) {
                    $customValue->cfv_value = $customField['value'];
                    $customValue->save();
                    continue;
                }
                if (empty($customField['value'])) {
                    $customValue->delete();
                }
            } elseif (!empty($customField['value'])) {
                CustomFieldValue::create([
                    'cfv_cfc_id' => $customField['id'],
                    ($isDevice ? 'cfv_device_id' : 'cfv_ds_id') => $targetId,
                    'cfv_value' => $customField['value'],
                ]);
            }
        }
    }

    public function getParrotAppField(Device $device): ?string
    {
        $accountId = $device->device_site?->account?->account_id;
        if (!$accountId) {
            return null;
        }

        $customFieldsConfig = $this->getAccountCustomFieldsConfig($accountId);

        $parrotAppFieldConfig = null;
        foreach ($customFieldsConfig as $fieldConfig) {
            if (!empty($fieldConfig['parrot_app']) && $fieldConfig['parrot_app'] === true) {
                $parrotAppFieldConfig = $fieldConfig;
                break;
            }
        }

        if (!$parrotAppFieldConfig) {
            return null;
        }

        $isDeviceField = !empty($parrotAppFieldConfig['cfc_is_device']);
        $fieldValue = null;

        if ($isDeviceField) {
            $deviceCustomFields = $this->getCustomFields($accountId, $device->device_id, true);
            foreach ($deviceCustomFields as $field) {
                if ($field['id'] == $parrotAppFieldConfig['cfc_id'] && !empty($field['value'])) {
                    $fieldValue = $field['value'];
                    break;
                }
            }
        } else {
            $siteId = $device->device_site?->ds_id;
            if ($siteId) {
                $siteCustomFields = $this->getCustomFields($accountId, $siteId, false);
                foreach ($siteCustomFields as $field) {
                    if ($field['id'] == $parrotAppFieldConfig['cfc_id'] && !empty($field['value'])) {
                        $fieldValue = $field['value'];
                        break;
                    }
                }
            }
        }

        return $fieldValue ?? null;
    }

    public function getQrCodeField(Device $device): ?array
    {
        $accountId = $device->device_site?->account?->account_id;
        if (!$accountId) {
            return null;
        }

        $customFieldsConfig = $this->getAccountCustomFieldsConfig($accountId);

        $qrCodeFieldConfig = null;
        foreach ($customFieldsConfig as $fieldConfig) {
            if (!empty($fieldConfig['qr_code']) && $fieldConfig['qr_code'] === true && !empty($fieldConfig['cfc_is_device'])) {
                $qrCodeFieldConfig = $fieldConfig;
                break;
            }
        }

        if (!$qrCodeFieldConfig) {
            return null;
        }

        $deviceCustomFields = $this->getCustomFields($accountId, $device->device_id, true);
        foreach ($deviceCustomFields as $field) {
            if ($field['id'] == $qrCodeFieldConfig['cfc_id'] && !empty($field['value'])) {
                return [
                    'fieldName' => $qrCodeFieldConfig['cfc_name'],
                    'value' => $field['value'],
                    'qrCodeSvg' => $this->generateQrCode($field['value'])
                ];
            }
        }

        return null;
    }

    private function generateQrCode(string $value): string
    {
        try {
            return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($value);
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed', ['error' => $e->getMessage(), 'value' => $value]);
            return '';
        }
    }

    private function validateQrCodeUniqueness(int $currentDeviceId, array $customFields): void
    {
        // Get accountId from device
        $device = Device::find($currentDeviceId);
        if (!$device || !$device->device_account_id) {
            return;
        }
        $accountId = $device->device_account_id;

        // Get QR code field config
        $customFieldsConfig = $this->getAccountCustomFieldsConfig($accountId);
        $qrCodeConfig = null;
        foreach ($customFieldsConfig as $fieldConfig) {
            if (!empty($fieldConfig['qr_code']) && $fieldConfig['qr_code'] === true && !empty($fieldConfig['cfc_is_device'])) {
                $qrCodeConfig = $fieldConfig;
                break;
            }
        }

        if (!$qrCodeConfig) {
            return;
        }

        // Find QR code field value in submitted data
        $qrCodeFieldValue = null;
        foreach ($customFields as $field) {
            if ($field['id'] == $qrCodeConfig['cfc_id'] && !empty($field['value'])) {
                $qrCodeFieldValue = trim($field['value']);
                break;
            }
        }

        if (!$qrCodeFieldValue) {
            return;
        }

        // Check if QR code value already exists in account (excluding current device)
        $existingValue = CustomFieldValue::where('cfv_cfc_id', $qrCodeConfig['cfc_id'])
            ->where('cfv_value', $qrCodeFieldValue)
            ->where('cfv_device_id', '!=', $currentDeviceId)
            ->whereHas('config', function($query) use ($accountId) {
                $query->where('cfc_account_id', $accountId);
            })
            ->first();

        if ($existingValue) {
            throw new \Exception(__('QR Code value must be unique within the account. This value already exists on another device.'));
        }
    }

}