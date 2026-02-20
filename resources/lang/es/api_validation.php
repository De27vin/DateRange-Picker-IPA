<?php

return [
    // General context prefixes
    'Site' => 'Sitio',
    'Device' => 'Dispositivo',

    // General validation messages
    ':context: The :attribute field is required.' => ':context: El campo :attribute es obligatorio.',
    ':context: The :attribute must be a string.' => ':context: El campo :attribute debe ser una cadena de texto.',
    ':context: The :attribute must be an integer.' => ':context: El campo :attribute debe ser un número entero.',
    ':context: The :attribute must be a number.' => ':context: El campo :attribute debe ser un número.',
    ':context: The :attribute must be true or false.' => ':context: El campo :attribute debe ser verdadero o falso.',
    ':context: The :attribute must be an array.' => ':context: El campo :attribute debe ser un array.',
    ':context: The :attribute must be a valid URL.' => ':context: El campo :attribute debe ser una URL válida.',
    ':context: The :attribute must be a valid email address.' => ':context: El campo :attribute debe ser una dirección de correo electrónico válida.',
    ':context: The :attribute must be a valid date.' => ':context: El campo :attribute debe ser una fecha válida.',
    ':context: The selected :attribute is invalid.' => ':context: El :attribute seleccionado no es válido.',
    ':context: The :attribute must be at least :min.' => ':context: El campo :attribute debe ser al menos :min.',
    ':context: The :attribute may not be greater than :max.' => ':context: El campo :attribute no puede ser mayor que :max.',

    // Specific messages for your use case
    ':context: The :attribute must not be 0.' => ':context: El campo :attribute no puede ser 0.',
    ':context: The :attribute format is invalid.' => ':context: El formato del campo :attribute no es válido.',
    ':context: The :attribute has already been taken.' => ':context: El campo :attribute ya ha sido utilizado.',
    ':context: The :attribute must be between :min and :max.' => ':context: El campo :attribute debe estar entre :min y :max.',

    // Messages for address fields
    ':context: The address field is required.' => ':context: El campo de dirección es obligatorio.',
    ':context: The address must be an array.' => ':context: La dirección debe ser un array.',
    ':context: The street field is required.' => ':context: El campo de calle es obligatorio.',
    ':context: The city field is required.' => ':context: El campo de ciudad es obligatorio.',
    ':context: The ZIP code field is required.' => ':context: El campo de código postal es obligatorio.',
    ':context: The country ID field is required.' => ':context: El campo de ID de país es obligatorio.',

    // Messages for phone numbers
    ':context: The PSTN format is invalid.' => ':context: El formato de PSTN no es válido.',
    ':context: The SIM format is invalid.' => ':context: El formato de SIM no es válido.',
    ':context: The SIP format is invalid.' => ':context: El formato de SIP no es válido.',
    ':context: The PBX format is invalid.' => ':context: El formato de PBX no es válido.',

    // Messages for device-specific fields
    ':context: The device ID field is required.' => ':context: El campo de ID del dispositivo es obligatorio.',
    ':context: The device equipment must be a string.' => ':context: El equipo del dispositivo debe ser una cadena de texto.',
    ':context: The device identity must be a string.' => ':context: La identidad del dispositivo debe ser una cadena de texto.',
    ':context: The device module must be a string.' => ':context: El módulo del dispositivo debe ser una cadena de texto.',
    ':context: The device PIN must be a string.' => ':context: El PIN del dispositivo debe ser una cadena de texto.',
    ':context: The device module cannot be 0.' => ':context: El módulo del dispositivo no puede ser 0.',
    ':context: The device alarm number must be a string.' => ':context: El número de alarma del dispositivo debe ser una cadena de texto.',
    ':context: The device periodical number must be a string.' => ':context: El número periódico del dispositivo debe ser una cadena de texto.',

    // Messages for custom fields
    ':context: The cloned custom fields must be an array.' => ':context: Los campos personalizados clonados deben ser un array.',
    ':context: The DS Name must be a string.' => ':context: El nombre DS debe ser una cadena de texto.',
    ':context: The DS Link must be a string.' => ':context: El enlace DS debe ser una cadena de texto.',
    ':context: The DS Link must be a valid URL.' => ':context: El enlace DS debe ser una URL válida.',
];