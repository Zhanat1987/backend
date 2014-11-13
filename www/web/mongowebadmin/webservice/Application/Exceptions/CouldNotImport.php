<?php
namespace Application\Exceptions;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class CouldNotImport extends \Core\Exceptions\WebServiceException {
    public function __construct($reason) {
        parent::__construct("Could not import: " . $reason);
    }
}
