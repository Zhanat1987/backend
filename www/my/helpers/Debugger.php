<?php

namespace my\helpers;

use Yii;
use yii\base\Exception as YiiException;

/**
 * Class Debugger
 * @package my\helpers
 * класс-отладчик кода
 */
class Debugger
{

    public static function debug($v)
    {
        echo '<pre style="background-color: #000; color: #fff; font-size: 14px;
                    font-weight: 600; line-height: 18px; margin: 20px;
                    padding: 20px; border: 3px solid #00FF40;  border-radius: 10px;">';
        var_dump($v);
        echo '</pre>';
    }

    public static function stop($v)
    {
        exit(self::debug($v));
    }

    public static function margin()
    {
        echo '<br /><br /><br /><br /><br />';
    }

}