<?php
namespace Core;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class AbstractWebServiceProvider {

    /**
     * @var \Application Reference to application
     */
    protected $_application;

    /**
     *
     */
    public function __construct() {
        $this->_application = \Application::getInstance();
    }

}
