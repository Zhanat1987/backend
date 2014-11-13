<?php
namespace Core;
/**
 * DescriptionWriter
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
abstract class DescriptionWriter {
    /**
     * Write the description
     *
     * @abstract
     * @param $description The array of described classes->methods->params
     * @param $namespace The namespace of the classes
     * @param array $namespaces The list of all namespaces
     * @return string The description
     */
    abstract function write($description, $namespace, $namespaces);
}
