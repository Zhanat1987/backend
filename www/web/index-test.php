<?php

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', '10.0.2.2'])) {
    die('You are not allowed to access this file.');
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '/../vendor/autoload.php');
require('Yii.php');
require(__DIR__ . '/../config/bootstrap.php');

$config = require(__DIR__ . '/../tests/codeception/config/acceptance.php');

(new WebApplication($config))->run();
