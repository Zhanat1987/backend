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
    'keys' => [
        'google' => 'AIzaSyBTpF1YwdNSuQNxCOWkfvjBY4lJzKtAcb4',
        'wikimapia' => '3207D095-82638BA5-782C7F23-3783AF82-111B4890-6D27F10C-59BCAB60-5011EB10',
        'bingMaps' => 'AuWCSOxF0zn8vjh8fJtKbl1xULyH1kduD83Sq_XV2JWSA_AS3j2kVI7J8fAEvGO8',
    ],
    'restKey' => 'wiponwiponJ/Y3}T6VP%jB-t,',
];