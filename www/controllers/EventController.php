<?php

namespace app\controllers;

use Yii;
use my\yii2\RestController;
use yii\base\InvalidParamException;
use app\services\EventScan;

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
        /**
        1-й get запрос - кэшируется, get by number or code
        name
        type
        typeDescription
        optionalParameters
        brand
        number
        image
        brandLogo
        description
         *
         *  (po url number or code) and userId (uuid and phoneNumber)
         */
        return [
            'test' => $this->getParams('test'),
            'params' => $this->getParams(),
        ];
    }

    public function actionDynamicInfo()
    {
        /**
        2-й get запрос - get by number or code (number/ or code/)
        status
        scans
        получает uuid, macid или phoneNumber
         *
         *  (po url number or code) and userId (uuid and phoneNumber)
         */
        return [
            'test' => $this->getParams('test'),
            'params' => $this->getParams(),
        ];
    }

    public function actionTest($number = null, $code = null)
    {
        var_dump($number);
        var_dump($code);
    }

} 