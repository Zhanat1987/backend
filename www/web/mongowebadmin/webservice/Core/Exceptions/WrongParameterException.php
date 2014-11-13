<?php
namespace Core\Exceptions;
/**
 * WrongParameterException
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class WrongParameterException extends \Exception {
    public function __construct($class, $method, $parameter, $expected, $got) {
        parent::__construct("Wrong value for parameter $parameter in $class::$method. Expected: $expected, got: $got");
    }
}
