<?php

namespace Mysli\Core\Lib;

class Cfg
{
    private $configs = []; // All configurations values
    private $cache   = []; // Cached values, for faster access
    private $master  = ''; // The master config file, to which changes
                           // will be saved.

    /**
     * Construct CONFIG
     * --
     * @param array $config
     *   - cfgfile = Config master file
     * @param array $dependencies
     *   - none
     */
    public function __construct(array $config = [], array $dependencies = [])
    {
        $filename = \Arr::element('cfgfile', $config);

        // In case of wrong filename!
        if (!file_exists($filename)) {
            throw new \Mysli\Core\FileNotFoundException(
                "File not found: '{$filename}'."
            );
        }

        if (substr($filename, -5) === '.json') {
            $cfg = json_decode(file_get_contents($filename), true);
        } else {
            include($filename);
        }

        if (!isset($cfg)) {
            trigger_error(
                "File was loaded {$filename}, but \$cfg variable isn't set!",
                E_USER_WARNING
            );
        }

        $this->master = $filename;
        $this->append($cfg);
    }

    /**
     * Append config
     * --
     * @param  array $config
     * --
     * @return void
     */
    public function append(array $config)
    {
        // First we'll clear all cached values
        $this->cache = array();

        // Assign new merged version of config
        $this->configs = \Arr::merge($this->configs, $config);
    }

    /**
     * Will write changes to file, if no filename is provided,
     * the $this->master will be used.
     * --
     * @param  mixed $filename
     * --
     * @return boolean
     */
    public function write($filename = null)
    {
        $filename = $filename ? $filename : $this->master;
        return file_put_contents($filename, json_encode($this->config));
    }

    /**
     * Dump current cache and config.
     * --
     * @return array
     */
    public function dump()
    {
        return [$this->cache, $this->configs];
    }

    /**
     * Get Config Item by Path.
     * --
     * @param  string  $key      In format: key/subkey
     * @param  mixed   $default  Default value, if config isn't set
     * --
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = \Arr::get_by_path($key, $this->configs, $default);
        }

        return $this->cache[$key];
    }

    /**
     * Overwrite particular config key, this is temporary action,
     * the changes won't get saved, until you call $this->write();
     * --
     * @param   string  $path   In format: key/subkey
     * @param   mixed   $value
     * --
     * @return  void
     */
    public function set($path, $value)
    {
        # Clear cache to avoid conflicts
        $this->cache = [];

        \Arr::set_by_path($path, $value, $this->configs);
    }
}
