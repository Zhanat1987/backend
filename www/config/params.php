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
        'suspiciousUser' => [
            'code' => 403,
            'status' => Yii::t('common', 'Access denied'),
            'message' => Yii::t('common', 'User is bot'),
        ],
        'notValid' => [
            'code' => 400,
            'status' => Yii::t('error', 'Request parameters not valid'),
        ],
    ],
    'duration' => [
        'month' => 25920000000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
    ],
    'format' => [
        'dateTime' => 'd/m/Y (H:i)',
        'date' => 'd/m/Y',
        'time' => 'H:i:s',
    ],
];