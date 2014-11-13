<?php
namespace Core\OutputWriter;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class ExtJs extends \Core\OutputWriter {

    /**
     * Sets response with given data
     */
    protected function _writeResponse() {

        $callback = null;

        // when using JSONP proxy the callback parameter will be sent
        if (isset($_REQUEST['callback'])) {
            $callback = $_REQUEST['callback'];
        }

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($this->_response) . ');';
        } else {
            header('Content-Type: text/html');
            echo json_encode($this->_response);
        }

    }

    /**
     * Writes an exception
     */
    protected function _writeException() {
        $this->_response = array(
            'success' => false,
            'exception' => get_class($this->_response),
            'message'   => $this->_response->getMessage(),
            'trace'     => $this->_response->getTraceAsString()
        );

        $this->_writeResponse();
    }
}
