<?php

namespace app\controllers;

use Yii;
use my\yii2\RestController;
use yii\base\InvalidParamException;
use app\services\event\Scan;
use app\services\event\DynamicInfo;
use app\services\event\StaticInfo;

class EventController extends RestController
{

    public function actionScan()
    {
        if ($this->getParams('latitude') && $this->getParams('longitude') &&
            $this->getParams('time') && $this->getParams('threshold') &&
            $this->getParams('uuid') && $this->getParams('phoneNumber') &&
            $this->getParams('codeNumber') && $this->getParams('codeNumberType')) {
            return Scan::execute($this->getParams());
        }
        throw new InvalidParamException('Переданы не все обязательные параметры', 400);
    }

    public function actionStaticInfo()
    {
        return StaticInfo::execute($this->getParams('codeNumber'), $this->getParams('codeNumberType'));
    }

    public function actionDynamicInfo()
    {
        return DynamicInfo::execute($this->getParams('codeNumber'), $this->getParams('codeNumberType'));
    }

} 