<?php

return [
    // General context prefixes
    'Site' => 'Site',
    'Device' => 'Appareil',

    // General validation messages
    ':context: The :attribute field is required.' => ':context : Le champ :attribute est obligatoire.',
    ':context: The :attribute must be a string.' => ':context : Le champ :attribute doit être une chaîne de caractères.',
    ':context: The :attribute must be an integer.' => ':context : Le champ :attribute doit être un nombre entier.',
    ':context: The :attribute must be a number.' => ':context : Le champ :attribute doit être un nombre.',
    ':context: The :attribute must be true or false.' => ':context : Le champ :attribute doit être vrai ou faux.',
    ':context: The :attribute must be an array.' => ':context : Le champ :attribute doit être un tableau.',
    ':context: The :attribute must be a valid URL.' => ':context : Le champ :attribute doit être une URL valide.',
    ':context: The :attribute must be a valid email address.' => ':context : Le champ :attribute doit être une adresse e-mail valide.',
    ':context: The :attribute must be a valid date.' => ':context : Le champ :attribute doit être une date valide.',
    ':context: The selected :attribute is invalid.' => ':context : Le :attribute sélectionné n\'est pas valide.',
    ':context: The :attribute must be at least :min.' => ':context : Le champ :attribute doit être au moins :min.',
    ':context: The :attribute may not be greater than :max.' => ':context : Le champ :attribute ne peut pas être supérieur à :max.',

    // Specific messages for your use case
    ':context: The :attribute must not be 0.' => ':context : Le champ :attribute ne doit pas être 0.',
    ':context: The :attribute format is invalid.' => ':context : Le format du champ :attribute n\'est pas valide.',
    ':context: The :attribute has already been taken.' => ':context : Le champ :attribute est déjà utilisé.',
    ':context: The :attribute must be between :min and :max.' => ':context : Le champ :attribute doit être entre :min et :max.',

    // Messages for address fields
    ':context: The address field is required.' => ':context : Le champ adresse est obligatoire.',
    ':context: The address must be an array.' => ':context : L\'adresse doit être un tableau.',
    ':context: The street field is required.' => ':context : Le champ rue est obligatoire.',
    ':context: The city field is required.' => ':context : Le champ ville est obligatoire.',
    ':context: The ZIP code field is required.' => ':context : Le champ code postal est obligatoire.',
    ':context: The country ID field is required.' => ':context : Le champ ID du pays est obligatoire.',

    // Messages for phone numbers
    ':context: The PSTN format is invalid.' => ':context : Le format PSTN n\'est pas valide.',
    ':context: The SIM format is invalid.' => ':context : Le format SIM n\'est pas valide.',
    ':context: The SIP format is invalid.' => ':context : Le format SIP n\'est pas valide.',
    ':context: The PBX format is invalid.' => ':context : Le format PBX n\'est pas valide.',

    // Messages for device-specific fields
    ':context: The device ID field is required.' => ':context : Le champ ID de l\'appareil est obligatoire.',
    ':context: The device equipment must be a string.' => ':context : L\'équipement de l\'appareil doit être une chaîne de caractères.',
    ':context: The device identity must be a string.' => ':context : L\'identité de l\'appareil doit être une chaîne de caractères.',
    ':context: The device module must be a string.' => ':context : Le module de l\'appareil doit être une chaîne de caractères.',
    ':context: The device PIN must be a string.' => ':context : Le PIN de l\'appareil doit être une chaîne de caractères.',
    ':context: The device module cannot be 0.' => ':context : Le module de l\'appareil ne peut pas être 0.',
    ':context: The device alarm number must be a string.' => ':context : Le numéro d\'alarme de l\'appareil doit être une chaîne de caractères.',
    ':context: The device periodical number must be a string.' => ':context : Le numéro périodique de l\'appareil doit être une chaîne de caractères.',

    // Messages for custom fields
    ':context: The cloned custom fields must be an array.' => ':context : Les champs personnalisés clonés doivent être un tableau.',
    ':context: The DS Name must be a string.' => ':context : Le nom DS doit être une chaîne de caractères.',
    ':context: The DS Link must be a string.' => ':context : Le lien DS doit être une chaîne de caractères.',
    ':context: The DS Link must be a valid URL.' => ':context : Le lien DS doit être une URL valide.',
];