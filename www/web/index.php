<?php

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
mb_http_input('UTF-8');
mb_http_output('UTF-8');
setlocale(LC_ALL, 'ru_RU.UTF-8');
setlocale(LC_NUMERIC, 'en_US.UTF-8');
ini_set('date.timezone','Asia/Almaty');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

defined('SITE_URL') or define('SITE_URL', 'http://wipon-api.local:8080');

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../config/bootstrap.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
