<?php
namespace Core\OutputWriter;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Raw extends \Core\OutputWriter {
    /**
     * Writes the response
     */
    protected function _writeResponse() {
        echo $this->_response;
    }

    /**
     * Writes an exception
     */
    protected function _writeException() {
        echo get_class($this->_response) . PHP_EOL . $this->_response->getMessage() . PHP_EOL . $this->_response->getCode() . PHP_EOL . ' Line: ' . $this->_response->getLine() . ' File: ' . PHP_EOL . $this->_response->getFile();
    }
}
