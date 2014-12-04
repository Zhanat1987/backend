<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'wipon api console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'language' => 'en',
    'timezone' => 'Asia/Almaty',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
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
        'db' => $db,
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
                    'sourceLanguage' => 'en',
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
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://localhost:27017/wipon',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                'address-info/index/<scanId:\d+>/<latitude>/<longitude>' =>
                    'address-info/index',
                '<controller:[a-zA-Z0-9-]+>/<action:[a-zA-Z0-9-]+>' =>
                    '<controller>/<action>',
                '<controller:[a-zA-Z0-9-]+>/<action:[a-zA-Z0-9-]+>/<id:\d+>' =>
                    '<controller>/<action>',
                '<module:[a-zA-Z0-9-]+>/<controller:[a-zA-Z0-9-]+>/<action:[a-zA-Z0-9-]+>' =>
                    '<module>/<controller>/<action>',
                '<module:[a-zA-Z0-9-]+>/<controller:[a-zA-Z0-9-]+>/<action:[a-zA-Z0-9-]+>/<id:\d+>' =>
                    '<module>/<controller>/<action>',
            ],
        ],
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'templateFile' => '@my/templates/migration.php',
        ],
    ],
    'params' => $params,
];
