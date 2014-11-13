<?php
namespace Core;
/**
 * WebService
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class WebService {

    public function __construct() {
        Logger::setConfig(new Config('Config/logger.php'));
        ErrorHandler::setConfig(new Config('Config/errors.php'));
    }

    /**
     * Activate the router and call the requested method
     */
    public function call() {
        $router = Router::createRouter(new Config('Config/router.php'));
        $router->routeRequest();
    }

    /**
     * Describe the exported classes of the web service
     */
    public function describe() {

        $describer = new Describer(new Config('Config/describer.php'));

        $describer->describe();
    }

    /**
     * Gets the namespaces of the web service
     */
    public function getNamespaces() {
        $config = new Config('Config/describer.php');

        $describer  = new Describer($config);
        $namespaces = $describer->getNamespaces();

        $writer = OutputWriter::createOutputWriter($config['namespaceWriter']);

        $writer->setResponse($namespaces);
    }


}
