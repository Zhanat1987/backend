<?php
namespace Core\OutputWriter;
/**
 * ExtDirect
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class ExtDirect extends \Core\OutputWriter {

    protected function _setHeaders() {

        $origin = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$_SERVER['HTTP_ORIGIN'];

        // for cross-domain requests
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, GET , OPTIONS');
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, X-File-Name');
        header('Access-Control-Allow-Credentials: true');

        header('Content-Type: text/javascript');

    }


    /**
     * Writes the response
     */
    protected function _writeResponse() {

        $this->_setHeaders();

        // if response is successful then send result response
        $response = array(
            'type'   => 'rpc',
            'tid'    => $this->_inputReader->getTid(),
            'action' => $this->_inputReader->getClass(),
            'method' => $this->_inputReader->getMethod(),
            'result' => $this->_response
        );

        // echo response as JSON
        echo json_encode($response);
    }

    /**
     * Writes an exception
     */
    protected function _writeException() {

        $this->_setHeaders();

        $response = array(
            'type'          => 'exception',
            'tid'           => $this->_inputReader->getTid(),
            'message'       => $this->_response->getMessage(),
            'code'          => $this->_response->getCode(),
            'exceptionType' => get_class($this->_response)
        );

        // echo response as JSON
        echo json_encode($response);
    }
}
