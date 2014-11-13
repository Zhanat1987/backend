<?php
namespace Application\Exceptions;
/**
 * UserDoesNotExist
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class DuplicateName extends \Core\Exceptions\WebServiceException {

    /**
     * Creates duplicate name exception
     *
     */
    public function __construct() {
        parent::__construct("Duplicate name exists");
    }

}
