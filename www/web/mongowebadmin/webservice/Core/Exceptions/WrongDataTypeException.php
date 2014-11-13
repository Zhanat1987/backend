<?php
namespace Core\Exceptions;
/**
 * WrongDataTypeException
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class WrongDataTypeException extends \Exception {
    public function __construct($variable, $expected, $received) {
        parent::__construct("Wrong data type for '$variable', expecting '$expected', got '$received'");
    }
}
