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
            'required' => 'Indirizzo richiesto',
            'missingdata' => 'Indirizzo non salvato. Compilare tutti i campi relativi',
            'address_wrong_input' => 'Il campo dell\'indirizzo contiene caratteri non validi',
            'locality_wrong_input' => 'Il campo della città contiene caratteri non validi',
            'postcode_wrong_input' => 'Il campo per il codice postale contiene caratteri non validi',
            'location_exists' => 'La posizione esiste già e appartiene a un altro paese',
        ],
        'device_custom1' => [
            'required' => 'Campo personalizzato 1 richiesto'
        ],
        'device_custom2' => [
            'required' => 'Campo personalizzato 2 richiesto'
        ],
        'device_custom3' => [
            'required' => 'Campo personalizzato 3 richiesto'
        ],
        'device_custom4' => [
            'required' => 'Campo personalizzato 4 richiesto'
        ],
        'device_identity' => [
            'required' => 'Identità richiesta'
        ],
        'device_equipment' => [
            'required' => 'Equipment required',
            'already_exists' => 'Device with given equipment already exists',
        ],
        'device_module' => [
            'required' => 'Numero modulo richiesto',
            'already_exists' => 'Il dispositivo con l\'identità fornita esiste già',
        ],
        'device_number_primary' => [
            'required' => 'Numero primario richiesto'
        ],
        'device_number_sim' => [
            'required' => 'Numero SIM richiesto'
        ],
        'device_number_sip' => [
            'required' => 'Numero SIP richiesto'
        ],
        'device_pin' => [
            'required' => 'PIN richiesto'
        ],
        'numbers' => [
            'required' => 'È richiesto almeno un numero',
            'already_exists' => 'Almeno uno dei numeri forniti già esiste',
            'not_unique' => 'I numeri forniti non sono unici',
        ]
    ],
    'device_address' => [
        'required' => 'Indirizzo richiesto'
    ],
    'device_custom1' => [
        'required' => 'Campo personalizzato 1 richiesto'
    ],
    'device_custom2' => [
        'required' => 'Campo personalizzato 2 richiesto'
    ],
    'device_custom3' => [
        'required' => 'Campo personalizzato 3 richiesto'
    ],
    'device_custom4' => [
        'required' => 'Campo personalizzato 4 richiesto'
    ],
    'device_identity' => [
        'required' => 'Identità richiesta'
    ],
    'device_module' => [
        'required' => 'Numero modulo richiesto'
    ],
    'device_number_primary' => [
        'required' => 'Numero primario richiesto'
    ],
    'device_number_sim' => [
        'required' => 'Numero SIM richiesto'
    ],
    'device_number_sip' => [
        'required' => 'Numero SIP richiesto'
    ],
    'device_pin' => [
        'required' => 'PIN richiesto'
    ],
    'sipuser exists' => 'Il seguente utente Sip esiste già',
    'sipuser required' => 'È richiesto almeno un utente Sip',
    'Device created' => 'Dati del dispositivo salvati con successo',
    'Primary phonenumber has wrong format. Use E.164 format' => 'Il numero di telefono principale ha un formato errato. Utilizzare il formato E.164',
    'PIN required' => 'PIN richiesto',
    'Primary phonenumber required' => 'Numero di telefono principale richiesto',
    'Identity already exists' => 'L\'identità esiste già',
    'Modulenumber already exists' => 'Il dispositivo con il numero di modulo fornito esiste già',
    'PIN already exists' => 'Il dispositivo con il PIN fornito esiste già',
    'Primary phonenumber already exists' => 'Il numero di telefono principale esiste già',
    'phone' => 'Il campo :attribute contiene un numero non valido.',
    'accepted' => 'Il campo :attribute deve essere accettato.',
    'active_url' => 'Il campo :attribute non è un URL valido.',
    'after' => 'Il campo :attribute deve essere una data successiva a :date.',
    'after_or_equal' => 'Il campo :attribute deve essere una data successiva o uguale a :date.',
    'alpha' => 'Il campo :attribute deve contenere solo lettere.',
    'alpha_dash' => 'Il campo :attribute può contenere solo lettere, numeri, trattini e trattini bassi.',
    'alpha_num' => 'Il campo :attribute può contenere solo lettere e numeri.',
    'array' => 'Il campo :attribute deve essere un array.',
    'before' => 'Il campo :attribute deve essere una data precedente a :date.',
    'before_or_equal' => 'Il campo :attribute deve essere una data precedente o uguale a :date.',
    'between' => [
        'numeric' => 'Il campo :attribute deve essere compreso tra :min e :max.',
        'file' => 'Il campo :attribute deve essere compreso tra :min e :max kilobyte.',
        'string' => 'Il campo :attribute deve essere compreso tra :min e :max caratteri.',
        'array' => 'Il campo :attribute deve avere tra :min e :max elementi.',
    ],
    'boolean' => 'Il campo :attribute deve essere vero o falso.',
    'confirmed' => 'La conferma del campo :attribute non corrisponde.',
    'date' => 'Il campo :attribute non è una data valida.',
    'date_equals' => 'Il campo :attribute deve essere una data uguale a :date.',
    'date_format' => 'Il campo :attribute non corrisponde al formato :format.',
    'different' => 'Il campo :attribute e :other devono essere diversi.',
    'digits' => 'Il campo :attribute deve essere composto da :digits cifre.',
    'digits_between' => 'Il campo :attribute deve essere compreso tra :min e :max cifre.',
    'dimensions' => 'Il campo :attribute ha dimensioni immagine non valide.',
    'distinct' => 'Il campo :attribute ha un valore duplicato.',
    'email' => 'Il campo :attribute deve essere un indirizzo email valido.',
    'ends_with' => 'Il campo :attribute deve terminare con uno dei seguenti valori: :values.',
    'exists' => 'Il campo selezionato :attribute non è valido.',
    'file' => 'Il campo :attribute deve essere un file.',
    'filled' => 'Il campo :attribute deve avere un valore.',
    'gt' => [
        'numeric' => 'Il campo :attribute deve essere maggiore di :value.',
        'file' => 'Il campo :attribute deve essere maggiore di :value kilobyte.',
        'string' => 'Il campo :attribute deve essere maggiore di :value caratteri.',
        'array' => 'Il campo :attribute deve avere più di :value elementi.',
    ],
    'gte' => [
        'numeric' => 'Il campo :attribute deve essere maggiore o uguale a :value.',
        'file' => 'Il campo :attribute deve essere maggiore o uguale a :value kilobyte.',
        'string' => 'Il campo :attribute deve essere maggiore o uguale a :value caratteri.',
        'array' => 'Il campo :attribute deve avere :value elementi o più.',
    ],
    'image' => 'Il campo :attribute deve essere un\'immagine.',
    'in' => 'Il campo selezionato :attribute non è valido.',
    'in_array' => 'Il campo :attribute non esiste in :other.',
    'integer' => 'Il campo :attribute deve essere un numero intero.',
    'ip' => 'Il campo :attribute deve essere un indirizzo IP valido.',
    'ipv4' => 'Il campo :attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => 'Il campo :attribute deve essere un indirizzo IPv6 valido.',
    'json' => 'Il campo :attribute deve essere una stringa JSON valida.',
    'lt' => [
        'numeric' => 'Il campo :attribute deve essere minore di :value.',
        'file' => 'Il campo :attribute deve essere minore di :value kilobyte.',
        'string' => 'Il campo :attribute deve essere minore di :value caratteri.',
        'array' => 'Il campo :attribute deve avere meno di :value elementi.',
    ],
    'lte' => [
        'numeric' => 'Il campo :attribute deve essere minore o uguale a :value.',
        'file' => 'Il campo :attribute deve essere minore o uguale a :value kilobyte.',
        'string' => 'Il campo :attribute deve essere minore o uguale a :value caratteri.',
        'array' => 'Il campo :attribute non deve avere più di :value elementi.',
    ],
    'max' => [
        'numeric' => 'Il campo :attribute non deve essere maggiore di :max.',
        'file' => 'Il campo :attribute non deve essere maggiore di :max kilobyte.',
        'string' => 'Il campo :attribute non deve essere maggiore di :max caratteri.',
        'array' => 'Il campo :attribute non deve avere più di :max elementi.',
    ],
    'mimes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'mimetypes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'min' => [
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'file' => 'Il campo :attribute deve essere almeno :min kilobyte.',
        'string' => 'Il campo :attribute deve essere almeno :min caratteri.',
        'array' => 'Il campo :attribute deve avere almeno :min elementi.',
    ],
    'multiple_of' => 'Il campo :attribute deve essere un multiplo di :value.',
    'not_in' => 'Il campo selezionato :attribute non è valido.',
    'not_regex' => 'Il formato del campo :attribute non è valido.',
    'numeric' => 'Il campo :attribute deve essere un numero.',
    'password' => 'La password non è corretta.',
    'present' => 'Il campo :attribute deve essere presente.',
    'regex' => 'Il formato del campo :attribute non è valido.',
    'required' => 'Il campo :attribute è obbligatorio.',
    'required_if' => 'Il campo :attribute è obbligatorio quando :other è :value.',
    'required_unless' => 'Il campo :attribute è obbligatorio a meno che :other sia in :values.',
    'required_with' => 'Il campo :attribute è obbligatorio quando :values è presente.',
    'required_with_all' => 'Il campo :attribute è obbligatorio quando :values sono presenti.',
    'required_without' => 'Il campo :attribute è obbligatorio quando :values non è presente.',
    'required_without_all' => 'Il campo :attribute è obbligatorio quando nessuno dei :values è presente.',
    'prohibited' => 'Il campo :attribute è vietato.',
    'prohibited_if' => 'Il campo :attribute è vietato quando :other è :value.',
    'prohibited_unless' => 'Il campo :attribute è vietato a meno che :other sia in :values.',
    'same' => 'Il campo :attribute e :other devono corrispondere.',
    'size' => [
        'numeric' => 'Il campo :attribute deve essere :size.',
        'file' => 'Il campo :attribute deve essere :size kilobyte.',
        'string' => 'Il campo :attribute deve essere di :size caratteri.',
        'array' => 'Il campo :attribute deve contenere :size elementi.',
    ],
    'starts_with' => 'Il campo :attribute deve iniziare con uno dei seguenti valori: :values.',
    'string' => 'Il campo :attribute deve essere una stringa.',
    'timezone' => 'Il campo :attribute deve essere una zona valida.',
    'unique' => 'Il campo :attribute è già stato utilizzato.',
    'uploaded' => 'Il caricamento del file :attribute non è riuscito.',
    'url' => 'Il formato del campo :attribute non è valido.',
    'uuid' => 'Il campo :attribute deve essere un UUID valido.',
    'mac_address' => [
        'exists' => 'L\'indirizzo MAC :mac_address esiste già.',
        'not_exists' => 'L\'indirizzo MAC :mac_address non esiste.',
        'invalid' => 'L\'indirizzo MAC non è valido.',
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
            'required' => 'PIN mancante',
        ],
        'device.device_number_primary' => [
            'required' => 'Numero di telefono principale mancante',
        ],
        'device.device_identity' => [
            'required' => 'Identità mancante',
        ],
        'device.device_module' => [
            'required' => 'Numero di modulo mancante',
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
