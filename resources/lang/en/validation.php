<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'device_site' => [
        'link' => [
            'required' => 'Link is required'
        ]
    ],


    'device' => [
        'device_address' => [
            'required' => 'Address required',
            'missingdata' => 'Address is incomplete, please fill in all related fields',
            'address_wrong_input' => 'Address field contains invalid characters',
            'locality_wrong_input' => 'Locality field contains invalid characters',
            'postcode_wrong_input' => 'Postcode field contains invalid characters',
            'location_exists' => 'Location already exists and belongs to different country',
        ],
//        'device_custom1' => [
//            'required' => 'Custom field 1 required'
//        ],
//        'device_custom2' => [
//            'required' => 'Custom field 2  required'
//        ],
//        'device_custom3' => [
//            'required' => 'Custom field 3  required'
//        ],
//        'device_custom4' => [
//            'required' => 'Custom field 4  required'
//        ],
        // Device fields validation
        'device_equipment' => [
            'required' => 'Equipment required',
            'already_exists' => 'Device with given equipment already exists',
        ],
        'device_identity' => [
            'required' => 'Identity required',
            'already_exists' => 'Device with given identity already exists',
            'uniqueness_violation' => 'Edited Identity violates uniqueness constraints',
        ],
        'device_setidentity' => [
            'required' => 'Identity required',
            'already_exists' => 'Device with given identity already exists',
             'uniqueness_violation' => 'Edited Identity violates uniqueness constraints',
        ],
        'device_identity_or_set' => [
            'already_exists' => 'Device with given identity or setidentity already exists',
        ],
        'device_module' => [
            'required' => 'Module Number required',
            'already_exists' => 'Device with given module already exists',
            'uniqueness_violation' => 'Edited Module violates uniqueness constraints',
        ],
        'device_pin' => [
            'required' => 'PIN required',
            'already_exists' => 'Device with given pin already exists',
            'uniqueness_violation' => 'Edited Pin violates uniqueness constraints',
        ],
        'device_setmodule' => [
            'required' => 'Module Number required',
            'already_exists' => 'Device with given module already exists',
            'uniqueness_violation' => 'Edited Module violates uniqueness constraints',
        ],
        'device_setpin' => [
            'required' => 'PIN required',
            'already_exists' => 'Device with given pin already exists',
            'uniqueness_violation' => 'Edited Pin violates uniqueness constraints',
        ],
//        'device_number_primary' => [
//            'required' => 'Primary Number required'
//        ],
//        'device_number_sim' => [
//            'required' => 'SIM Number required'
//        ],
//        'device_number_sip' => [
//            'required' => 'SIP Number required'
//        ],

        'numbers' => [
            'required' => 'At least one number is required',
            'already_exists' => 'At least one from provided numbers already exists',
            'not_unique' => 'Provided numbers are not unique',
        ]
    ],
    'device_address' => [
        'required' => 'Address required'
    ],
    'device_custom1' => [
        'required' => 'Custom field 1 required'
    ],
    'device_custom2' => [
        'required' => 'Custom field 2  required'
    ],
    'device_custom3' => [
        'required' => 'Custom field 3  required'
    ],
    'device_custom4' => [
        'required' => 'Custom field 4  required'
    ],
    'device_identity' => [
        'required' => 'Identity required'
    ],
    'device_module' => [
        'required' => 'Module Number required'
    ],
    'device_number_primary' => [
        'required' => 'Primary Number required'
    ],
    'device_number_sim' => [
        'required' => 'SIM Number required'
    ],
    'device_number_sip' => [
        'required' => 'SIP Number required'
    ],
    'device_pin' => [
        'required' => 'PIN required'
    ],
    'sipuser exists' => 'The following Sip user already exists',
    'sipuser required' => 'At least one Sip User is required',
    'Device created' => 'Device data successfully saved',
    'Primary phonenumber has wrong format. Use E.164 format' => 'Primary phonenumber has wrong format. Use E.164 format',
    'PIN required' => 'PIN required',
    'Primary phonenumber required' => 'Primary phonenumber required',
    'Identity already exists' => 'Identity already exists',
    'Modulenumber already exists' => 'Device with given module number already exists',
    'PIN already exists' => 'Device with given PIN already exists',
    'Primary phonenumber already exists' => 'Primary phonenumber already exists',
    'phone' => 'The :attribute field contains an invalid number.',
    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'string' => 'The :attribute must not be greater than :max characters.',
        'array' => 'The :attribute must not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',
    'mac_address' => [
        'exists' => 'Mac address already exists: :mac_address',
        'not_exists' => 'Mac address does not exist: :mac_address',
        'invalid' => 'Mac address is invalid',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'device.device_pin' => [
            'required' => 'PIN fehlt',
        ],
        'device.device_number_primary' => [
            'required' => 'Primäre Rufnummer fehlt',
        ],
        'device.device_identity' => [
            'required' => 'Identity fehlt',
        ],
        'device.device_module' => [
            'required' => 'Modul Nummer fehlt',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
