<?php
/**
 * Autoloads classes by namespace path
 *
 * @param string $className
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
function __autoload($className) {

    $baseDir = dirname(__FILE__);

    if (substr($className,0,1) == '\\') {
        $className = substr($className,1);
    }

    $path = $baseDir . '/' . str_replace('\\', '/', $className) . '.php';

    if (file_exists($path)) {
        include_once $path;
    }
}

spl_autoload_register('__autoload');
