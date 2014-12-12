<?php

namespace app\commands;

use Yii;
use my\yii2\ConsoleController;
use app\services\event\ScanCluster;

class ScanClusterController extends ConsoleController
{

    public function actionIndex($scanId, $latitude, $longitude, $accuracy)
    {
        if (ScanCluster::execute($scanId, $latitude, $longitude, $accuracy)) {
            return ConsoleController::EXIT_CODE_NORMAL;
        }
        return ConsoleController::EXIT_CODE_ERROR;
    }

}