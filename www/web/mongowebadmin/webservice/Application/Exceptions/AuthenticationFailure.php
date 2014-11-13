<?php
namespace Application\Exceptions;
/**
 * UserDoesNotExist
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class AuthenticationFailure extends \Core\Exceptions\WebServiceException {

    /**
     * Creates duplicate name exception
     *
     */
    public function __construct() {
        parent::__construct("Authentication failure");
    }

}
