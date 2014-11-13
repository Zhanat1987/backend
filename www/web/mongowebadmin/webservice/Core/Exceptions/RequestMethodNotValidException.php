<?php
namespace Core\Exceptions;

/**
 * RequsetMethodNotValidException
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class RequestMethodNotValidException extends \Exception {

    /**
     * Request Method not valid Exception
     *
     * @param string $expectedMethod The Expected Request Method
     * @param string $receivedMethod The Received Request Method
     */
    public function __construct($expectedMethod, $receivedMethod) {
        parent::__construct("Expecting `$expectedMethod`, received `$receivedMethod`");
    }

}

