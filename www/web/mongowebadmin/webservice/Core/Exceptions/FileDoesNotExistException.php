<?php
namespace Core\Exceptions;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class FileDoesNotExistException extends \Exception {

    public function __construct($file) {
        parent::__construct("Filename '$file' does not exist");
    }
}
