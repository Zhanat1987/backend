<?php
namespace Core;

/**
 * Description of Config
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Config implements \ArrayAccess, \Countable, \Iterator {

    /**
     * @var array Config settings
     */
    protected $_config;

    /**
     * @var string Config file name with full path and extension
     */
    protected $_file;

    /**
     * @var string The name of the loaded environment
     */
    protected $_environment;

    /**
     * @var array
     */
    protected static $_configObjects = array();

    /**
     * @var array
     */
    protected $_rawConfig;

    /**
     * Constructor loads config settings from file
     *
     * @param string $file Filename without path or extension
     * @param string $environment The name of the environment to load
     */
    public function __construct($file, $environment = '') {
        self::$_configObjects[] = $this;

        $this->_file = $file;
        $this->_environment = $environment;
        $this->_load($file);
    }

    /**
     * Returns the filename
     *
     * @return string The full path filename with extension
     */
    public function getFile() {
        return $this->_file;
    }

    /**
     * Loads the config from the raw config
     */
    protected function _loadConfig() {

        $myConfig = array();

        // get all envs
        foreach ($this->_rawConfig as $key => $value) {
            if (!preg_match('/\[[^\]]*\]/i',$key)) {
                $myConfig[$key] = $value;
            }
        }

        if ($this->_environment) {
            foreach ($this->_rawConfig as $key => $value) {
                if (preg_match('/\[(?P<env>([\w\d_.])+)(?:\s*\:\s*(?P<parent>([\w\d_.])+))?\]/i',$key, $groups)) {
                    $this->envs[$groups['env']] = array('parent' => isset($groups['parent'])?$groups['parent']:null, 'settings' => $value);
                }
            }
            if (isset($this->envs[$this->_environment])) {
                if ($this->envs[$this->_environment]['parent']) {
                    $this->envs[$this->_environment]['settings'] = $this->mergeSettings($this->envs[$this->_environment]['settings'], $this->inheritSettings($this->envs[$this->_environment]['parent']));
                }
                $myConfig = $this->mergeSettings($myConfig, $this->envs[$this->_environment]['settings']);
            }
        }

        $this->_config = $myConfig;
    }

    /**
     * Loads settings from config file
     *
     * @throws Exceptions\FileDoesNotExistException
     * @internal param string $file
     */
    protected function _load() {

        $path =  realpath($this->_file);

        if (!file_exists($path)) {
            throw new Exceptions\FileDoesNotExistException($path);
        }

        $this->_rawConfig = require $path;
        $this->_loadConfig();
    }

    /**
     * Like array_merge, but also done recursively when item is array, but unlike array_merge_recursive
     *
     * @param array $original The original array
     * @param array $settings The array you want to merge with
     * @return array The result of the merge
     */
    protected function mergeSettings($original, $settings) {

        foreach($settings as $key => $Value) {
            if (is_string($key)) {
                if(array_key_exists($key, $original) && is_array($Value)) {
                    $original[$key] = $this->mergeSettings($original[$key], $settings[$key]);
                } else {
                    $original[$key] = $Value;
                }
            } else {
                $original[] = $Value;
            }
        }

        return $original;

    }

    /**
     * Recursively gets all settings for environment
     *
     * @param string $env
     * @return array
     */
    protected function inheritSettings($env) {
        if (!isset($this->envs[$env])) {
            return array();
        }

        if (isset($this->envs[$env]['visited'])) {
            return array();
        }

        $this->envs[$env]['visited'] = true;

        if ($this->envs[$env]['parent']) {
            $this->envs[$env]['settings'] = $this->mergeSettings($this->envs[$env]['parent'], $this->envs[$env]['settings']);
        }

        return $this->envs[$env]['settings'];
    }

    /**
     * Counts settings
     *
     * @return int The number of config settings
     */
    public function count() {
        return count($this->_config);
    }

    /**
     * Checks if setting exists
     *
     * @param string $setting
     * @return bool True if the offset exists, false otherwise
     */
    public function offsetExists($setting) {
        return array_key_exists($setting, $this->_config);
    }

    /**
     * Gets setting
     *
     * @param string $setting The name of the setting
     * @return mixed The value of the setting
     */
    public function offsetGet($setting) {
        if (!isset($this->_config[$setting])) {
            throw new Exceptions\PropertyDoesNotExistException(get_class($this), $setting);
        }
        return $this->_config[$setting];
    }

    /**
     * Throws exception because settings are read only
     *
     * @param string $setting The name of the setting
     * @param mixed $value The value of the setting
     */
    public function offsetSet($setting, $value) {
        throw new Exceptions\ReadOnlyKeyException(get_class($this), $setting);
    }

    /**
     * Throws exception because settings are read only
     *
     * @param string $setting The name of the setting
     */
    public function offsetUnset($setting) {
        throw new Exceptions\ReadOnlyKeyException(get_class($this), $setting);
    }

    /**
     * Resets config array
     */
    public function rewind() {
        reset($this->_config);
    }

    /**
     * Returns current config
     *
     * @return mixed
     */
    public function current() {
        return current($this->_config);
    }

    /**
     * Gets next config
     */
    public function next() {
        next($this->_config);
    }

    /**
     * Checks if end of array is reached
     *
     * @return bool
     */
    public function valid() {
        return key($this->_config) !== null;
    }

    /**
     * Gets current setting
     *
     * @return string
     */
    public function key() {
        return key($this->_config);
    }

    /**
     * Returns settings array
     *
     * @return array
     */
    public function toArray() {
        return $this->_config;
    }

    /**
     * Returns string representing the config object
     *
     * @return string
     */
    public function __toString() {
        return print_r(array('file' => $this->_file, 'settings' => $this->_config), true);
    }

}
