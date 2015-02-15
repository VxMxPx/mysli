<?php

namespace mysli\util\config;

__use(__namespace__, '
    mysli.framework.pkgm
    mysli.framework.json
    mysli.framework.fs/fs,file
    mysli.framework.type/arr,arr_path
');

class config {

    static private $registry = [];

    private $package;
    private $filename;
    private $data  = [];
    private $cache = [];

    /**
     * Config data
     * @param string $package vendor/package
     */
    function __construct($package) {
        $this->package = self::ns_to_pkg($package);
        $this->filename = fs::datpath(
            'mysli/util/config', str_replace('/', '.', $this->package).'.json');

        // If we have file, then load contents...
        if (file::exists($this->filename)) {
            $this->data = json::decode_file($this->filename, true);
        }
    }
    /**
     * Get config element, by: sub/key/main
     * @param  mixed $key string (sub/key) or array ([sub/key, sub/key2])
     * @param  mixed $default value if key not found
     * @return mixed
     */
    function get($key, $default=null) {
        // If key is an array, do recursive search.
        if (is_array($key)) {
            $values = [];
            foreach ($key as $val) {
                $values[] = $this->get($val, $default);
            }
            return $values;
        }

        if (arr::get($this->cache, $key)) {
            return $this->cache[$key];
        }

        $value = arr_path::get($this->data, $key, $default);

        // We cache only when we assume it's not default value...
        if ($value !== $default) {
            $this->cache[$key] = $value;
        }

        // Return value in any case...
        return $value;
    }
    /**
     * Retrun all config as an array.
     * @return array
     */
    function as_array() {
        return $this->data;
    }
    /**
     * Set value for key.
     * @param string $path sub/key
     * @param mixed  $value
     * @return null
     */
    function set($path, $value) {
        // Clear cache to avoid corrupted data
        $this->cache = [];
        return arr_path::set($this->data, $path, $value);
    }
    /**
     * Append config to the file.
     * @param  array  $config
     * @return null
     */
    function merge(array $config) {
        $this->cache = [];
        $this->data = arr::merge($this->data, $config);
    }
    /**
     * Save config file.
     * @return boolean
     */
    function save() {
        return json::encode_file($this->filename, $this->data);
    }
    /**
     * Delete config file.
     * @return boolean
     */
    function destroy() {
        $this->cache = $this->data = [];
        unset(self::$registry[$this->package]);
        if (file::exists($this->filename)) {
            return file::remove($this->filename);
        } else {
            return true;
        }
    }

    /**
     * Get config instance or value.
     * @param  string $package vendor/package
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    static function select($package, $key=false, $default=null) {
        $package = self::ns_to_pkg($package);
        if (!arr::get(self::$registry, $package)) {
            self::$registry[$package] = new self($package);
        }
        $config = self::$registry[$package];
        if ($key) {
            return $config->get($key, $default);
        } else {
            return $config;
        }
    }

    /**
     * Check if provided package is actually namespace and if it is, convert it
     * to package name.
     * @param  string $in
     * @return string
     */
    private static function ns_to_pkg($in) {
        if (strpos($in, '\\') !== false) {
            $in = explode('\\', $in);
            if (pkgm::exists($pkg = implode('/', array_slice($in, 0, 3)))) {
                return $pkg;
            } elseif (pkgm::exists($pkg = implode('/', array_slice($in, 0, 2))))
            {
                return $pkg;
            } else {
                throw new framework\exception\not_found(
                    "No package for namespace: `{$in}`.");
            }
        } else return $in;
    }
}
