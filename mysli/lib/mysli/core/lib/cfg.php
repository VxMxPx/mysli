<?php

namespace Mysli\Core\Lib;

class Cfg
{
    private static $configs = []; // All configurations values
    private static $cache   = []; // Cached values, for faster access
    private static $master  = ''; // The master config file, to which changes
                                  // will be saved.

    /**
     * Will load, append and set it as main config file.
     * The "master" mean, that when you call "write",
     * all changes will get written to this file.
     * --
     * @param  string  $filename Full path to the master config file.
     *                           Please note: All changes will be written
     *                           to this file, including those appended.
     * --
     * @return boolean
     */
    public static function init($filename)
    {
        // In case of wrong filename!
        if (!file_exists($filename)) {
            trigger_error("File not found: `{$filename}`.", E_USER_ERROR);
        }

        if (substr($filename, -5) === '.json') {
            $cfg = json_decode(file_get_contents($filename), true);
        } else {
            include($filename);
        }

        if (!isset($cfg)) {
            trigger_error("File was loaded {$filename}, but \$cfg variable isn't set!", E_USER_WARNING);
        }

        self::$master = $filename;
        self::append($cfg);
    }

    /**
     * Append config
     * --
     * @param  array $config
     * --
     * @return void
     */
    public static function append($config)
    {
        // First we'll clear all cached values
        self::$cache = array();

        // Assign new merged version of config
        self::$configs = Arr::merge(self::$configs, $config);
    }

    /**
     * Will write changes to file, if no filename is provided,
     * the self::$master will be used.
     * --
     * @param  mixed $filename
     * --
     * @return boolean
     */
    public static function write($filename=null)
    {
        $filename = $filename ? $filename : self::$master;
        return file_put_contents($filename, json_encode(self::$config));
    }

    /**
     * Dump current cache and config.
     * --
     * @return array
     */
    public static function dump()
    {
        return [self::$cache, self::$configs];
    }

    /**
     * Get Config Item by Path.
     * --
     * @param  string  $key      In format: key/subkey
     * @param  mixed   $default  Default value, if config isn't set
     * --
     * @return mixed
     */
    public static function get($key, $default=null)
    {
        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = Arr::get_by_path($key, self::$configs, $default);
        }

        return self::$cache[$key];
    }

    /**
     * Overwrite particular config key, this is temporary action,
     * the changes won't get saved, until you call self::write();
     * --
     * @param   string  $path   In format: key/subkey
     * @param   mixed   $value
     * --
     * @return  void
     */
    public static function set($path, $value)
    {
        # Clear cache to avoid conflicts
        self::$cache = [];

        Arr::set_by_path($path, $value, self::$configs);
    }
}