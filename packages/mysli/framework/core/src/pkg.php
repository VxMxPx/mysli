<?php

namespace mysli\framework\core;

class pkg {

    private static $path = null;
    private static $r = [];

    /**
     * Init pkg
     * @param  string registry path
     */
    static function __init($path)
    {
        if (self::$path)
            throw new \Exception("Already initialized.", 10);

        if (!file_exists($path))
            throw new \Exception("File not found: `{$path}`", 20);

        self::$path = $path;
        self::read();
    }

    /**
     * Return current registry state as an array.
     * @return array
     */
    static function dump() {
        return self::$r;
    }

    /**
     * Add new package to the registry.
     * @param  string  $name
     * @param  array   $meta
     * @return boolean
     */
    static function add($name, array $meta)
    {
        if (isset(self::$r['pkg'][$name]))
            throw new \Exception("Package {$name} already on the list.", 10);

        self::$r['pkg'][$name] = $meta;
    }
    /**
     * Remove package from the list.
     * @param  string $name
     * @return boolean
     */
    static function remove($name)
    {
        if (!isset(self::$r['pkg'][$name]))
            throw new \Exception(
                "Trying to remove a non-existant package: `{$name}`");

        unset(self::$r['pkg'][$name]);
    }
    /**
     * Update a package information.
     * @param  string $name
     * @param  array  $new_meta
     * @return boolean
     */
    static function update($name, array $new_meta)
    {
        self::$r['pkg'][$name] = $new_meta;
    }
    /**
     * Check if particular package is on the list.
     * You can check by:
     * - release: mysli.framework.core-r150224.1
     * - source:  mysli/framework/core
     * - name:    mysli.framework.core
     * @param  string  $name
     * @return boolean
     */
    static function has($name)
    {
        // Release
        if (strpos($name, '/') || strpos($name, '-r'))
        {
            foreach (self::$r['pkg'] as $meta) {
                if ($meta['release'] === $name)
                    return true;
            }
        }
        // Name
        else
        {
            return isset(self::$r['pkg'][$name]);
        }

        return false;
    }

    /**
     * Get release (mysli.framework.core-r150214.1) by
     * name(mysli.framework.core), if release is on the list.
     * @param  string $name
     * @return string
     */
    static function get_release_by_name($name)
    {
        if (isset(self::$r['pkg'][$name]))
            return self::$r['pkg'][$name]['release'];
    }
    /**
     * Get name(mysli.framework.core)
     * by release (mysli.framework.core-r150214.1), if release is on the list.
     * @param  string $release
     * @return string
     */
    static function get_name_by_release($release)
    {
        foreach (self::$r['pkg'] as $name => $meta) {
            if ($meta['release'] === $release)
                return $name;
        }
    }

    /**
     * Get package's meta by name
     * @param  string $name
     * @return array
     */
    static function get_by_name($name)
    {
        if ($name && isset(self::$r['pkg'][$name]))
            return self::$r['pkg'][$name];
    }
    /**
     * Get package's meta by release
     * @param  string $release
     * @return array
     */
    static function get_by_release($release)
    {
        return self::get_by_name(self::get_name_by_release($release));
    }

    /**
     * Get list of (enabled) packages
     * @param  boolean $detailed
     * @return array
     */
    static function get_list($detailed=false)
    {
        if ($detailed)
            return self::$r['pkg'];
        else
            return array_keys(self::$r['pkg']);
    }

    // R/W

    static function read() {
        return (self::$r = json_decode(file_get_contents(self::$path), true));
    }

    static function write() {
        return (file_put_contents(self::$path, json_encode(self::$r)));
    }
}
