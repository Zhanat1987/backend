<?php
namespace Core;
/**
 * Describer
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Describer {

    /**
     * @var string The directory of the web service
     */
    protected $_webServiceDirectory;

    /**
     * @var string The namespace of the web service classes
     */
    protected $_namespace;

    /**
     * @var string The description writer
     */
    protected $_descriptionWriter;

    /**
     * Creates a describer object instance, that gets information about exported classes
     *
     * @param array|Config $config
     */
    public function __construct($config) {
        $this->_namespace           = $config['namespace'];
        $this->_webServiceDirectory = $config['directory'];
        $this->_descriptionWriter   = $config['writer'];
    }

    /**
     * Describes the WebService exported classes
     *
     * @param string $namespace The namespace for the exported classes (optional)
     */
    public function describe($namespace = '') {

        $webServiceDirectory = realpath($this->_webServiceDirectory . '/');

        $classes = $this->getExportedClasses($webServiceDirectory);

        $details = $this->getClassesDetails($classes, '');

        // Access-Control-Allow-Origin response header should be set every time Origin header is
        // sent, no matter what the request method was!
        // Also if Origin was set, most probably Access-Control-Allow-Credentials is also
        // requested (because ExtJS.Ajax.withCredentials is set to true, indicating that
        // cookies should also be sent with the request)
        if (isset($_SERVER['HTTP_ORIGIN'])){
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
        }
        // first request is OPTIONS and the following headers must be sent as response
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header('Access-Control-Allow-Methods: POST, GET , OPTIONS');
            header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Accept-Encoding, Accept-Language, Connection, Host, Origin, Referer, User-Agent, x-insight');
            exit();
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            // if is AJAX request then use the configured writer
            $writerClassName = '\\Core\\DescriptionWriter\\' . $this->_descriptionWriter;

        } else {
            // if is not AJAX request then use the html writer
            $writerClassName = '\\Core\\DescriptionWriter\\Html';

        }

        $namespaces = $this->getNamespaces();

        $writer = new $writerClassName;

        echo $writer->write($details, '', $namespaces);

        exit();
    }

    /**
     * Gets all the namespace exported by the web service
     *
     * @param string $directory The directory from witch to start looking for namespaces
     * @return array List of classes
     */
    public function getNamespaces($directory = '') {

        if ($directory == '') {
            $directory = realpath($this->_webServiceDirectory);
        }

        $namespace = str_replace('/', '\\', substr($directory, strlen(realpath($this->_webServiceDirectory)) + 1));

        $files = scandir($directory);

        $classes    = array();
        $namespaces = array();

        // get each file in the current directory and get details about the class defined in it
        // foreach directory recursively call this function and get the namespaces to merge with the current result
        foreach ($files as $file) {

            if ($file == '.' || $file == '..') {
                continue;
            }

            $path = $directory . '/' . $file;

            if (is_dir($path)) {
                $namespaces = array_merge($namespaces, $this->getNamespaces($path));
            } else {
                $className = ($namespace ? $namespace . '\\' : '') . str_replace('.php', '', $file);
                $classes[] = '\\' . $this->_namespace . '\\' . $className;
            }
        }

        $details = array();

        // for the classes in the current directory get details
        foreach ($classes as $className) {
            $reflectionObject    = new \ReflectionClass($className);
            $details[$className] = $this->getMethodsDetails($reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC));
        }

        // remove classes that do not have any methods
        $details = array_filter($details, function($methodsDetails) {
                return count($methodsDetails) > 0;
            }
        );

        // add the current namespace to the result if it has classes
        if (count($details)) {
            $namespaces[] = $namespace;
        }

        return array_reverse($namespaces);
    }

    /**
     * Creates a list of exported classes
     *
     * @param string $directory The current directory where to scan
     * @return array The list of classes as strings
     */
    public function getExportedClasses($directory) {

        $files = scandir($directory);

        $classes = array();

        foreach ($files as $file) {

            if ($file == '.' || $file == '..') {
                continue;
            }

            $path = $directory . '/' . $file;

            if (!is_dir($path)) {
                $className = str_replace('.php', '', $file);
                $classes[] = $className;
            }
        }

        return $classes;
    }

    /**
     * Get details about classes in given array, if class has no methods then it is omitted
     *
     * @param array $classes An array of class names to get details for
     * @param string $namespace The namespace of the classes without the namespace option from config
     * @return array An array where each key is a class name and each value an array of details for that class's public methods
     */
    public function getClassesDetails($classes, $namespace) {

        $details = array();

        if (count($classes) == 0) {
            return $details;
        }

        if ($namespace) {
            $namespace = ($this->_namespace ? '\\' . $this->_namespace : '') . '\\' . $namespace;
        } else {
            $namespace = ($this->_namespace ? '\\' . $this->_namespace : '');
        }

        foreach ($classes as $className) {
            $reflectionObject    = new \ReflectionClass(($namespace ? $namespace . '\\' : '') . $className);
            $details[$className] = $this->getMethodsDetails($reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC));
        }

        // remove classes that do not have any methods
        $details = array_filter($details, function($methodsDetails) {
                return count($methodsDetails) > 0;
            }
        );

        return $details;
    }

    /**
     * Gets details for every passed method
     *
     * @param ReflectionMethod[] $methods The methods got from Reflection
     * @return array An array where each key is the method name and each value is an array containing details like: description, parameters and return
     */
    public function getMethodsDetails($methods) {

        $methodsDetails = array();

        foreach ($methods as $method) {

            // do not allow constructors or destructors
            if ($method->isConstructor() || $method->isDestructor()) {
                continue;
            }

            // do not allow methods that start with underscore
            if (substr($method->getName(), 0, 1) == '_') {
                continue;
            }

            // get docBlock details array
            $docBlock = $this->parseDocComment($method->getDocComment());

            // match real method parameters with parameters from doc block
            $parameters = array_fill_keys(array_map(function($parameter) {
                                                   return $parameter->getName();
                                               }, $method->getParameters()
                                          ), null
            );

            $parameters = array_intersect_key($docBlock['parameters'], $parameters);

            $methodsDetails[$method->getName()] = array(
                'description' => $docBlock['description'],
                'parameters'  => $parameters,
                'return'      => $docBlock['return'],
                'formHandler' => $docBlock['formHandler']
            );
        }

        return $methodsDetails;
    }

    /**
     * Parses doc comment and extracts: description, parameters, return and fromHandler
     *
     * @param string $docComment The docComment retrieved with Reflection
     * @return array An array of details having keys: description, parameters, return, formHandler
     */
    public function parseDocComment($docComment) {

        // eliminate doc comment start syntax /*
        $docComment = preg_replace('/^\/\*\*/', '', $docComment);

        // eliminate doc comment end syntax */
        $docComment = preg_replace('/\*\/$/', '', $docComment);

        // eliminate doc comment line syntax \t*
        $docComment = preg_replace('/ +\*/', '', $docComment);

        // split text into lines
        $lines = explode("\n", $docComment);

        // the description text
        $description = '';

        // the parameters details
        $parameters = array();

        // whether or not method is a form action
        $formHandler = false;

        // the return details
        $return = array();

        // for every line extract appropriate information
        foreach ($lines as $line) {

            // skip empty lines
            if (preg_match('/^\s*$/', $line)) {
                continue;
            }

            // eliminate heading and trailing blanks
            $line = trim($line);

            // if line is param or return
            if (preg_match('/^\@(param|return|throws|author|formHandler)/i', $line, $match)) {

                // split line by space
                $paramParts = explode(' ', $line);

                // switch after line type (param or return)
                switch ($match[1]) {

                    case 'param':
                        $param                      = array();
                        $param['type']              = $paramParts[1];
                        $param['name']              = isset($paramParts[2]) ? $paramParts[2] : '';
                        $param['name']              = preg_replace('/^\$/', '', $param['name']);
                        $param['description']       = isset($paramParts[3]) ? implode(' ', array_slice($paramParts, 3)) : '';
                        $parameters[$param['name']] = $param;
                        break;

                    case 'return':
                        $return['type'] = $paramParts[1];

                        // return may have or not a description
                        if (isset($paramParts[2])) {
                            $return['description'] = implode(' ', array_slice($paramParts, 2));
                        } else {
                            $return['description'] = '';
                        }
                        break;

                    case 'formHandler':
                        $formHandler = true;
                        break;

                    case 'throws':
                    case 'author':
                        break;
                }

            } else {
                // if line is not param nor return then consider it to be part of description so append it with a newline
                $description .= "$line\n";
            }

        }

        return array(
            'description' => $description,
            'parameters'  => $parameters,
            'return'      => $return,
            'formHandler' => $formHandler
        );
    }
}
