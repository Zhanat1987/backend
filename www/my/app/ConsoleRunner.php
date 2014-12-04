<?php

namespace my\app;

use Yii;

class ConsoleRunner
{

    /*
     * запустить фоновый процесс, выполнить и удалить
     */
    public static function execute($cmd)
    {
        pclose(popen('php ' . Yii::getAlias('@root') . '/yii ' . $cmd . ' /dev/null &', 'r'));
    }

}