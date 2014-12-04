<?php

namespace my\traits;

/**
 * Class Singleton
 * @package my\traits
 *
 * @link http://anton.shevchuk.name/php/php-traits/
 * @link http://php.net/manual/ru/language.oop5.traits.php
 */
trait Singleton
{

    protected static $instance;

    protected function __construct()
    {
        static::setInstance($this);
    }

    final public static function setInstance($instance)
    {
        static::$instance = $instance;
        return static::$instance;
    }

    final public static function getInstance()
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static;
    }

}