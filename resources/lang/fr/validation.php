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
            'required' => 'Adresse requise',
            'missingdata' => 'L\'adresse n\'est pas enregistrée. Veuillez remplir tous les champs relatifs',
            'address_wrong_input' => 'Le champ de l\'adresse contient des caractères non valides',
            'locality_wrong_input' => 'Le champ de la ville contient des caractères non valides',
            'postcode_wrong_input' => 'Le champ du code postal contient des caractères non valides.',
            'location_exists' => 'L\'emplacement existe déjà et appartient à un autre pays',
        ],
        'device_custom1' => [
            'required' => 'Champ personnalisé 1 requis'
        ],
        'device_custom2' => [
            'required' => 'Champ personnalisé 2 requis'
        ],
        'device_custom3' => [
            'required' => 'Champ personnalisé 3 requis'
        ],
        'device_custom4' => [
            'required' => 'Champ personnalisé 4 requis'
        ],
        'device_identity' => [
            'required' => 'Identité requise',
            'already_exists' => 'Le périphérique avec l\'identité fournie existe déjà',
        ],
        'device_equipment' => [
            'required' => 'Equipment required',
            'already_exists' => 'Device with given equipment already exists',
        ],
        'device_module' => [
            'required' => 'Numéro de module requis'
        ],
        'device_number_primary' => [
            'required' => 'Numéro principal requis'
        ],
        'device_number_sim' => [
            'required' => 'Numéro SIM requis'
        ],
        'device_number_sip' => [
            'required' => 'Numéro SIP requis'
        ],
        'device_pin' => [
            'required' => 'PIN requis'
        ],
        'numbers' => [
            'required' => 'Au moins un numéro est requis',
            'already_exists' => 'Au moins l\'un des numéros fournis existe déjà',
            'not_unique' => 'Les numéros fournis ne sont pas uniques',
        ]
    ],
    'device_address' => [
        'required' => 'Adresse requise'
    ],
    'device_custom1' => [
        'required' => 'Champ personnalisé 1 requis'
    ],
    'device_custom2' => [
        'required' => 'Champ personnalisé 2 requis'
    ],
    'device_custom3' => [
        'required' => 'Champ personnalisé 3 requis'
    ],
    'device_custom4' => [
        'required' => 'Champ personnalisé 4 requis'
    ],
    'device_identity' => [
        'required' => 'Identité requise'
    ],
    'device_module' => [
        'required' => 'Numéro de module requis'
    ],
    'device_number_primary' => [
        'required' => 'Numéro principal requis'
    ],
    'device_number_sim' => [
        'required' => 'Numéro SIM requis'
    ],
    'device_number_sip' => [
        'required' => 'Numéro SIP requis'
    ],
    'device_pin' => [
        'required' => 'PIN requis'
    ],
    'sipuser exists' => 'Le ou les utilisateurs Sip suivants existent déjà',
    'sipuser required' => 'Au moins un utilisateur Sip est requis',
    'Device created' => 'Données de périphérique enregistrées avec succès',
    'Primary phonenumber has wrong format. Use E.164 format' => 'Le numéro de téléphone principal est mal formaté. Utilisez le format E.164',
    'PIN required' => 'PIN requis',
    'Primary phonenumber required' => 'Numéro de téléphone principal requis',
    'Identity already exists' => 'L\'identité existe déjà',
    'Modulenumber already exists' => 'Le périphérique avec le numéro de module donné existe déjà',
    'PIN already exists' => 'Le périphérique avec le PIN donné existe déjà',
    'Primary phonenumber already exists' => 'Le numéro de téléphone principal existe déjà',
    'phone' => 'Le champ :attribute contient un numéro invalide.',
    'accepted' => 'Le champ :attribute doit être accepté.',
    'active_url' => 'Le champ :attribute n\'est pas une URL valide.',
    'after' => 'Le champ :attribute doit être une date après :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date postérieure ou égale à :date.',
    'alpha' => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne doit contenir que des lettres, des chiffres, des tirets et des underscores.',
    'alpha_num' => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'before' => 'Le champ :attribute doit être une date antérieure à :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date antérieure ou égale à :date.',
    'between' => [
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'file' => 'Le champ :attribute doit être compris entre :min et :max kilooctets.',
        'string' => 'Le champ :attribute doit être compris entre :min et :max caractères.',
        'array' => 'Le champ :attribute doit avoir entre :min et :max éléments.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'date' => 'Le champ :attribute n\'est pas une date valide.',
    'date_equals' => 'Le champ :attribute doit être une date égale à :date.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit comporter :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit comporter entre :min et :max chiffres.',
    'dimensions' => 'Le champ :attribute a des dimensions d\'image non valides.',
    'distinct' => 'Le champ :attribute a une valeur en double.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'ends_with' => 'Le champ :attribute doit se terminer par l\'une des valeurs suivantes : :values.',
    'exists' => 'Le champ :attribute sélectionné est invalide.',
    'file' => 'Le champ :attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',
    'gt' => [
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'file' => 'Le champ :attribute doit être supérieur à :value kilooctets.',
        'string' => 'Le champ :attribute doit contenir plus de :value caractères.',
        'array' => 'Le champ :attribute doit contenir plus de :value éléments.',
    ],
    'gte' => [
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'file' => 'Le champ :attribute doit être supérieur ou égal à :value kilooctets.',
        'string' => 'Le champ :attribute doit contenir :value caractères ou plus.',
        'array' => 'Le champ :attribute doit avoir :value éléments ou plus.',
    ],
    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'Le champ :attribute sélectionné n\'est pas valide.',
    'in_array' => 'Le champ :attribute n\'existe pas dans :other.',
    'integer' => 'Le champ :attribute doit être un entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4' => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6' => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json' => 'Le champ :attribute doit être une chaîne JSON valide.',
    'lt' => [
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'file' => 'Le champ :attribute doit être inférieur à :value kilooctets.',
        'string' => 'Le champ :attribute doit contenir moins de :value caractères.',
        'array' => 'Le champ :attribute doit contenir moins de :value éléments.',
    ],
    'lte' => [
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'file' => 'Le champ :attribute doit être inférieur ou égal à :value kilooctets.',
        'string' => 'Le champ :attribute doit contenir :value caractères ou moins.',
        'array' => 'Le champ :attribute ne doit pas avoir plus de :value éléments.',
    ],
    'max' => [
        'numeric' => 'Le champ :attribute ne doit pas être supérieur à :max.',
        'file' => 'Le champ :attribute ne doit pas être supérieur à :max kilooctets.',
        'string' => 'Le champ :attribute ne doit pas contenir plus de :max caractères.',
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
    ],
    'mimes' => 'Le champ :attribute doit être un fichier de type :values.',
    'mimetypes' => 'Le champ :attribute doit être un fichier de type :values.',
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'file' => 'Le champ :attribute doit être au moins de :min kilooctets.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
    ],
    'multiple_of' => 'Le champ :attribute doit être un multiple de :value.',
    'not_in' => 'Le champ :attribute sélectionné n\'est pas valide.',
    'not_regex' => 'Le format du champ :attribute n\'est pas valide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'password' => 'Le mot de passe est incorrect.',
    'present' => 'Le champ :attribute doit être présent.',
    'regex' => 'Le format du champ :attribute n\'est pas valide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire lorsque :other est :value.',
    'required_unless' => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with' => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_with_all' => 'Le champ :attribute est obligatoire lorsque :values sont présents.',
    'required_without' => 'Le champ :attribute est obligatoire lorsque :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est obligatoire lorsque aucun de :values n\'est présent.',
    'prohibited' => 'Le champ :attribute est interdit.',
    'prohibited_if' => 'Le champ :attribute est interdit lorsque :other est :value.',
    'prohibited_unless' => 'Le champ :attribute est interdit sauf si :other est dans :values.',
    'same' => 'Les champs :attribute et :other doivent correspondre.',
    'size' => [
        'numeric' => 'Le champ :attribute doit être :size.',
        'file' => 'Le champ :attribute doit être :size kilooctets.',
        'string' => 'Le champ :attribute doit contenir :size caractères.',
        'array' => 'Le champ :attribute doit contenir :size éléments.',
    ],
    'starts_with' => 'Le champ :attribute doit commencer par l\'un des éléments suivants : :values.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone' => 'Le champ :attribute doit être une zone valide.',
    'unique' => 'Le champ :attribute a déjà été pris.',
    'uploaded' => 'Le champ :attribute n\'a pas pu être téléchargé.',
    'url' => 'Le format de :attribute n\'est pas valide.',
    'uuid' => 'Le champ :attribute doit être un UUID valide.',
    'mac_address' => [
        'exists' => 'L\'adresse MAC suivante existe déjà : :mac_address',
        'not_exists' => 'L\'adresse MAC suivante n\'existe pas : :mac_address',
        'invalid' => 'Adresse MAC invalide',
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
            'required' => 'PIN requis.',
        ],
        'device.device_number_primary' => [
            'required' => 'Numéro de téléphone principal requis.',
        ],
        'device.device_identity' => [
            'required' => 'Identité requise.',
        ],
        'device.device_module' => [
            'required' => 'Numéro de module requis.',
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
