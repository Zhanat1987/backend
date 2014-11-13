<?php
namespace Core\DescriptionWriter;
use Core\Logger;

/**
 * ExtDirect
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class ExtDirect extends \Core\DescriptionWriter {

    /**
     * Write the description
     *
     * @param array $description The array of described classes->methods->params
     * @param string $namespace The namespace of the classes
     * @param array $namespaces The list of all namespaces
     * @return string The description
     */
    function write($description, $namespace, $namespaces) {

        // convert API config to Ext.Direct spec
        $actions = array();

        foreach ($description as $className => &$classMethods) {

            $methods = array();

            foreach ($classMethods as $methodName=> &$methodDetails) {
                $params = array();
                $pos    = 0;
                foreach ($methodDetails['parameters'] as $param) {
                    $params[] = array(
                        'name' => $param['name'],
                        'type' => $param['type'],
                        'pos'  => $pos
                    );
                    $pos++;
                }

                $methods[] = array(
                    'name'        => $methodName,
                    'len'         => count($methodDetails['parameters']),
                    'params'      => $params,
                    'formHandler' => $methodDetails['formHandler']
                );

            }

            $actions[$className] = $methods;
        }

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!= 'off'){
            $defaultPort = 443;
            $protocol = 'https';
        } else {
            $defaultPort = 80;
            $protocol = 'http';
        }

        $pos = strpos($_SERVER['REQUEST_URI'], "?action=");

        if ($pos !== false) {
            $requestUri = substr($_SERVER['REQUEST_URI'], 0, $pos);
        } else {
            $requestUri = $_SERVER['REQUEST_URI'];
        }

        $url = $protocol . '://' . $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT']!=$defaultPort?':'.$_SERVER['SERVER_PORT']:'')
            . $requestUri;

        // with thanks from Puya

        $cfg = array(
            'url'       => $url,
            'type'      => 'remoting',
            'actions'   => $actions
        );

        $description = json_encode($cfg);

        // set content type as json, this way the response will be automatically
        // decoded by browser/ExtJS (don't know which one for sure)
        // header('Content-Type: application/json');

        return $description;
    }
}
