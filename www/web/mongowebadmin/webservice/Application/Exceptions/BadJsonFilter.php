<?php
namespace Application\Exceptions;
/**
 * UserDoesNotExist
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class BadJsonFilter extends \Core\Exceptions\WebServiceException {

    /**
     * Creates exception
     */
    public function __construct() {
        parent::__construct("The JSON you have provided is invalid");
    }

}
