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
    'device' => [
        'device_address' => [
            'required' => 'Address fehlt',
            'missingdata' => 'Adresse nicht gespeichert. Bitte füllen Sie alle entsprechenden Felder aus',
            'address_wrong_input' => 'Feld für Adresse enthält ungültige Zeichen',
            'locality_wrong_input' => 'Feld für Ort enthält ungültige Zeichen',
            'postcode_wrong_input' => 'Feld für Postleitzahl enthält ungültige Zeichen',
            'location_exists' => 'Standort existiert bereits und gehört zu einem anderen Land',
        ],
        'device_custom1' => [
            'required' => 'Custom field 1 fehlt'
        ],
        'device_custom2' => [
            'required' => 'Custom field 2  fehlt'
        ],
        'device_custom3' => [
            'required' => 'Custom field 3  fehlt'
        ],
        'device_custom4' => [
            'required' => 'Custom field 4  fehlt'
        ],
        'device_identity' => [
            'required' => 'Identity fehlt',
            'already_exists' => 'Gerät mit der angegebenen Identität existiert bereits',
        ],
        'device_equipment' => [
            'required' => 'Equipment required',
            'already_exists' => 'Device with given equipment already exists',
        ],
        'device_module' => [
            'required' => 'Modul Nummer fehlt',
            'alreadyexists' => 'Modul Nummer existiert bereits',
        ],
        'device_number_primary' => [
            'required' => 'Primäre Rufnummer fehlt',
            'samegroup' => 'Primäre Rufnummer gehört noch zur aktuellen Gruppe',
            'notfound' => 'Primäre Rufnummer wurde nicht gefunden',
            'alreadyexists' => 'Primäre Rufnummer existiert bereits'
        ],
        'device_number_sim' => [
            'required' => 'SIM Nummer fehlt'
        ],
        'device_number_sip' => [
            'required' => 'SIP Nummer fehlt'
        ],
        'device_pin' => [
            'required' => 'PIN fehlt',
            'alreadyexists' => 'PIN existiert bereits'
        ],
        'module' => [
            'notsamemodule' => 'Das neue Parent Gerät muss das gleichen UCP Modul besitzen'
        ],
        'numbers' => [
            'required' => 'Mindestens eine Zahl ist erforderlich',
            'already_exists' => 'Mindestens eine der bereitgestellten Zahlen existiert bereits',
            'not_unique' => 'Die bereitgestellten Zahlen sind nicht eindeutig',
        ]
    ],
    'device_address' => [
        'required' => 'Address fehlt'
    ],
    'device_custom1' => [
        'required' => 'Custom field 1 fehlt'
    ],
    'device_custom2' => [
        'required' => 'Custom field 2  fehlt'
    ],
    'device_custom3' => [
        'required' => 'Custom field 3  fehlt'
    ],
    'device_custom4' => [
        'required' => 'Custom field 4  fehlt'
    ],
    'device_identity' => [
        'required' => 'Identity fehlt'
    ],
    'device_module' => [
        'required' => 'Module Nummer fehlt'
    ],
    'device_number_primary' => [
        'required' => 'Primäre Rufnummer fehlt'
    ],
    'device_number_sim' => [
        'required' => 'SIM Nummer fehlt'
    ],
    'device_number_sip' => [
        'required' => 'SIP Nummer fehlt'
    ],
    'device_pin' => [
        'required' => 'PIN fehlt'
    ],
    'sipuser exists'                                         => 'Folgender Sip Benutzer existiert bereits',
    'sipuser required'                                       => 'Geben Sie mindestens einen Sip Benutzer ein',
    'Device created'                                         => 'Gerätedaten erfolgreich gespeichert',
    'Primary phonenumber has wrong format. Use E.164 format' => 'Primäre Rufnummer hat falsches Format. Verwenden Sie das E.164 Format',
    'PIN required'                                           => 'PIN fehlt',
    'Primary phonenumber required'                           => 'Primäre Rufnummer fehlt',
    'Modulenumber already exists'                            => 'Gerät mit dieser Modul Nummer existiert bereits',
    'PIN already exists'                                     => 'Gerät mit dieser PIN existiert bereits',
    'Identity already exists'                                => 'Gerät mit dieser Identität existiert bereits',
    'Primary phonenumber already exists'                     => 'Primäre Rufnummer existiert bereits',
    'phone'                                                  => 'Das :attribute Feld enthält eine inkorrekte Rufnummer.',
    'accepted'                                               => 'The :attribute must be accepted.',
    'active_url'                                             => 'The :attribute is not a valid URL.',
    'after'                                                  => 'The :attribute must be a date after :date.',
    'after_or_equal'                                         => 'The :attribute must be a date after or equal to :date.',
    'alpha'                                                  => 'The :attribute must only contain letters.',
    'alpha_dash'                                             => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num'                                              => 'The :attribute must only contain letters and numbers.',
    'array'                                                  => 'The :attribute must be an array.',
    'before'                                                 => 'The :attribute must be a date before :date.',
    'before_or_equal'                                        => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'        => 'The :attribute field must be true or false.',
    'confirmed'      => 'The :attribute confirmation does not match.',
    'date'           => 'The :attribute is not a valid date.',
    'date_equals'    => 'The :attribute must be a date equal to :date.',
    'date_format'    => 'The :attribute does not match the format :format.',
    'different'      => 'The :attribute and :other must be different.',
    'digits'         => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions'     => 'The :attribute has invalid image dimensions.',
    'distinct'       => 'The :attribute field has a duplicate value.',
    'email'          => 'The :attribute must be a valid email address.',
    'ends_with'      => 'The :attribute must end with one of the following: :values.',
    'exists'         => 'The selected :attribute is invalid.',
    'file'           => 'The :attribute must be a file.',
    'filled'         => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file'    => 'The :attribute must be greater than :value kilobytes.',
        'string'  => 'The :attribute must be greater than :value characters.',
        'array'   => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file'    => 'The :attribute must be greater than or equal :value kilobytes.',
        'string'  => 'The :attribute must be greater than or equal :value characters.',
        'array'   => 'The :attribute must have :value items or more.',
    ],
    'image'    => 'The :attribute must be an image.',
    'in'       => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer'  => 'The :attribute must be an integer.',
    'ip'       => 'The :attribute must be a valid IP address.',
    'ipv4'     => 'The :attribute must be a valid IPv4 address.',
    'ipv6'     => 'The :attribute must be a valid IPv6 address.',
    'json'     => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file'    => 'The :attribute must be less than :value kilobytes.',
        'string'  => 'The :attribute must be less than :value characters.',
        'array'   => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file'    => 'The :attribute must be less than or equal :value kilobytes.',
        'string'  => 'The :attribute must be less than or equal :value characters.',
        'array'   => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file'    => 'The :attribute must not be greater than :max kilobytes.',
        'string'  => 'The :attribute must not be greater than :max characters.',
        'array'   => 'The :attribute must not have more than :max items.',
    ],
    'mimes'     => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'multiple_of'          => 'The :attribute must be a multiple of :value.',
    'not_in'               => 'The selected :attribute is invalid.',
    'not_regex'            => 'The :attribute format is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'password'             => 'The password is incorrect.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values are present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'prohibited'           => 'The :attribute field is prohibited.',
    'prohibited_if'        => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless'    => 'The :attribute field is prohibited unless :other is in :values.',
    'same'                 => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string'      => 'The :attribute must be a string.',
    'timezone'    => 'The :attribute must be a valid zone.',
    'unique'      => 'The :attribute has already been taken.',
    'uploaded'    => 'The :attribute failed to upload.',
    'url'         => 'The :attribute format is invalid.',
    'uuid'        => 'The :attribute must be a valid UUID.',
    'mac_address' => [
        'exists' => 'Die MAC-Adresse existiert bereits: :mac_address',
        'not_exists' => 'Die MAC-Adresse existiert nicht: :mac_address',
        'invalid' => 'MAC-Adresse ist ungültig',
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
