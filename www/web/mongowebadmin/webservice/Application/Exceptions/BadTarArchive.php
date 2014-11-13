<?php
namespace Application\Exceptions;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class BadTarArchive extends \Core\Exceptions\WebServiceException {

    public function __construct($filename) {
        parent::__construct("Bad tar archive $filename");
    }

}
