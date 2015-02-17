<?php

namespace mysli\framework\core;

class pkg {

    private $path = null;
    private $r = [];

    /**
     * Init pkg
     * @param  string registry path
     */
    static function __init($path)
    {
        if ($path)
            throw new \Exception("Already initialized.", 10);

        if (!file_exists($path))
            throw new \Exception("File not found: `{$file}`", 20);

        self::$file = $file;
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
     * @param  string  $release
     * @param  array   $meta
     * @return boolean
     */
    static function add($release, array $meta)
    {
        $name = $meta['package'];

        if (isset(self::$r['pkg'][$release]) || isset(self::$r['map'][$name]))
            throw new \Exception("Package {$release} already on the list.", 10);

        self::$r['map'][$name] = $release;
        self::$r['pkg'][$release] = $meta;
    }
    /**
     * Remove package from the list.
     * @param  string $release
     * @return boolean
     */
    static function remove($release)
    {
        if (!isset(self::$r['pkg'][$release]))
            throw new \Exception(
                "Trying to remove a non-existant package: `{$release}`");

        $name = self::$r['pkg'][$release]['package'];

        unset(self::$r['pkg'][$release]);
        unset(self::$r['map'][$name]);
    }
    /**
     * Update a package information.
     * @param  string $release     old release
     * @param  array  $new_meta
     * @param  string $new_release
     * @return boolean
     */
    static function update($release, array $new_meta, $new_release=null)
    {
        if ($new_release !== null && $release !== $new_release)
        {
            self::remove($release);
            self::add($new_release, $new_meta);
        }
        else
            self::$r['pkg'][$release] = $new_meta;
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
            return isset(self::$r['pkg'][$name]);

        // Name
        return isset(self::$r['map'][$name]);
    }

    /**
     * Get release (mysli.framework.core-r150214.1) by
     * name(mysli.framework.core), if release is on the list.
     * @param  string $name
     * @return string
     */
    static function get_release_by_name($name)
    {
        if (isset(self::$r['map'][$name]))
            return self::$r['map'][$name];
    }
    /**
     * Get name(mysli.framework.core)
     * by release (mysli.framework.core-r150214.1), if release is on the list.
     * @param  string $release
     * @return string
     */
    static function get_name_by_release($release)
    {
        if (isset(self::$r['pkg'][$release]))
            return self::$r['pkg'][$release]['package'];
    }

    /**
     * Get package's meta by name
     * @param  string $name
     * @return array
     */
    static function get_by_name($name)
    {
        $release = self::get_release_by_name($name);
        return self::get_by_release($release);
    }
    /**
     * Get package's meta by release
     * @param  string $release
     * @return array
     */
    static function get_by_release($release)
    {
        if (isset(self::$r['pkg'][$release]))
            return self::$r['pkg'][$release];
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
            return self::$r['map'];
    }

    // R/W

    static function read() {
        return (self::$r = json_decode(file_get_contents(self::$path), true));
    }

    static function write() {
        return (file_put_contents(self::$path, json_encode(self::$r)));
    }
}
