<?php
namespace Core\Exceptions;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class LogLevelDoesNotExistException extends \Exception {
    public function __construct($logLevel) {
        parent::__construct("Log level '$logLevel' does not exist");
    }
}
