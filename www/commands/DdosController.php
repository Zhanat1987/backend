<?php

namespace app\commands;

use Yii;
use my\yii2\ConsoleController;
use app\services\CheckUser;

class DdosController extends ConsoleController
{

    public function actionCheckAllUsers()
    {
        if ((new CheckUser())->all()) {
            return ConsoleController::EXIT_CODE_NORMAL;
        }
        return ConsoleController::EXIT_CODE_ERROR;
    }

}