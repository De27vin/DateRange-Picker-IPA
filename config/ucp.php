<?php

return [
    'version' => '2.1-26',
    'active_labels' => true,

    'notifications' => [
        'invite_subject' => env('INVITE_EMAIL_SUBJECT', 'Invite'),
    ],

    'import' => [
        'allowed_roles' => array_filter(array_map('trim', explode(',', env('IMPORT_ALLOWED_ROLES', 'site')))),
    ],

    'charts' => [
        'use_dummy_data' => filter_var(env('CHARTS_USE_DUMMY_DATA', false), FILTER_VALIDATE_BOOLEAN),
    ],

    // TODO: temporary solution for liftcare subtenants - remove after implementing granular permissions system
    'subtenant' => [
        'custom_field_name' => env('SUBTENANT_CUSTOM_FIELD_NAME', 'customer'),
        'accounts' => array_filter(array_map('trim', explode(',', env('SUBTENANT_ACCOUNTS', 'liftcare')))),
    ],

    'versions' => [
        '0-0-0' => '',
        '0-0-1' => 'updateProfile_2023_10_05'
    ],
    'theme' => 'v1',
    'pages' => [
        'ucp.dashboard' => [
            'title' => 'Devices - Alerts & Errors',
            'description' => 'Shows all active device alerts, including their number. Clicking on an alert badge displays the corresponding devices.',
            'showTitle' => 0,
            'slug' => 'ucp/dashboard',
            'roles' => ['user']
        ],
        'ucp.devices' => [
            'title' => 'Device List',
            'description' => 'Description of device list page',
            'showTitle' => 1,
            'slug' => 'ucp/devices',
            'roles' => ['user']
        ],
        'ucp.gateways' => [
            'title' => 'Gateways',
            'description' => 'Manage gateways',
            'showTitle' => 1,
            'slug' => 'ucp/gateways',
            'roles' => ['user']
        ],
        'ucp.device-details' => [
            'title' => 'Device Detail',
            'description' => 'Description of device detail page',
            'showTitle' => 1,
            'slug' => 'ucp/device-details',
            'roles' => ['user']
        ],
        'ucp.device-create' => [
            'title' => 'New Installation',
            'description' => 'Description of device create page',
            'showTitle' => 1,
            'slug' => 'ucp/device-create',
            'roles' => ['admin']
        ],
        'settings.translations' => [
            'title' => 'Translations',
            'description' => 'Manage translations for alert types and form field labels',
            'showTitle' => 1,
            'slug' => 'settings/translations',
            'roles' => ['admin']
        ],
        'settings.deviceform' => [
            'title' => 'Device Form fields',
            'description' => 'Manage settings for device form fields',
            'showTitle' => 1,
            'slug' => 'settings/deviceform',
            'roles' => ['admin']
        ],
        'settings.alerts' => [
            'title' => 'Alert Types',
            'description' => 'Manage settings for alert types',
            'showTitle' => 1,
            'slug' => 'settings/alerts',
            'roles' => ['admin']
        ],
        'settings.users' => [
            'title' => 'Users',
            'description' => 'Manage settings for users',
            'showTitle' => 1,
            'slug' => 'settings/users',
            'roles' => ['admin']
        ],
        'settings.invites' => [
            'title' => 'Invites',
            'description' => 'Manage user invites',
            'showTitle' => 1,
            'slug' => 'settings/invites',
            'roles' => ['admin']
        ],
        'admin.accounts' => [
            'title' => 'Accounts',
            'description' => 'Select the account you want to be logged into',
            'showTitle' => 1,
            'slug' => 'admin/accounts',
            'roles' => ['admin']
        ],
        'admin.alarm' => [
            'title' => 'Alarm Callcenter',
            'description' => '',
            'showTitle' => 1,
            'slug' => 'admin/alarm',
            'roles' => ['agent']
        ],
        'admin.profile' => [
            'title' => 'Account profile',
            'description' => 'Manage Account profile data',
            'showTitle' => 1,
            'slug' => 'admin/profile',
            'roles' => ['user']
        ],
    ]
];


