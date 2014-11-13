<?php
namespace Core;

/**
 * Description of Logger
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Logger {

    /**
     * @var array Logging levels array in order from less to most verbose
     */
    protected static $_levels = array('none', 'error', 'warning', 'notice', 'info', 'debug');

    /**
     * @var string Current log level
     */
    protected static $_level;

    /**
     * @var int The log level that the current message is being written at
     */
    protected static $_messageLogLevel;

    /**
     * @var ILogWriter Instance of multiple LogWriter class
     */
    protected static $_logWriters = array();

    /**
     * @var boolean Show timestamp in log message
     */
    protected static $_timestamp;

    /**
     * @var boolean Show a detailed log. This includes timestamp, filepath, class, method, line
     */
    protected static $_backtrace;

    /**
     * Transforms level from int to string
     *
     * @param int $level
     * @return string
     */
    public static function getLevelName($level) {
        return self::$_levels[$level];
    }

    /**
     * Transforms level from string to int
     *
     * @param string $level
     * @return int
     */
    public static function getLevelNumber($level) {
        return array_search($level, self::$_levels);
    }

    /**
     * Sets config for logger
     *
     * @param \Core\Config $config The config array for the logger
     */
    public static function setConfig(\Core\Config $config) {

        self::$_level = array_search($config['level'], self::$_levels, true);

        self::$_logWriters = array();

        foreach( $config['writers'] as $key => $value ){

            $writer = $value;
            $writerConfig = array();

            if (is_array($value)) {
                $writer = $key;
                $writerConfig = $value;
            }

            $logWriter = '\\Core\\Logger\\Writers\\'.ucfirst($writer);
            array_push(self::$_logWriters, new $logWriter($writerConfig));

        }

        self::$_timestamp = $config['timestamp'];

        self::$_backtrace = $config['backtrace'];

    }

    /**
     * Logs message
     *
     * @param int $level The level the message is being logged at
     * @param string $message The message to be logged.
     */
    protected static function _log($level, $message) {

        $prefix = '';

        if (self::$_timestamp) {
            $prefix .= '[' . date("Y-m-d H:i:s") . ']';
        }

        if (self::$_backtrace) {
            $debugBacktrace = debug_backtrace();
            $debug = $debugBacktrace[3]; // the third level is the one needed
            $prefix .= '[' . $debug['file'] . '][' . $debug['class'] . '][' . $debug['function'] . '][' . $debug['line'] . ']';
        }

        $message = func_get_args();
        array_shift($message);
        $arguments = array_merge(array($level, $prefix), $message);

        foreach(self::$_logWriters as $logWriter) {
            call_user_func_array(array($logWriter, 'write'), $arguments);
        }

    }

    /**
     * Functions with names matching loglevels will log if current level is grater or equal then log level in function name
     *
     * @param string $functionName The name of the function is the name of the log level
     * @param array $arguments The arguments passed to be logged
     */
    public static function __callStatic($functionName, $arguments) {

        $level = array_search($functionName, self::$_levels, true);

        if ($level===false) {
            throw new Exceptions\LogLevelDoesNotExistException($functionName);
        }

        if (self::$_level >= $level) {
            self::$_messageLogLevel = $level;
            call_user_func_array(array('self', '_log'), array_merge(array($level), $arguments));
        }
    }



}
