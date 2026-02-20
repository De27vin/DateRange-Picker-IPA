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
            'required' => 'Se requiere dirección',
            'missingdata' => 'Dirección no guardada. Por favor, complete todos los campos relacionados',
            'address_wrong_input' => 'El campo de dirección contiene caracteres no válidos',
            'locality_wrong_input' => 'El campo de ciudad contiene caracteres no válidos',
            'postcode_wrong_input' => 'El campo de código postal contiene caracteres no válidos',
            'location_exists' => 'La ubicación ya existe y pertenece a otro país',
        ],
        'device_custom1' => [
            'required' => 'Se requiere campo personalizado 1'
        ],
        'device_custom2' => [
            'required' => 'Se requiere campo personalizado 2'
        ],
        'device_custom3' => [
            'required' => 'Se requiere campo personalizado 3'
        ],
        'device_custom4' => [
            'required' => 'Se requiere campo personalizado 4'
        ],
        'device_identity' => [
            'required' => 'Se requiere identidad',
            'already_exists' => 'El dispositivo con la identidad proporcionada ya existe',
        ],
        'device_equipment' => [
            'required' => 'Equipment required',
            'already_exists' => 'Device with given equipment already exists',
        ],
        'device_module' => [
            'required' => 'Se requiere número de módulo'
        ],
        'device_number_primary' => [
            'required' => 'Se requiere número primario'
        ],
        'device_number_sim' => [
            'required' => 'Se requiere número SIM'
        ],
        'device_number_sip' => [
            'required' => 'Se requiere número SIP'
        ],
        'device_pin' => [
            'required' => 'Se requiere PIN'
        ],
        'numbers' => [
            'required' => 'Se requiere al menos un número',
            'already_exists' => 'Al menos uno de los números proporcionados ya existe',
            'not_unique' => 'Los números proporcionados no son únicos',
        ]
    ],
    'device_address' => [
        'required' => 'Se requiere dirección'
    ],
    'device_custom1' => [
        'required' => 'Se requiere campo personalizado 1'
    ],
    'device_custom2' => [
        'required' => 'Se requiere campo personalizado 2'
    ],
    'device_custom3' => [
        'required' => 'Se requiere campo personalizado 3'
    ],
    'device_custom4' => [
        'required' => 'Se requiere campo personalizado 4'
    ],
    'device_identity' => [
        'required' => 'Se requiere identidad'
    ],
    'device_module' => [
        'required' => 'Se requiere número de módulo'
    ],
    'device_number_primary' => [
        'required' => 'Se requiere número primario'
    ],
    'device_number_sim' => [
        'required' => 'Se requiere número SIM'
    ],
    'device_number_sip' => [
        'required' => 'Se requiere número SIP'
    ],
    'device_pin' => [
        'required' => 'Se requiere PIN'
    ],
    'sipuser exists' => 'El siguiente usuario Sip ya existe',
    'sipuser required' => 'Se requiere al menos un usuario Sip',
    'Device created' => 'Datos del dispositivo guardados exitosamente',
    'Primary phonenumber has wrong format. Use E.164 format' => 'El número de teléfono primario tiene un formato incorrecto. Use el formato E.164',
    'PIN required' => 'Se requiere PIN',
    'Primary phonenumber required' => 'Se requiere número de teléfono primario',
    'Identity already exists' => 'La identidad ya existe',
    'Modulenumber already exists' => 'El dispositivo con el número de módulo proporcionado ya existe',
    'PIN already exists' => 'El dispositivo con el PIN proporcionado ya existe',
    'Primary phonenumber already exists' => 'El número de teléfono primario ya existe',
    'phone' => 'El campo :attribute contiene un número no válido.',
    'accepted' => 'El :attribute debe ser aceptado.',
    'active_url' => 'El :attribute no es una URL válida.',
    'after' => 'El :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El :attribute debe contener solo letras.',
    'alpha_dash' => 'El :attribute debe contener solo letras, números, guiones y guiones bajos.',
    'alpha_num' => 'El :attribute debe contener solo letras y números.',
    'array' => 'El :attribute debe ser un array.',
    'before' => 'El :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'numeric' => 'El :attribute debe estar entre :min y :max.',
        'file' => 'El :attribute debe estar entre :min y :max kilobytes.',
        'string' => 'El :attribute debe tener entre :min y :max caracteres.',
        'array' => 'El :attribute debe tener entre :min y :max elementos.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => 'El :attribute no es una fecha válida.',
    'date_equals' => 'El :attribute debe ser una fecha igual a :date.',
    'date_format' => 'El :attribute no coincide con el formato :format.',
    'different' => 'El :attribute y :other deben ser diferentes.',
    'digits' => 'El :attribute debe tener :digits dígitos.',
    'digits_between' => 'El :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'El :attribute tiene dimensiones de imagen no válidas.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'email' => 'El :attribute debe ser una dirección de correo electrónico válida.',
    'ends_with' => 'El :attribute debe terminar con uno de los siguientes: :values.',
    'exists' => 'El :attribute seleccionado es inválido.',
    'file' => 'El :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute debe tener un valor.',
    'gt' => [
        'numeric' => 'El :attribute debe ser mayor que :value.',
        'file' => 'El :attribute debe ser mayor que :value kilobytes.',
        'string' => 'El :attribute debe tener más de :value caracteres.',
        'array' => 'El :attribute debe tener más de :value elementos.',
    ],
    'gte' => [
        'numeric' => 'El :attribute debe ser mayor o igual que :value.',
        'file' => 'El :attribute debe ser mayor o igual que :value kilobytes.',
        'string' => 'El :attribute debe tener :value caracteres o más.',
        'array' => 'El :attribute debe tener :value elementos o más.',
    ],
    'image' => 'El :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado es inválido.',
    'in_array' => 'El campo :attribute no existe en :other.',
    'integer' => 'El :attribute debe ser un número entero.',
    'ip' => 'El :attribute debe ser una dirección IP válida.',
    'ipv4' => 'El :attribute debe ser una dirección IPv4 válida.',
    'ipv6' => 'El :attribute debe ser una dirección IPv6 válida.',
    'json' => 'El :attribute debe ser una cadena JSON válida.',
    'lt' => [
        'numeric' => 'El :attribute debe ser menor que :value.',
        'file' => 'El :attribute debe ser menor que :value kilobytes.',
        'string' => 'El :attribute debe tener menos de :value caracteres.',
        'array' => 'El :attribute debe tener menos de :value elementos.',
    ],
    'lte' => [
        'numeric' => 'El :attribute debe ser menor o igual que :value.',
        'file' => 'El :attribute debe ser menor o igual que :value kilobytes.',
        'string' => 'El :attribute debe tener :value caracteres o menos.',
        'array' => 'El :attribute no debe tener más de :value elementos.',
    ],
    'max' => [
        'numeric' => 'El :attribute no debe ser mayor que :max.',
        'file' => 'El :attribute no debe ser mayor que :max kilobytes.',
        'string' => 'El :attribute no debe ser mayor que :max caracteres.',
        'array' => 'El :attribute no debe tener más de :max elementos.',
    ],
    'mimes' => 'El :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'numeric' => 'El :attribute debe ser al menos :min.',
        'file' => 'El :attribute debe tener al menos :min kilobytes.',
        'string' => 'El :attribute debe tener al menos :min caracteres.',
        'array' => 'El :attribute debe tener al menos :min elementos.',
    ],
    'multiple_of' => 'El :attribute debe ser un múltiplo de :value.',
    'not_in' => 'El :attribute seleccionado es inválido.',
    'not_regex' => 'El formato de :attribute es inválido.',
    'numeric' => 'El :attribute debe ser un número.',
    'password' => 'La contraseña es incorrecta.',
    'present' => 'El campo :attribute debe estar presente.',
    'regex' => 'El formato de :attribute es inválido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values está presente.',
    'prohibited' => 'El campo :attribute está prohibido.',
    'prohibited_if' => 'El campo :attribute está prohibido cuando :other es :value.',
    'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other esté en :values.',
    'same' => 'El :attribute y :other deben coincidir.',
    'size' => [
        'numeric' => 'El :attribute debe ser :size.',
        'file' => 'El :attribute debe tener :size kilobytes.',
        'string' => 'El :attribute debe tener :size caracteres.',
        'array' => 'El :attribute debe contener :size elementos.',
    ],
    'starts_with' => 'El :attribute debe comenzar con uno de los siguientes: :values.',
    'string' => 'El :attribute debe ser una cadena de texto.',
    'timezone' => 'El :attribute debe ser una zona horaria válida.',
    'unique' => 'El :attribute ya ha sido tomado.',
    'uploaded' => 'El :attribute no pudo ser cargado.',
    'url' => 'El formato de :attribute es inválido.',
    'uuid' => 'El :attribute debe ser un UUID válido.',
    'mac_address' => [
        'exists' => 'La dirección MAC :mac_address ya existe.',
        'not_exists' => 'La dirección MAC :mac_address no existe.',
        'invalid' => 'La dirección MAC no es válida.',
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
            'required' => 'PIN requerido',
        ],
        'device.device_number_primary' => [
            'required' => 'Número de teléfono primario requerido',
        ],
        'device.device_identity' => [
            'required' => 'Identity requerido',
        ],
        'device.device_module' => [
            'required' => 'Número de módulo requerido',
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
