<?php
namespace Core\DescriptionWriter;
/**
 * Html
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Html extends \Core\DescriptionWriter {

    /**
     * Write the description
     *
     * @param $description The array of described classes->methods->params
     * @param $namespace The namespace of the classes
     * @param array $namespaces The list of all namespaces
     * @return string The description
     */
    public function write($description, $namespace, $namespaces) {
        ob_start();
        include realpath(dirname(__FILE__) . '/description.phtml');
        ob_end_flush();
    }

}
