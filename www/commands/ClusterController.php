<?php

namespace app\commands;

use Yii;
use my\yii2\ConsoleController;
use app\services\Cluster;

class ClusterController extends ConsoleController
{

    public function actionGoogle()
    {
//        Yii::$app->db->createCommand('truncate table cluster')->execute();
        if (Cluster::google()) {
            return ConsoleController::EXIT_CODE_NORMAL;
        }
        return ConsoleController::EXIT_CODE_ERROR;
    }

}