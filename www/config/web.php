<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'wipon api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'en',
    'timezone' => 'Asia/Almaty',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
//            'class' => 'yii\caching\MemCache',
//            'useMemcached' => true,
//            'servers' => [
//                [
//                    'host' => '127.0.0.1',
//                    'port' => 11211,
//                    'weight' => 100,
//                ],
//            ],
        ],
        'mailer' => [
            'class'            => 'yii\swiftmailer\Mailer',
            'viewPath'         => '@app/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
//            'useFileTransport' => false,
//            'transport'        => [
//                'class'      => 'Swift_SmtpTransport',
//                'host'       => 'smtp.gmail.com',
//                'username'   => 'systemofcommunitykz@gmail.com',
//                'password'   => 'TJN~e2=Cm$',
//                'port'       => '465',
//                'encryption' => 'ssl',
//            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
//            'dsn' => 'mongodb://developer:password@localhost:27017/wipon',
//            'dsn' => 'mongodb://localhost:27017',
            'dsn' => 'mongodb://localhost:27017/wipon',
        ],
        'user' => [
            'identityClass' => 'yii\web\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['appErrors'],
                    'logFile' => '@app/runtime/logs/appErrors.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['disableUser'],
                    'logFile' => '@app/runtime/logs/disableUser.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],
            ],
        ],
        'errorHandler' => [
            'class' => 'my\yii2\ApiErrorHandler',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                '<language:(ru|kz|en)>/<controller:[a-zA-Z0-9-]+>/<action:[a-zA-Z0-9-]+>' =>
                    '<controller>/<action>',
                '<language:(ru|kz|en)>/<controller:[a-zA-Z0-9-]+>/<action:[a-zA-Z0-9-]+>/<id:\d+>' =>
                    '<controller>/<action>',
                '<language:(ru|kz|en)>/<module:[a-zA-Z0-9-]+>/<controller:[a-zA-Z0-9-]+>/<action:[a-zA-Z0-9-]+>' =>
                    '<module>/<controller>/<action>',
                '<language:(ru|kz|en)>/<module:[a-zA-Z0-9-]+>/<controller:[a-zA-Z0-9-]+>/<action:[a-zA-Z0-9-]+>/<id:\d+>' =>
                    '<module>/<controller>/<action>',
            ],
        ],
        'request' => [
            'class' => '\yii\web\Request',
            'enableCsrfValidation' => false,
            'enableCookieValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'debugger' => [
            'class' => 'my\helpers\Debugger',
        ],
        'current' => [
            'class' => 'my\helpers\Current',
        ],
        'exception' => [
            'class' => 'my\helpers\Exception',
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'common' => 'common.php',
                        'error' => 'error.php',
                    ],
                ],
                'yii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@app/messages'
                ],
            ],
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $allowedIps = ['127.0.0.1', '::1', '10.0.2.2'];
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => $allowedIps,
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module', //adding gii module
        'allowedIPs' => $allowedIps, //allowing ip's
    ];
}

return $config;
