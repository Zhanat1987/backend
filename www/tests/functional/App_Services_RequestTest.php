<?php

namespace tests\functional;

use app\services\Request;
use Yii;

class App_Services_RequestTest extends \Codeception\TestCase\Test
{
   /**
    * @var \FunctionalTester
    */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testMe()
    {
        Yii::$app->request->url = 'kz/event/scan';
        Yii::$app->request->method = 'post';
//        Yii::$app->request->setUrl('kz/event/scan');
        $this->assertTrue(Request::execute(rand(1, 100)), 'Request has been counted!!!');
    }

}