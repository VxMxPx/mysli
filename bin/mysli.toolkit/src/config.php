<?php

namespace mysli\toolkit; class config
{
    const __use = '
        .{ pkg, json, fs.fs, fs.file, type.arr, type.arr_path -> arrp, exception.* }
    ';

    /**
     * Currently selected package.
     * --
     * @var string
     */
    private $package;

    /**
     * Filename for currently selected package.
     * --
     * @var string
     */
    private $filename;

    /**
     * All values for currently selected package.
     * --
     * @var array
     */
    private $data  = [];

    /**
     * Cached value, so that array search will be faster when accessing multiple
     * values by path.
     * --
     * @var array
     */
    private $cache = [];

    /**
     * Instace of config object.
     * --
     * @param string $package vendor.package or a namespace.
     */
    function __construct($package)
    {
        $this->package = self::ns_to_pkg($package);

        if (!$this->package)
            throw new exception\value("Invalid package name for config.", 1);

        $this->filename = fs::cfgpath('pkg', $this->package.'.json');

        // If we have file, then load contents...
        if (file::exists($this->filename))
            $this->data = json::decode_file($this->filename, true);
    }

    /**
     * Get config element, by: sub.key.main
     * --
     * @param mixed $key
     *        String (sub.key) or array ([sub.key, sub.key2]).
     *
     * @param mixed $default
     *        Default value if key not found.
     * --
     * @return mixed
     */
    function get($key, $default=null)
    {
        // If key is an array, do recursive search.
        if (is_array($key))
        {
            $values = [];

            foreach ($key as $val)
            {
                $values[] = $this->get($val, $default);
            }

            return $values;
        }

        if (arr::get($this->cache, $key))
        {
            return $this->cache[$key];
        }

        $value = arrp::get($this->data, $key, $default);

        // We cache only when we assume it's not default value...
        if ($value !== $default)
        {
            $this->cache[$key] = $value;
        }

        // Return value in any case...
        return $value;
    }

    /**
     * Return all configurations for current package, as an array.
     * --
     * @return array
     */
    function as_array()
    {
        return $this->data;
    }

    /**
     * Set value for key.
     * --
     * @param string $path sub.key
     * @param mixed  $value
     */
    function set($path, $value)
    {
        // Clear cache to avoid corrupted data
        $this->cache = [];
        return arrp::set($this->data, $path, $value);
    }

    /**
     * Reset configuration for package, - erase all current values, and replace
     * them with those provided to this method.
     * --
     * @param array $config
     */
    function reset(array $config)
    {
        $this->cache = [];
        $this->data = $config;
    }

    /**
     * Append configuration, preserve current values (replace only those that
     * overlaps).
     * --
     * @param array $config
     */
    function merge(array $config)
    {
        $this->cache = [];
        $this->data = arr::merge($this->data, $config);
    }

    /**
     * Save configuration to file.
     * --
     * @return boolean
     */
    function save()
    {
        return json::encode_file($this->filename, $this->data);
    }

    /**
     * Delete configuration file entirely.
     * --
     * @return boolean
     */
    function destroy()
    {
        $this->cache = $this->data = [];
        unset(self::$registry[$this->package]);

        if (file::exists($this->filename))
        {
            return file::remove($this->filename);
        }
        else
        {
            return true;
        }
    }

    /*
    --- Static -----------------------------------------------------------------
     */

    /**
     * Contains so far constructed objects, so that config object is constructed
     * only once for each package, when using `::select`.
     * --
     * @var array
     */
    static private $registry = [];

    /**
     * Get an instance of config or value from it.
     * --
     * @param string $package Use vendor.package or full namespace.
     * @param mixed  $key
     * @param mixed  $default
     * --
     * @return \mysli\toolkit\config or a mixed value
     */
    static function select($package, $key=false, $default=null)
    {
        $package = self::ns_to_pkg($package);

        if (!$package)
        {
            throw new exception\value("Invalid package.", 1);
        }

        if (!arr::get(self::$registry, $package))
        {
            self::$registry[$package] = new self($package);
        }

        $config = self::$registry[$package];

        if ($key)
        {
            return $config->get($key, $default);
        }
        else
        {
            return $config;
        }
    }

    /**
     * Check if provided package is actually namespace
     * and if it is, convert it to package name.
     * --
     * @param string $in
     * --
     * @return string
     */
    private static function ns_to_pkg($in)
    {
        if (strpos($in, '\\') !== false)
        {
            return pkg::by_namespace($in);
        }
        else
        {
            return $in;
        }
    }
}
