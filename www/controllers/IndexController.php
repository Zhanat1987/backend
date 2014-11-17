<?php

namespace app\controllers;

use Yii;
use my\yii2\RestController;

class IndexController extends RestController
{

    public function actionIndex()
    {
        return [
            'test' => $this->getParams('test'),
            'params' => $this->getParams(),
        ];
    }

} 