<?php

return [
    'host' => env('RABBITMQ_HOST', 'ucp-web-dev'),
    'port' => env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', ''),
    'password' => env('RABBITMQ_PASSWORD', ''),
    'vhost' => env('RABBITMQ_VHOST', 'ucp'),

    'queues' => [
        'sessions' => [
            'name' => 'sessions',
            'durable' => true,
            'auto_delete' => false,
            'exclusive' => false,
        ],
        'alerts' => [
            'name' => 'alerts',
            'durable' => true,
            'auto_delete' => false,
            'exclusive' => false,
        ],
        'events' => [
            'name' => 'events',
            'durable' => true,
            'auto_delete' => false,
            'exclusive' => false,
        ],
        'sets' => [
            'name' => 'sets',
            'durable' => true,
            'auto_delete' => false,
            'exclusive' => false,
        ]
    ]
];