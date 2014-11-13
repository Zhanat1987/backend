<?php
namespace Core;
/**
 * Router
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
abstract class Router {

    /**
     * @var array An array where the keys are the responses from the _choose method and the values are arrays with reader and writer keys
     */
    protected $_onCondition;

    /**
     * @var InputReader The input reader of the current request
     */
    protected $_inputReader;

    /**
     * @var OutputWriter The output writer of the current request
     */
    protected $_outputWriter;

    /**
     * Creates a router object with given config
     *
     * @param array $config
     * @throws Exceptions\WrongDataTypeException
     * @throws Exceptions\ArrayKeyMissingException
     */
    protected function __construct($config) {
        $this->_onCondition = $config;

        foreach ($this->_onCondition as $condition => $readWriter) {
            if (!is_array($readWriter)) {
                throw new \Core\Exceptions\WrongDataTypeException('Router::condition[' . $condition . ']', 'array', gettype($readWriter));
            }

            if (!isset($readWriter['reader'])) {
                throw new \Core\Exceptions\ArrayKeyMissingException('reader', "Router::condition[$condition]");
            }

            if (!isset($readWriter['writer'])) {
                throw new \Core\Exceptions\ArrayKeyMissingException('writer', "Router::condition[$condition]");
            }

        }

        $condition = $this->_getCondition();

        $reader = $this->_onCondition[$condition]['reader'];
        $writer = $this->_onCondition[$condition]['writer'];
        $environment = isset($this->_onCondition[$condition]['environment']) ? $this->_onCondition[$condition]['environment'] : '';

        Logger::setConfig(new Config('Config/logger.php', $environment));
        ErrorHandler::setConfig(new Config('Config/errors.php', $environment));

        $this->_inputReader  = InputReader::createInputReader($reader);
        $this->_outputWriter = OutputWriter::createOutputWriter($writer, $this->_inputReader);
    }

    /**
     * Routes the request using the configured InputReader and OutputWriter
     *
     * @throws \Core\Exceptions\RouterException
     */
    public function routeRequest() {

        $class     = $this->_inputReader->getClass();
        $method    = $this->_inputReader->getMethod();
        $arguments = $this->_inputReader->getArguments();

        // create the requested class name
        $className = '\\WebService\\' . $class;

        // check if the class exists
        if (!class_exists($className)) {
            throw new \Core\Exceptions\RouterException($className, \Core\Exceptions\RouterException::DOES_NOT_EXIST);
        }

        // create the requested object
        $object = new $className;

        // check if the method exists
        if (!method_exists($object, $method)) {
            throw new \Core\Exceptions\RouterException("$className::$method", \Core\Exceptions\RouterException::DOES_NOT_EXIST);
        }

        // get reflection of method
        $reflectionMethod = new \ReflectionMethod($object, $method);

        // do not allow private, protected or static methods to be called
        if (!$reflectionMethod->isPublic() || $reflectionMethod->isStatic()) {
            throw new \Core\Exceptions\RouterException("$className::$method", \Core\Exceptions\RouterException::NOT_ACCESSIBLE);
        }


        // get method parameters details
        $methodParameters = $reflectionMethod->getParameters();

        $optionalParameters = array();
        $requiredParameters = array();

        // get required and optional fields with default values
        foreach ($methodParameters as $reflectionParameter) {
            if ($reflectionParameter->isOptional()) {
                $optionalParameters[$reflectionParameter->getName()] = $reflectionParameter->getDefaultValue();
            } else {
                $requiredParameters[$reflectionParameter->getName()] = null;
            }
        }

        // clean parameter list from parameters that do not belong to this method
        $arguments = array_intersect_key($arguments, array_merge($optionalParameters, $requiredParameters));

        // get missing required parameters
        $missingParameters = array_keys(array_diff_key($requiredParameters, (array_intersect_key($arguments, $requiredParameters))));

        if ($missingParameters) {
            throw new \Core\Exceptions\RouterException("$className::$method", \Core\Exceptions\RouterException::MISSING_PARAMETERS);
        }

        // create list of parameters with default values
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $parameterKeys[$reflectionParameter->getName()] = $reflectionParameter->isOptional() ? $reflectionParameter->getDefaultValue() : null;
        }

        // rearrange parameters to desired order and set default values for missing parameters if they exist
        $arguments = array_merge(array_merge($requiredParameters, $optionalParameters), $arguments);

        $application = \Application::getInstance();

        $hooks = $application->getHooks();

        try {

            foreach ($hooks as $hook) {
                if ($hook instanceof \Core\WebServiceHook\BeforeMethodCallHook) {
                    $hook->call($className, $method, $arguments);
                }
            }

            Logger::info("Calling $className::$method with arguments:", $arguments);

            $result = call_user_func_array(array($object, $method), $arguments);

            Logger::info('Got result:', $result);

            foreach ($hooks as $hook) {
                if ($hook instanceof \Core\WebServiceHook\SuccessMethodCallHook) {
                    $hook->setResult($result);
                    $hook->call($className, $method, $arguments);
                }
            }

            // write the response
            $this->_outputWriter->setResponse($result);

        } catch (\Core\Exceptions\WebServiceException $exception) {
            Logger::info('Got ' . get_class($exception) . ' with message:', $exception->getMessage(),
                         'File:' . $exception->getFile(),
                         'Line:' . $exception->getLine(),
                         'Trace:'. $exception->getTraceAsString()
            );

            foreach ($hooks as $hook) {
                if ($hook instanceof \Core\WebServiceHook\ExceptionMethodCallHook) {
                    $hook->setResult($exception);
                    $hook->call($className, $method, $arguments);
                }
            }

            $result = $exception;

            // expected web service exception caught
            $this->_outputWriter->setException($exception);

        } catch (\Exception $exception) {

            // unexpected web service exception caught
            Logger::error('Unexpected exception ' . get_class($exception) . ' thrown with message:', $exception->getMessage(),
                          'File:' . $exception->getFile(),
                          'Line:' . $exception->getLine(),
                          'Trace:' . $exception->getTraceAsString()

            );

            foreach ($hooks as $hook) {
                if ($hook instanceof \Core\WebServiceHook\ExceptionMethodCallHook) {
                    $hook->setResult($exception);
                    $hook->call($className, $method, $arguments);
                }
            }

            $result = $exception;

            $this->_outputWriter->setException(new \Exception("Internal server error"));
        }

        foreach ($hooks as $hook) {
            if ($hook instanceof \Core\WebServiceHook\AfterMethodCallHook && !($hook instanceof \Core\WebServiceHook\SuccessMethodCallHook) && !($hook instanceof \Core\WebServiceHook\ExceptionMethodCallHook)) {
                $hook->setResult($result);
                $hook->call($className, $method, $arguments);
            }
        }

    }

    /**
     * Returns one of the possible routing conditions that should be present in the config
     *
     * @abstract
     * @return mixed
     */
    abstract protected function _getCondition();

    /**
     * Creates a router object with given config
     *
     * @static
     * @param array $config
     * @throws Exceptions\ArrayKeyMissingException
     * @return Router
     */
    public static function createRouter($config) {

        if (!isset($config['router'])) {
            throw new \Core\Exceptions\ArrayKeyMissingException('router', 'router config');
        }

        $router = $config['router'];

        if (!isset($config[$router])) {
            throw new \Core\Exceptions\ArrayKeyMissingException($router, 'router config');
        }

        $routerClassName = '\\Core\\Router\\' . ucfirst($router);

        return new $routerClassName($config[$router]);

    }

}
