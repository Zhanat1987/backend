<?php
namespace Core;
/**
 * ErrorHandler - Handles error reporting for development errors
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class ErrorHandler {

    /**
     * Creates error handler instance with config array
     *
     * @param array $config
     */
    public static function setConfig($config = array()) {
        ini_set('display_errors', $config['displayErrors'] ? 'On' : 'Off');

        $errorHandler = new ErrorHandler();

        if ($config['exceptionHandler']) {
            set_exception_handler(array($errorHandler, 'exceptionHandler'));
        }

        if ($config['errorHandler']) {
            set_error_handler(array($errorHandler, 'phpErrorHandler'));
            register_shutdown_function(array($errorHandler, 'shutdownHandler'));
        }
    }

    /**
     * Handles common php errors
     *
     * @param int $errorNumber
     * @param string $errorString
     * @param string $errorFile
     * @param int $errorLine
     */
    public function phpErrorHandler($errorNumber, $errorString, $errorFile, $errorLine) {

        // list of error code numbers by level
        $levelsCodes = array(
            'error'   => array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR),
            'warning' => array(E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING),
            'notice'  => array(E_NOTICE, E_USER_NOTICE)
        );

        // find the corresponding level
        foreach ($levelsCodes as $level => $levelCodes) {
            if (in_array($errorNumber, $levelCodes)) {
                break;
            }
        }


        // log by level
        Logger::$level(ucfirst($level) . ":\n"
                           . "    Message: $errorString\n"
                           . "    File: $errorFile\n"
                           . "    Line: $errorLine\n"
        );
    }

    /**
     * Handles uncaught php exceptions
     *
     * @param Exception $exception The uncaught exception
     */
    public function exceptionHandler($exception) {
        Logger::error("Uncaught exception " . get_class($exception) . "\n"
                          . "    Message: " . $exception->getMessage() . "\n"
                          . "    File: " . $exception->getFile() . "\n"
                          . "    Line: " . $exception->getLine() . "\n"
                          . "    Trace: " . $exception->getTraceAsString() . "\n"
        );
    }

    /**
     * Handles fatal php errors uncaught by errorHandler
     */
    public function shutdownHandler() {
        $error = error_get_last();
        if (in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR))) {
            self::phpErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}

