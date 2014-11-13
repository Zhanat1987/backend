<?php
namespace Core\Exceptions;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class PropertyDoesNotExistException extends \Exception {
    public function __construct($class, $property) {
        parent::__construct("Class '$class' does not have property '$property'");
    }
}
