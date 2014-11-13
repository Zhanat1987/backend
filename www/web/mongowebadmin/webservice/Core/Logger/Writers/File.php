<?php
namespace Core\Logger\Writers;
/**
 * File
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class File implements \Core\Logger\IWritter {

    /**
     * @var string The filename where to write the log
     */
    protected $_fileName;

    /**
     * @var bool True to use bash colors
     */
    protected $_useColors = false;

    /**
     * Create new File Logger Writer
     *
     * @param array $config The config array that must contain the file name and the overwrite option
     */
    public function __construct($config) {

        if (!file_exists($config['filename'])) {
            file_put_contents($config['filename'], '');
            $this->_fileName = realpath($config['filename']);
        } else {
            $this->_fileName = realpath($config['filename']);
            if($config['overwrite']){
                file_put_contents($this->_fileName, '');
            }
        }

        if (isset($config['colors']) && $config['colors']) {
            $this->_useColors = true;
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
     * @param string $message Message to be written. More arguments may follow message.
     */
    public function write($level, $prefix, $message) {
        $message = func_get_args();
        array_shift($message); // remove level
        array_shift($message); // remove prefix

        $message = implode(' ', array_map(function($item) { return File::format($item); }, $message));

        $colorStart = '';
        $colorEnd   = '';

        if ($this->_useColors) {
            $colors = array(
                'error' => '0;31',
                'warning' => '0;33',
                'notice' => '1;33',
                'info' => '1;34',
                'debug' => '1;32'
            );

            $color = $colors[\Core\Logger::getLevelName($level)];


            $colorStart = chr(27) . "[{$color}m";
            $colorEnd   = chr(27) . "[0m";

        }

        if( $this->_fileName ){
            file_put_contents($this->_fileName, "$colorStart$prefix$message$colorEnd\n", FILE_APPEND);
        }

    }
}
