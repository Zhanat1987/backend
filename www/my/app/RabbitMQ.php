<?php

namespace my\app;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQ
 * @package my\app
 *
 * @link http://www.phptherightway.com/pages/Design-Patterns.html
 */
class RabbitMQ
{

    const REQUEST_EXCHANGE = 'request exchange';
    const REQUEST_QUEUE = 'request queue';
    const REQUEST_EXCHANGE_TYPE = 'fanout';

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instances of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
//            $instance = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
            $instance = new AMQPStreamConnection('localhost', 5672, 'test', 'test', 'wipon-api.local');
        }

        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    public static function createMessage($data)
    {
        return new AMQPMessage(json_encode($data), [
            'delivery_mode' => 2,
            'content_type' => 'application/json',
        ]);
    }

} 