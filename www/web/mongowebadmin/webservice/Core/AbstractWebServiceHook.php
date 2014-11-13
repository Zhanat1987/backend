<?php
namespace Core;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
abstract class AbstractWebServiceHook {

    /**
     * @var callback
     */
    protected $_callback;

    /**
     * @param $callback
     */
    public function __construct($callback) {
        $this->_callback = $callback;
    }

    /**
     * @param string $class
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function call($class, $method, $arguments) {
        return call_user_func_array($this->_callback, array($class, $method, $arguments));
    }

}
