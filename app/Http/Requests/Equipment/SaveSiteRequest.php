<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\Traits\TrimStrings;
use App\Models\DeviceGateway;
use App\Services\NotificationsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SaveSiteRequest extends FormRequest
{
    use TrimStrings;

    public function __construct(
        private readonly NotificationsService $notifications,
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Additional flags
            'updateCli' => 'nullable|boolean',

            // Site data
            'ds_id' => 'required|integer',
            'cloned.ds_name' => 'nullable|string',
            'cloned.ds_link' => 'nullable|string',

            // Address
            'cloned.address.street' => 'nullable|string',
            'cloned.address.city' => 'nullable|string',
            'cloned.address.zip' => 'nullable|string',
            'cloned.address.countryId' => 'nullable|integer',

            // Phone numbers
            'cloned.sip' => 'nullable|string',
            'cloned.sim' => 'nullable|string',
            'cloned.pbx' => 'nullable|string',
            'cloned.pstn' => 'nullable|string',

            // Settings numbers
            'cloned.alarmNumber' => 'nullable|string',
            'cloned.periodicalNumber' => 'nullable|string',

            // Labels
            'cloned.labels' => 'nullable|array',
            'cloned.labels.*.dl_id' => 'required|integer|exists:device_labels,dl_id',
            'cloned.labels.*.dl_name' => 'required|string',

                // Devices
                'devices' => 'nullable|array',
                'devices.*.device_id' => 'required|integer',
                'devices.*.cloned.device_equipment' => 'required|string',
                'devices.*.cloned.device_identity' => 'nullable|string',
                'devices.*.cloned.device_module' => 'nullable|integer|min:0',
                'devices.*.cloned.device_pin' => 'nullable|string',
                'devices.*.cloned.device_setidentity' => 'nullable|string',
                'devices.*.cloned.device_setmodule' => 'nullable|integer|min:0',
                'devices.*.cloned.device_setpin' => 'nullable|string',
                'devices.*.cloned.alarmNumber' => 'nullable|string',
                'devices.*.cloned.periodicalNumber' => 'nullable|string',

                // Devices custom fields
                'devices.*.clonedCustomFields' => 'nullable|array',
                'devices.*.clonedCustomFields.*.name' => 'required|string',
                'devices.*.clonedCustomFields.*.value' => 'nullable',
                'devices.*.clonedCustomFields.*.type' => 'nullable|string',
                'devices.*.clonedCustomFields.*.is_required' => 'nullable|boolean',
                'devices.*.clonedCustomFields.*.options' => 'nullable|array',

                // Devices gateways
                'devices.*.clonedGatewayId' => 'nullable|integer|exists:device_gateways,dg_id',

            // Custom fields
            'clonedCustomFields' => 'nullable|array',
            'clonedCustomFields.*.name' => 'required|string',
            'clonedCustomFields.*.value' => 'nullable',
            'clonedCustomFields.*.type' => 'nullable|string',
            'clonedCustomFields.*.is_required' => 'nullable|boolean',
            'clonedCustomFields.*.options' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __(':context: :attribute field is required.'),
            'string' => __(':context: :attribute must be a string.'),
            'integer' => __(':context: :attribute must be an integer.'),
            'numeric' => __(':context: :attribute must be a number.'),
            'boolean' => __(':context: :attribute must be true or false.'),
            'array' => __(':context: :attribute must be an array.'),
            'url' => __(':context: :attribute must be a valid URL.'),
            'email' => __(':context: :attribute must be a valid email address.'),
            'date' => __(':context: :attribute must be a valid date.'),
            'not_in' => __(':context: selected :attribute is invalid.'),
            'min' => __(':context: :attribute must be at least :min.'),
            'max' => __(':context: :attribute may not be greater than :max.'),
            'between' => __(':context: :attribute must be between :min and :max.'),
            'regex' => __(':context: :attribute format is invalid.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->trimStrings($this->all()));
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->messages();
        $errors = $this->formatErrors($errors);

        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $errors,
            'notifications' => $this->notifications->get(),
//            'notifications' => $this->notifications->addMany('error', $errors)->get(),
        ], 422));
    }

    protected function formatErrors(array $errors): array
    {
        $formattedErrors = [];

        foreach ($errors as $field => $messages) {
            $context = $this->getErrorContext($field);
            $attribute = $this->getLastFieldName($field);

            foreach ($messages as $message) {
                $formattedMessage = str_replace(
                    [':context', ':attribute', $field],
                    [$context, $attribute, $attribute],
                    $message
                );
                $formattedErrors[] = $formattedMessage;
            }
        }

        return $formattedErrors;
    }

    protected function getErrorContext(string $field): string
    {
        if (preg_match('/^devices\.(\d+)/', $field, $matches)) {
            $deviceIndex = $matches[1];
            $device = $this->input("devices.$deviceIndex", []);
            return $device['cloned']['device_equipment'] ?? __("Device #") . ((int) $deviceIndex + 1);
        }
        return __('Site');
    }

    protected function getLastFieldName(string $field): string
    {
        $fieldParts = explode('.', $field);
        return end($fieldParts);
    }
}