<?php
namespace Application\Exceptions;
/**
 * UserDoesNotExist
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class WrongArgumentType extends \Core\Exceptions\WebServiceException {

    /**
     * Creates exception
     *
     * @param string $argumentName The name of the argument
     */
    public function __construct($argumentName) {
        parent::__construct("Parameter `$argumentName` has wrong type");
    }

}
