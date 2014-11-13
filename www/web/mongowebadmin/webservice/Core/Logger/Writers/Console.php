<?php
namespace Core\Logger\Writers;
/**
 * Console Logger Writer - writes loggs to console
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Console implements \Core\Logger\IWritter {

    /**
     * @var bool True to use bash colors
     */
    protected $_useColors;

    /**
     * Create new Console Logger Writer
     *
     * @param array $config The config array that must contain the colors feature on/off (boolean)
     */
    public function __construct($config) {
        $this->_useColors = true;

        if (isset($config['colors'])) {
            $this->_useColors = $config['colors'];
        }
    }

    /**
     * Formats argument for writing and returns it as string
     *
     * @param mixed $arg
     * @return string
     */
    public static function format($arg) {
        return is_object($arg)?(method_exists($arg, '__toString')?$arg->__toString():print_r($arg, true)):(is_string($arg)?$arg:var_export($arg, true));
    }

    /**
     *  Writes log message
     *
     * @param int $level The level to witch the message is written
     * @param string $prefix Prefix to be added before message
     * @param string $msg Message to be written. More arguments may follow message.
     */
    public function write($level, $prefix, $message) {
        $message = func_get_args();
        array_shift($message); // remove level
        array_shift($message); // remove prefix

        $message = implode(' ', array_map(function($item) { return Console::format($item); }, $message));

        $colors = array(
            'error' => '0;31',
            'warning' => '0;33',
            'notice' => '1;33',
            'info' => '1;34',
            'debug' => '1;32'
        );

        $color = $colors[\Core\Logger::getLevelName($level)];

        if ($this->_useColors) {
            $colorStart = chr(27)."[{$color}m";
            $colorEnd = chr(27) . "[0m";
        } else {
            $colorStart = '';
            $colorEnd = '';
        }

        echo "$colorStart$prefix$message$colorEnd\n";
        flush();
    }
}
