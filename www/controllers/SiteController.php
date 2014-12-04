<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\caching\TagDependency;
use app\services\CheckUser;
use yii\helpers\VarDumper;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Yandex\Geo\Api;
use Exception;
use app\models\Request;
use Redis;
use yii\mongodb\Collection;
use yii\redis\Connection as RedisConnection;
use MongoClient;

class SiteController extends Controller
{

    public function actionIndex()
    {

    }

    public function actionHtml()
    {
        try {
            $result = 'test';
        } catch (Exception $e) {
            Yii::$app->exception->register($e);
        }
        return $this->render('html', [
            'test' => $result ? : false,
        ]);
    }

}