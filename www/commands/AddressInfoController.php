<?php

namespace app\commands;

use Yii;
use my\yii2\ConsoleController;
use app\services\event\AddressInfo;

class AddressInfoController extends ConsoleController
{

    public function actionIndex($scanId, $latitude, $longitude)
    {
        if (AddressInfo::execute($scanId, $latitude, $longitude)) {
            echo ConsoleController::EXIT_CODE_NORMAL;
        } else {
            echo ConsoleController::EXIT_CODE_ERROR;
        }
    }

}