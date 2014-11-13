<?php
namespace Core\Exceptions;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class ReadOnlyKeyException extends \Exception {
    public function __construct($class, $key) {
        parent::__construct("Array of type '$class' has key '$key' read-only");
    }
}
