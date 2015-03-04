<?php

namespace mysli\framework\core;

class pkg
{
    const source = 'source';
    const phar   = 'phar';

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
    static function dump()
    {
        return self::$r;
    }
    /**
     * Get package's name by namespace.
     * @param  string $namespace
     * @return string
     */
    static function by_namespace($namespace)
    {
        $s = explode('\\', $namespace);

        if (self::exists(implode('.', array_slice($s, 0, 3))))
            return implode('.', array_slice($s, 0, 3));
        elseif (self::exists(implode('.', array_slice($s, 0, 2))))
            return implode('.', array_slice($s, 0, 2));
    }
    /**
     * Get package's name from path - this must be full absolute path.
     * $path  = /home/user/project/packages/mysli.framework.core.phar
     * return   mysli.framework.core
     * @param  string $path
     * @return mixed  string (package name) or false if not found
     */
    static function by_path($path)
    {
        $path = rtrim(str_replace('\\', '/', array_shift($arguments)), '/');

        do
        {
            if (file_exists($path.'/mysli.pkg.ym'))
                break;
            else
                $path = substr($path, 0, strrpos($path, '/'));

        } while(strlen($path) > 1);

        $package = substr($path, strlen(MYSLI_PKGPATH));
        $package = substr($package, -5) === '.phar'
            ? substr($package, 0, -5)
            : $package;
        $package = trim($package, '/');

        if (strpos($package, '/'))
            return str_replace('/', '.', $package);
        else
            return $package;
    }
    /**
     * Check if package exists (is available in file-system)
     * This will include source packages, phar, enabled and disabled.
     * @param  string $package
     * @return boolean
     */
    static function exists($package)
    {
        return !!(self::exists_as($package));
    }
    /**
     * Return form in which package exists (either pkg::source | pkg::phar)
     * If package not found, null will be returned.
     * @param  string $package
     * @return string
     */
    static function exists_as($package)
    {
        $source = MYSLI_PKGPATH.'/'.str_replace('.', '/', $package).'/mysli.pkg.ym';
        $phar   = 'phar://'.MYSLI_PKGPATH."/{$package}.phar/mysli.pkg.ym";

        if     (file_exists($phar))   return self::phar;
        elseif (file_exists($source)) return self::source;
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
     * Check if particular package is enabled.
     * @param  string  $name
     * @return boolean
     */
    static function is_enabled($name)
    {
        return isset(self::$r['pkg'][$name]);
    }
    /**
     * Check if particular package is disabled.
     * @param  string  $name
     * @return boolean
     */
    static function is_disabled($name)
    {
        return !self::is_enabled($name);
    }
    /**
     * Check weather particular package is on the boot list, which mean
     * the package is essential for system to work.
     * @param  string  $package (name)
     * @return boolean
     */
    static function is_boot($package)
    {
        foreach (self::$r['boot'] as $name)
            if ($package === explode('/', $name, 2)[0])
                return true;

        return false;
    }
    /**
     * Set/overwrite a boot package.
     * Make a mistake here, and the system will be useless.
     * @param string $key
     * @param string $vale
     */
    static function set_boot($key, $vale)
    {
        self::$r['boot'][$key] = $vale;
    }

    // R/W

    static function read() {
        return (self::$r = json_decode(file_get_contents(self::$path), true));
    }
    static function write() {
        return (file_put_contents(self::$path, json_encode(self::$r)));
    }
}
