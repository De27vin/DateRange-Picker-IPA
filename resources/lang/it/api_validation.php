<?php

return [
    // General context prefixes
    'Site' => 'Sito',
    'Device' => 'Dispositivo',

    // General validation messages
    ':context: The :attribute field is required.' => ':context: Il campo :attribute è obbligatorio.',
    ':context: The :attribute must be a string.' => ':context: Il campo :attribute deve essere una stringa.',
    ':context: The :attribute must be an integer.' => ':context: Il campo :attribute deve essere un numero intero.',
    ':context: The :attribute must be a number.' => ':context: Il campo :attribute deve essere un numero.',
    ':context: The :attribute must be true or false.' => ':context: Il campo :attribute deve essere vero o falso.',
    ':context: The :attribute must be an array.' => ':context: Il campo :attribute deve essere un array.',
    ':context: The :attribute must be a valid URL.' => ':context: Il campo :attribute deve essere un URL valido.',
    ':context: The :attribute must be a valid email address.' => ':context: Il campo :attribute deve essere un indirizzo email valido.',
    ':context: The :attribute must be a valid date.' => ':context: Il campo :attribute deve essere una data valida.',
    ':context: The selected :attribute is invalid.' => ':context: Il :attribute selezionato non è valido.',
    ':context: The :attribute must be at least :min.' => ':context: Il campo :attribute deve essere almeno :min.',
    ':context: The :attribute may not be greater than :max.' => ':context: Il campo :attribute non può essere maggiore di :max.',

    // Specific messages for your use case
    ':context: The :attribute must not be 0.' => ':context: Il campo :attribute non può essere 0.',
    ':context: The :attribute format is invalid.' => ':context: Il formato del campo :attribute non è valido.',
    ':context: The :attribute has already been taken.' => ':context: Il campo :attribute è già stato utilizzato.',
    ':context: The :attribute must be between :min and :max.' => ':context: Il campo :attribute deve essere compreso tra :min e :max.',

    // Messages for address fields
    ':context: The address field is required.' => ':context: Il campo indirizzo è obbligatorio.',
    ':context: The address must be an array.' => ':context: L\'indirizzo deve essere un array.',
    ':context: The street field is required.' => ':context: Il campo via è obbligatorio.',
    ':context: The city field is required.' => ':context: Il campo città è obbligatorio.',
    ':context: The ZIP code field is required.' => ':context: Il campo codice postale è obbligatorio.',
    ':context: The country ID field is required.' => ':context: Il campo ID paese è obbligatorio.',

    // Messages for phone numbers
    ':context: The PSTN format is invalid.' => ':context: Il formato PSTN non è valido.',
    ':context: The SIM format is invalid.' => ':context: Il formato SIM non è valido.',
    ':context: The SIP format is invalid.' => ':context: Il formato SIP non è valido.',
    ':context: The PBX format is invalid.' => ':context: Il formato PBX non è valido.',

    // Messages for device-specific fields
    ':context: The device ID field is required.' => ':context: Il campo ID dispositivo è obbligatorio.',
    ':context: The device equipment must be a string.' => ':context: L\'equipaggiamento del dispositivo deve essere una stringa.',
    ':context: The device identity must be a string.' => ':context: L\'identità del dispositivo deve essere una stringa.',
    ':context: The device module must be a string.' => ':context: Il modulo del dispositivo deve essere una stringa.',
    ':context: The device PIN must be a string.' => ':context: Il PIN del dispositivo deve essere una stringa.',
    ':context: The device module cannot be 0.' => ':context: Il modulo del dispositivo non può essere 0.',
    ':context: The device alarm number must be a string.' => ':context: Il numero di allarme del dispositivo deve essere una stringa.',
    ':context: The device periodical number must be a string.' => ':context: Il numero periodico del dispositivo deve essere una stringa.',

    // Messages for custom fields
    ':context: The cloned custom fields must be an array.' => ':context: I campi personalizzati clonati devono essere un array.',
    ':context: The DS Name must be a string.' => ':context: Il nome DS deve essere una stringa.',
    ':context: The DS Link must be a string.' => ':context: Il link DS deve essere una stringa.',
    ':context: The DS Link must be a valid URL.' => ':context: Il link DS deve essere un URL valido.',
];