<?php
namespace Core\Exceptions;
/**
 * RouterMethodException
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class RouterException extends \Exception {

    /**
     * Method does not exist
     */
    const DOES_NOT_EXIST = 1;

    /**
     * Method is not accessible ( is not public or static )
     */
    const NOT_ACCESSIBLE = 2;

    /**
     * Method has missing parameters
     */
    const MISSING_PARAMETERS = 3;

    /**
     * @param string $object The name of the object that has errors
     * @param int $type The type of error, one of the class constants
     */
    public function __construct($object, $type) {

        switch ($type) {
            case self::DOES_NOT_EXIST:
                $message = 'does not exist';
                break;
            case self::NOT_ACCESSIBLE:
                $message = 'is not accessible';
                break;
            case self::MISSING_PARAMETERS:
                $message = 'has missing parameters';
                break;
        }
        parent::__construct("'$object' $message");
    }

}
