<?php
namespace Core\InputReader;
/**
 * ExtDirect InputReader
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class ExtDirect extends HTTP {

    /**
     * @var string The thread id of the ExtDirect request
     */
    protected $_tid;

    /**
     * @var bool Specifies if the request is sent from a form or from AJAX
     */
    protected $_isForm;

    /**
     * @var bool Specifies if the request is an upload request
     */
    protected $_isUpload;

    /**
     * Gets the Tid
     *
     * @return string
     */
    public function getTid() {
        return $this->_tid;
    }

    /**
     * Gets if request is from a form
     *
     * @return bool True if request is from a form, false otherwise
     */
    public function isForm() {
        return $this->_isForm;
    }

    /**
     * Gets if request is from a form upload
     *
     * @return bool True if request is an upload request, false otherwise
     */
    public function isUpload() {
        return $this->_isUpload;
    }

    /**
     * Reads the request info for ExtDirect protocol and sets internal properties
     *
     * @throws \Core\Exceptions\RequestMethodNotValidException
     * @throws \Core\Exceptions\RequestNotValidException
     */
    protected function _readRequest() {

        // basic HTTP handler
        parent::_readRequest();

        // expecting request method to be POST
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            throw new \Core\Exceptions\RequestMethodNotValidException('POST', $_SERVER['REQUEST_METHOD']);
        }

        list($contentType) = explode(';', $_SERVER['CONTENT_TYPE']);

        switch ($contentType) {
            case 'application/json':
                global $HTTP_RAW_POST_DATA;
                $requestData = ((isset($HTTP_RAW_POST_DATA) && $HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents('php://input'));
                $requestData = json_decode($requestData, true);

                $this->_class     = $requestData['action'];
                $this->_method    = $requestData['method'];
                $this->_arguments = $requestData['data'];

                // ExtDirect specific info
                $this->_tid      = $requestData['tid'];
                $this->_isForm   = false;
                $this->_isUpload = false;

                break;

            case 'multipart/form-data':

                $this->_class  = $_POST['extAction'];
                $this->_method = $_POST['extMethod'];

                $this->_arguments = array_merge(array_diff_key($_POST, array_fill_keys(array('extAction',
                                                                                             'extMethod', 'extTID',
                                                                                             'extType', 'extUpload'
                                                                                       ), null
                                                                     )
                                                ), $_FILES
                );

                // ExtDirect specific info
                $this->_tid       = isset($_POST['extTID']) ? $_POST['extTID'] : null; // not set for upload
                $this->_isForm    = true;
                $$this->_isUpload = $_POST['extUpload'] == 'true';

                break;

            default:
                throw new \Core\Exceptions\RequestNotValidException('Content-type', \Core\Exceptions\RequestNotValidException::NOT_VALID);

        }

    }
}
