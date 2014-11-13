<?php
namespace Core;
/**
 * IInputReader
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
abstract class InputReader {

    /**
     * @var string The class name
     */
    protected $_class;

    /**
     * @var string The method name
     */
    protected $_method;


    /**
     * @var array The arguments
     */
    protected $_arguments;

    /**
     * Returns the class name
     *
     * @return string
     */
    public function getClass() {
        return $this->_class;
    }

    /**
     * Returns the method name
     *
     * @return string
     */
    public function getMethod() {
        return $this->_method;
    }

    /**
     * Returns the arguments as an associative array
     *
     * @return array
     */
    public function getArguments() {
        return $this->_arguments;
    }

    /**
     * When the object is created it reads the request
     */
    protected function __construct() {
        $this->_readRequest();
    }

    /**
     * Reads the request and sets internal properties class, method and arguments
     *
     * @abstract
     * @return mixed
     */
    abstract protected function _readRequest();

    /**
     * Create and return an input reader object
     *
     * @static
     * @param string $reader The name of the reader
     * @return InputReader The object
     */
    public static function createInputReader($reader) {

        $readerClassName = '\\Core\\InputReader\\' . $reader;

        return new $readerClassName;

    }

}
