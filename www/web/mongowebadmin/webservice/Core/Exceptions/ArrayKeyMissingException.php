<?php
namespace Core\Exceptions;
/**
 * ArrayKeyMissingException
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class ArrayKeyMissingException extends \Exception {

    public function __construct($keyName, $arrayName = null) {
        parent::__construct("Array key $keyName is missing" . ($arrayName?" from $arrayName":''));
    }
}
