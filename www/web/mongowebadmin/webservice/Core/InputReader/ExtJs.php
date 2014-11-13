<?php
namespace Core\InputReader;
/**
 * ExtJs
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class ExtJs extends HTTP {

    /**
     * Reads the request info for ExtJs protocol and sets internal properties
     *
     * @throws \Core\Exceptions\RequestNotValidException
     */
    public function _readRequest() {

        // basic HTTP handler
        parent::_readRequest();

        // request fields names
        $requestFields = array('class', 'method');

        // create an array with request fields as keys
        $requestFieldsKeys = array_fill_keys($requestFields, null);

        // check to see if request fields are sent
        foreach ($requestFields as $key) {
            if (!isset($_REQUEST[$key]) || $_REQUEST[$key] === '') {
                throw new \Core\Exceptions\RequestNotValidException($key, \Core\Exceptions\RequestNotValidException::DOES_NOT_EXIST);
            }
        }

        $this->_class  = $_REQUEST['class'];
        $this->_method = $_REQUEST['method'];

        // get the other parameters from $_REQUEST
        $this->_arguments = array_diff_key($_REQUEST, $requestFieldsKeys);

        // depending on the request method, parameters will be decoded differently
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            switch ($_SERVER['CONTENT_TYPE']) {
                case 'application/json':
                    $content          = file_get_contents('php://input');
                    $this->_arguments = array_merge($this->_arguments, json_decode($content, true));
                    break;

                case 'application/x-www-form-urlencoded':
                    $this->_arguments = array_merge($this->_arguments, $_POST);
                    break;

                default:
                    if (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === 0) {
                        $this->_arguments = array_merge($this->_arguments, $_FILES);
                    } else {
                        throw new \Core\Exceptions\RequestNotValidException('Content-type', \Core\Exceptions\RequestNotValidException::NOT_VALID);
                    }
            }
        }

    }
}
