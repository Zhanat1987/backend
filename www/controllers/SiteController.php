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
        $response = [
            'unit' => 'test'
        ];
//        return $response;
//        VarDumper::dump($response, 10, true);
//        echo 1;
//        VarDumper::dump(base64_encode('wiponwiponJ/Y3}T6VP%jB-t,'), 10, true);
//        $json = json_encode([
//            'latitude' => 51.132349,
//            'longitude' => 71.402818,
//            'time' => 1391310060,
//            'device' => '482C6A1E593D',
//        ]);
//        VarDumper::dump($json, 10, true);
    }

    public function actionHtml()
    {
        try {
            $test = [];
        } catch (Exception $e) {
            Yii::$app->exception->register($e);
        }
        return $this->render('html', [
            'test' => $test ? : false,
        ]);
    }

    public function actionPhpInfo()
    {
        phpinfo();
    }

}