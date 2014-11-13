<?php
namespace Core;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
abstract class AbstractApplication {

    protected static $_instance;

    /**
     * @var \Core\AbstractWebServiceHook[]
     */
    protected $_hooks = array();

    /**
     */
    private function __construct() {
        $this->init();
    }

    abstract function init();

    /**
     * @param AbstractWebServiceHook $hook
     */
    public function addHook(\Core\AbstractWebServiceHook $hook) {
        $this->_hooks[] = $hook;
    }

    /**
     * @return AbstractWebServiceHook[]
     */
    public function getHooks() {
        return $this->_hooks;
    }

    /**
     * @return \Core\AbstractApplication
     */
    public static function getInstance() {

        if (!static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }
}
