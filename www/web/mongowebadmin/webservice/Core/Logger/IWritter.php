<?php
namespace Core\Logger;

/**
 * IWriter - Logger Writer Interface
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
interface IWritter {

    /**
     *  Writes log message
     *
     * @param int $level The level to witch the message is written
     * @param string $prefix Prefix to be added before message
     * @param string $msg Message to be written. More arguments may follow message.
     */
    public function write($level, $prefix, $message);
}
