<?php
namespace Core\Logger\Writers;

/**
 * FirePHP
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class FirePHP implements \Core\Logger\IWritter {

    /**
     * The FirePHP Class
     */
    protected $_firePhp;

    protected $_collapsed = false;

    /**
     * Class constructor - Initializes the FirePHP object
     */
    public function __construct($config) {
        spl_autoload_register(function($className) {
                $filePath = "Lib/FirePhp/$className.php";
                if (file_exists($filePath)) {
                    include_once $filePath;
                }
            }
        );

        $this->_firePhp = \FirePHP::getInstance(true);

        if (isset($config['collapsed']) && $config['collapsed']) {
            $this->_collapsed = true;
        }
    }

    /**
     *  Writes log message
     *
     * @param int $level The level to witch the message is written
     * @param string $prefix Prefix to be added before message
     * @param string $msg Message to be written. More arguments may follow message.
     */
    public function write($level, $prefix, $message) {

        $level = \Core\Logger::getLevelName($level);
        $level = $level == 'warning' ? 'warn' : $level;
        $level = $level == 'notice' ? 'warn' : $level;
        $level = $level == 'debug' ? 'log' : $level;

        if ($prefix === '') {
            $prefix = ' ';
        }


        if (func_num_args() > 3) {
            $messages = func_get_args();
            array_shift($messages); // extract level
            array_shift($messages); // extract prefix

            $messages = array_filter($messages, function($msg) {
                    return $msg !== '';
                }
            );

            if (count($messages) > 1) {
                $this->_firePhp->group($prefix, array('Collapsed' => $this->_collapsed));

                foreach ($messages as $message) {
                    $this->_firePhp->$level($message);
                }

                $this->_firePhp->groupEnd();
            } else {
                $this->_firePhp->$level($prefix . ' ' . $messages[0]);
            }


        } else {

            if (is_string($message)) {
                $multiLines = array_filter(explode("\n", $message), function($msg) {
                        return trim($msg) !== '';
                    }
                );
                if (count($multiLines) > 1) {
                    $this->_firePhp->group($prefix, array('Collapsed' => $this->_collapsed));

                    foreach ($multiLines as $line) {
                        $this->_firePhp->$level($line);
                    }

                    $this->_firePhp->groupEnd();
                } else {
                    $this->_firePhp->$level($prefix . ' ' . $multiLines[0]);
                }
            } else {
                $this->_firePhp->group($prefix, array('Collapsed' => $this->_collapsed));
                $this->_firePhp->$level($message);
                $this->_firePhp->groupEnd();
            }

        }


    }
}
