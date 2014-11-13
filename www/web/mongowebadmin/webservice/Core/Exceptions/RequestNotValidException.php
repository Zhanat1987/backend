<?php
namespace Core\Exceptions;
/**
 * RequestNotValidException
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class RequestNotValidException extends \Exception {

    /**
     * Used for describing the error of a key
     */
    const DOES_NOT_EXIST = 1;

    /**
     * Used for describing the error of a key
     */
    const NOT_VALID = 2;

    /**
     * @param string $key The key of the request array that has errors
     * @param int $type The type of error ( does not exist / not valid )
     */
    public function __construct($key, $type) {
        switch ($type) {
            case self::DOES_NOT_EXIST:
                $messageType = 'does not exist';
                break;
            case self::NOT_VALID:
                $messageType = 'is not valid';
                break;
        }
        parent::__construct("The '" . $key . "' " . $messageType);
    }

}
