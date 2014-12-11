<?php

namespace app\services;

use Yii;
use Exception;
use my\app\RabbitMQ;

class Request
{

    public static function execute($userId)
    {
        try {
            $connection = RabbitMQ::getInstance();
            $data = [
                'userId' => $userId,
                'time' => time(),
                'url' => Yii::$app->request->url,
                'method' => Yii::$app->request->method,
            ];
            $channel = $connection->channel();
            /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
            */
            $channel->exchange_declare(RabbitMQ::REQUEST_EXCHANGE, RabbitMQ::REQUEST_EXCHANGE_TYPE, false, true, false);
            $msg = RabbitMQ::createMessage($data);
            $channel->basic_publish($msg, RabbitMQ::REQUEST_EXCHANGE);
            $channel->close();
            $connection->close();
            return true;
        } catch (Exception $e) {
            Yii::$app->exception->register($e, 'continue');
            return false;
        }
    }

} 