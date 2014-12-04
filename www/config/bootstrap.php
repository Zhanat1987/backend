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

// set aliases
Yii::setAlias('@my', dirname(__DIR__) . '/my');
Yii::setAlias('@root', dirname(__DIR__));