<?php

/**
 * Manage package.
 */
namespace mysli\toolkit; class pkg
{
    const source = 'source';
    const phar   = 'phar';

    /**
     * Full absolute path to the list of package.
     * --
     * @var string
     */
    private static $listf = null;

    /**
     * Registry. List of currently present packages + current version.
     * --
     * @var array
     */
    private static $r = [];

    /**
     * Init pkg
     * --
     * @param  string registry path
     */
    static function __init($path)
    {
        if (self::$listf)
        {
            throw new \Exception("Already initialized.", 10);
        }

        if (!file_exists($path))
        {
            throw new \Exception("File not found: `{$path}`", 20);
        }

        self::$listf = $path;
        self::read();
    }

    /**
     * Return packages list as an array.
     * --
     * @return array
     */
    static function dump()
    {
        return self::$r;
    }

    /**
     * Get package's name by namespace.
     * --
     * @param  string $namespace
     * --
     * @return string
     */
    static function by_namespace($namespace)
    {
        $s = explode('\\', $namespace);

        if (self::exists(implode('.', array_slice($s, 0, 3))))
        {
            return implode('.', array_slice($s, 0, 3));
        }
        elseif (self::exists(implode('.', array_slice($s, 0, 2))))
        {
            return implode('.', array_slice($s, 0, 2));
        }
        elseif (self::exists(implode('.', array_slice($s, 0, 1))))
        {
            return implode('.', array_slice($s, 0, 1));
        }
    }

    /**
     * Get package's name from path - this must be full absolute path.
     * --
     * @example
     * $path  = /home/user/project/packages/mysli.framework.core.phar
     * return   mysli.framework.core
     * --
     * @param  string $path
     * --
     * @return mixed  string (package name) or null if not found
     */
    static function by_path($path)
    {
        /*
        Loop as long as there's any path left,
        or until `mysli.pkg.ym` file is found.
         */
        do
        {
            if (file_exists($path.'/mysli.pkg.ym'))
            {
                break;
            }
            else
            {
                $path = substr($path, 0, strrpos($path, '/'));
            }
        } while(strlen($path) > 1);

        $package = substr($path, strlen(MYSLI_BINPATH));
        $package = substr($package, -5) === '.phar'
            ? substr($package, 0, -5)
            : $package;

        $package = trim($package, '/');

        if (strpos($package, '/'))
        {
            return str_replace('/', '.', $package);
        }
        else
        {
            return $package;
        }
    }

    /**
     * Return form in which package exists (either pkg::source | pkg::phar)
     * If package not found, null will be returned.
     * --
     * @param  string $package
     * --
     * @return string
     */
    static function exists_as($package)
    {
        $source = MYSLI_BINPATH."/{$package}/mysli.pkg.ym";
        $phar   = 'phar://'.MYSLI_BINPATH."/{$package}.phar/mysli.pkg.ym";

        if     (file_exists($phar))   return self::phar;
        elseif (file_exists($source)) return self::source;
        else                          return null;
    }

    /**
     * Check if package exists (is available in file-system)
     * This will include source packages and phars.
     * --
     * @param  string $package
     * --
     * @return boolean
     */
    static function exists($package)
    {
        return !!(self::exists_as($package));
    }

    /**
     * Add a new package to the registry.
     * --
     * @param  string  $name
     * @param  array   $meta
     * --
     * @return boolean
     */
    static function add($name, $version)
    {
        if (isset(self::$r[$name]))
        {
            throw new \Exception("Package {$name} already on the list.", 10);
        }

        self::$r[$name] = $version;
    }

    /**
     * Remove package from the list.
     * --
     * @param  string $name
     * --
     * @return boolean
     */
    static function remove($name)
    {
        if (!isset(self::$r[$name]))
        {
            throw new \Exception(
                "Trying to remove a non-existant package: `{$name}`");
        }

        unset(self::$r[$name]);
    }

    /**
     * Update a package version.
     * --
     * @param  string $name
     * @param  array  $new_version
     * --
     * @return boolean
     */
    static function update($name, array $new_version)
    {
        if (isset(self::$r[$name]))
        {
            self::$r[$name] = $new_version;
            return true;
        }
        else
            return null;
    }

    /**
     * Check if particular package is enabled.
     * --
     * @param  string  $name
     * --
     * @return boolean
     */
    static function is_enabled($name)
    {
        return isset(self::$r[$name]);
    }

    /**
     * Check if particular package is disabled.
     * --
     * @param  string  $name
     * --
     * @return boolean
     */
    static function is_disabled($name)
    {
        return !self::is_enabled($name);
    }

    /*
    --- Read / write -----------------------------------------------------------
     */

    /**
     * Read and process packages list.
     */
    static function read()
    {
        self::$r = [];
        $list = file_get_contents(self::$listf);
        $list = explode("\n", $list);

        foreach ($list as $line)
        {
            if (!strpos($line, ' '))
                continue;

            list($name, $version) = explode(' ', $line, 2);

            if (($name = trim($name)) && ($version = (int) $version))
                self::$r[$name] = $version;
        }
    }

    /**
     * Write packages list to the list file.
     * --
     * @return boolean
     */
    static function write()
    {
        $list = '';

        foreach (self::$r as $name => $version)
        {
            $list = "{$name} {$version}\n";
        }

        return !!file_put_contents(self::$listf, $list);
    }
}
