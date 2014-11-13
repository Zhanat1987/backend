<?php
namespace Core\OutputWriter;
/**
 * Html
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Html extends \Core\OutputWriter {

    /**
     * Writes the response
     */
    protected function _writeResponse() {
        // send output to browser with <pre> for nice formatting
        echo "<pre>" . var_export($this->_response, true);
    }

    /**
     * Writes an exception
     */
    protected function _writeException() {
        echo "<pre>" . get_class($this->_response) . PHP_EOL . $this->_response->getMessage() . PHP_EOL . $this->_response->getCode() . PHP_EOL . ' Line: ' . $this->_response->getLine() . ' File: ' . PHP_EOL . $this->_response->getFile();
    }
}
