<?php
namespace Core\WebServiceHook;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class AfterMethodCallHook extends \Core\AbstractWebServiceHook {

    /**
     * @var mixed
     */
    protected $_result;

    /**
     * @param $result
     */
    public function setResult($result) {
        $this->_result = $result;
    }

    /**
     * @param string $class
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function call($class, $method, $arguments) {
        return call_user_func_array($this->_callback, array($class, $method, $arguments, $this->_result));
    }
}
