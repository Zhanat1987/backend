<?php

namespace app\controllers;

use Yii;
use my\yii2\RestController;
use yii\base\InvalidParamException;
use app\services\EventScan;
use app\services\EventDynamicInfo;
use app\services\EventStaticInfo;

class EventController extends RestController
{

    public function actionScan()
    {
        if ($this->getParams('latitude') && $this->getParams('longitude') &&
            $this->getParams('time') && $this->getParams('threshold') &&
            $this->getParams('uuid') && $this->getParams('phoneNumber') &&
            $this->getParams('codeNumber') && $this->getParams('codeNumberType')) {
            return EventScan::execute($this->getParams());
        }
        throw new InvalidParamException('Переданы не все обязательные параметры', 400);
    }

    public function actionStaticInfo()
    {
        return EventStaticInfo::execute($this->getParams('codeNumber'), $this->getParams('codeNumberType'));
    }

    public function actionDynamicInfo()
    {
        return EventDynamicInfo::execute($this->getParams('codeNumber'), $this->getParams('codeNumberType'));
    }

} 