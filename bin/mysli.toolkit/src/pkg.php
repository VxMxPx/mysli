<?php

/**
 * # Pkg
 *
 * Manage packages.
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
    private static $list_file = null;

    /**
     * Registry. List of currently present packages + current version.
     * --
     * @var array
     */
    private static $enabled = [];

    /**
     * List of all packages currently present in the file-system.
     * --
     * @var array
     */
    private static $all = [];

    /**
     * Init pkg
     * --
     * @throws \Exception 10 Already initialized.
     * @throws \Exception 20 File not found.
     * --
     * @param  string registry path
     */
    static function __init($path)
    {
        if (self::$list_file)
        {
            throw new \Exception("Already initialized.", 10);
        }

        if (!file_exists($path))
        {
            throw new \Exception("File not found: `{$path}`", 20);
        }

        self::$list_file = $path;
        self::read();
        self::reload_all();
    }

    /**
     * Return list of enabled packages.
     * --
     * @return array
     */
    static function list_enabled()
    {
        return array_keys(self::$enabled);
    }

    /**
     * Return list of diabled packages.
     * --
     * @return array
     */
    static function list_disabled()
    {
        $disabled = [];

        foreach (self::$all as $package)
        {
            if (!self::is_enabled($package))
                $disabled[] = $package;
        }

        return $disabled;
    }

    /**
     * Return list of all packages.
     * --
     * @return array
     */
    static function list_all()
    {
        return self::$all;
    }


    /**
     * Get list of command line scripts, for each enabled package.
     * --
     * @example
     *     [
     *         'vendor.package.script' => [
     *             'script'      => 'script',
     *             'package'     => 'vendor.package',
     *             'class'       => '\vendor\package\cli\script',
     *             'path'        => 'vendor/package/src/cli/script.php',
     *             'description' => 'Package's description.',
     *         ],
     *         // ...
     *     ]
     * --
     * @return array
     */
    static function list_cli()
    {

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
     * @param string $package
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
     * @param  integer $version
     * @param  string  $release
     * --
     * @throws \Exception 10 Package already on the list.
     * --
     * @return boolean
     */
    static function add($name, $version, $release)
    {
        if (isset(self::$enabled[$name]))
        {
            throw new \Exception("Package {$name} already on the list.", 10);
        }

        self::$enabled[$name] = [
            'version' => $version,
            'release' => $release
        ];
    }

    /**
     * Remove package from the list.
     * --
     * @param  string $name
     * --
     * @throws \Exception 10 Trying to remove non-existent package.
     * --
     * @return boolean
     */
    static function remove($name)
    {
        if (!isset(self::$enabled[$name]))
        {
            throw new \Exception(
                "Trying to remove a non-existant package: `{$name}`", 10
            );
        }

        unset(self::$enabled[$name]);
    }

    /**
     * Update a package version.
     * --
     * @param  string  $name
     * @param  integer $new_version
     * @param  string  $new_release
     */
    static function update($name, $new_version, $new_release)
    {
        if (isset(self::$enabled[$name]))
        {
            self::$enabled[$name] = [
                'version' => $new_version,
                'release' => $new_release
            ];
        }
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
        return isset(self::$enabled[$name]);
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
     * Find all packages.
     * --
     * @throws \Exception 10 Package exists in two variations, source and .phar.
     * @throws \Exception 20 Found directory which is actually not a package.
     */
    static function reload_all()
    {
        self::$all = [];
        $binfiles = scandir(MYSLI_BINPATH);

        foreach ($binfiles as $package)
        {
            if (substr($package, 0, 1) === '.')
                continue;

            if (substr($package, 0, -4) === '.phar')
                $package = substr($package, 0, -4);


            if (in_array($package, self::$all))
                throw new \Exception(
                    "Package exists: `{$package}` both as `phar` and as `source`, ".
                    "please remove one of them or system will not boot.", 10
                );

            $type = self::exists_as($package);

            if (!$type)
                throw new \Exception(
                    "File in `bin/` directory appears not to be ".
                    "a valid package: `{$package}`, please remove it.", 20
                );

            self::$all[] = $package;
        }
    }

    /**
     * Read and process packages list.
     */
    static function read()
    {
        self::$enabled = [];
        $list = file_get_contents(self::$list_file);
        $list = explode("\n", $list);

        foreach ($list as $line)
        {
            if (!strpos($line, ' '))
                continue;

            list($name, $version) = explode(' ', $line, 2);

            if (($name = trim($name)) && ($version = (int) $version))
            {
                self::$enabled[$name] = $version;
            }
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

        foreach (self::$enabled as $name => $version)
            $list = "{$name} {$version}\n";

        return !!file_put_contents(self::$list_file, $list);
    }
}
