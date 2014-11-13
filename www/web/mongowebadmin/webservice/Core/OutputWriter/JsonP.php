<?php
namespace Core\OutputWriter;
/**
 * ExtJs
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class JsonP extends \Core\OutputWriter {

    /**
     * Writes the response
     */
    protected function _writeResponse() {
        $callback = null;

        // when using JsonP proxy the callback parameter will be sent
        if (isset($_REQUEST['callback'])) {
            $callback = $_REQUEST['callback'];
        }

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($this->_response) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($this->_response);
        }
    }

    /**
     * Writes an exception
     */
    protected function _writeException() {
        $this->_writeResponse();
    }
}
