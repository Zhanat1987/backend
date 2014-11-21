<?php

return [
    'adminEmail' => 'admin@example.com',
    'response' => [
        'success' => [
            'code' => 200,
            'status' => 'ok',
            'message' => 'Всё ништяк!!!',
        ],
        'error' => [
            'code' => 400,
            'status' => 'error',
        ],
        'empty' => [
            'code' => 200,
            'status' => 'empty',
        ],
    ],
    'cacheDuration' => [
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
    ],
];
