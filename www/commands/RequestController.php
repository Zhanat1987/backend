<?php

namespace app\commands;

use Yii;
use my\yii2\ConsoleController;
use Exception;
use my\app\RabbitMQ;
use app\models\Request;

class RequestController extends ConsoleController
{

    public function actionIndex()
    {
        try {
            $connection = RabbitMQ::getInstance();
            $channel = $connection->channel();
//            Request::deleteAll();
            /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
            */
            $channel->exchange_declare(RabbitMQ::REQUEST_EXCHANGE, RabbitMQ::REQUEST_EXCHANGE_TYPE, false, true, false);
            $callback = function ($msg) {
                $data = json_decode($msg->body, true);
                $request = new Request;
                $request->setAttributes($data);
                $request->save();
            };
            /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
            */
            $channel->queue_declare(RabbitMQ::REQUEST_QUEUE, false, true, false, false);
            $channel->queue_bind(RabbitMQ::REQUEST_QUEUE, RabbitMQ::REQUEST_EXCHANGE);
            $channel->basic_consume(RabbitMQ::REQUEST_QUEUE, '', false, true, false, false, $callback);
            while(count($channel->callbacks)) {
                $channel->wait();
            }
            $channel->close();
            $connection->close();
            return ConsoleController::EXIT_CODE_NORMAL;
        } catch (Exception $e) {
            Yii::$app->exception->register($e, 'continue');
            return ConsoleController::EXIT_CODE_ERROR;
        }
    }

}