<?php

namespace app\controllers;

use Yii;
use my\yii2\RestController;
use my\app\FactoryMethod;

class EventController extends RestController
{

    public function actionScan()
    {
        return FactoryMethod::execute('app\services\event\Scan', $this->getParams());
    }

    public function actionStaticInfo()
    {
        return FactoryMethod::execute('app\services\event\StaticInfo', $this->getParams());
    }

    public function actionDynamicInfo()
    {
        return FactoryMethod::execute('app\services\event\DynamicInfo', $this->getParams());
    }

}