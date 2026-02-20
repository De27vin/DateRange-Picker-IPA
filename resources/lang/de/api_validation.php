<?php

return [
    // General context prefixes
    'Site' => 'Site',
    'Device' => 'Device',

    // General validation messages
    ':context :attribute field is required.' => ':context :attribute field is required.',
    ':context :attribute must be a string.' => ':context :attribute must be a string.',
    ':context :attribute must be an integer.' => ':context :attribute must be an integer.',
    ':context :attribute must be a number.' => ':context :attribute must be a number.',
    ':context :attribute must be true or false.' => ':context :attribute must be true or false.',
    ':context :attribute must be an array.' => ':context :attribute must be an array.',
    ':context :attribute must be a valid URL.' => ':context :attribute must be a valid URL.',
    ':context :attribute must be a valid email address.' => ':context :attribute must be a valid email address.',
    ':context :attribute must be a valid date.' => ':context :attribute must be a valid date.',
    ':context selected :attribute is invalid.' => ':context selected :attribute is invalid.',
    ':context :attribute must be at least :min.' => ':context :attribute must be at least :min.',
    ':context :attribute may not be greater than :max.' => ':context :attribute may not be greater than :max.',

    // Specific messages for your use case
    ':context :attribute must not be 0.' => ':context :attribute must not be 0.',
    ':context :attribute format is invalid.' => ':context :attribute format is invalid.',
    ':context :attribute has already been taken.' => ':context :attribute has already been taken.',
    ':context :attribute must be between :min and :max.' => ':context :attribute must be between :min and :max.',

    // Messages for address fields
    ':context address field is required.' => ':context address field is required.',
    ':context address must be an array.' => ':context address must be an array.',
    ':context street field is required.' => ':context street field is required.',
    ':context city field is required.' => ':context city field is required.',
    ':context ZIP code field is required.' => ':context ZIP code field is required.',
    ':context country ID field is required.' => ':context country ID field is required.',

    // Messages for phone numbers
    ':context PSTN format is invalid.' => ':context PSTN format is invalid.',
    ':context SIM format is invalid.' => ':context SIM format is invalid.',
    ':context SIP format is invalid.' => ':context SIP format is invalid.',
    ':context PBX format is invalid.' => ':context PBX format is invalid.',

    // Messages for device-specific fields
    ':context device ID field is required.' => ':context device ID field is required.',
    ':context device equipment must be a string.' => ':context device equipment must be a string.',
    ':context device identity must be a string.' => ':context device identity must be a string.',
    ':context device module must be a string.' => ':context device module must be a string.',
    ':context device PIN must be a string.' => ':context device PIN must be a string.',
    ':context device module cannot be 0.' => ':context device module cannot be 0.',
    ':context device alarm number must be a string.' => ':context device alarm number must be a string.',
    ':context device periodical number must be a string.' => ':context device periodical number must be a string.',

    // Messages for custom fields
    ':context cloned custom fields must be an array.' => ':context cloned custom fields must be an array.',
    ':context DS Name must be a string.' => ':context DS Name must be a string.',
    ':context DS Link must be a string.' => ':context DS Link must be a string.',
    ':context DS Link must be a valid URL.' => ':context DS Link must be a valid URL.',
];