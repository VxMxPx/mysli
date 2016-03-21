<?php

namespace mysli\toolkit; class pkg
{
    const __use = '
        .{
            ym,
            fs.file -> file,
            exception.pkg
        }
    ';

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
     * [package => version, ...]
     * --
     * @var array
     */
    private static $enabled = [];

    /**
     * List of all packages currently present in the file-system.
     * [package, package, ...]
     * --
     * @var array
     */
    private static $all = [];

    /**
     * Cache of meta files for packages.
     * --
     * @var array
     */
    private static $meta_cache = [];

    /**
     * Init pkg
     * --
     * @param string $path
     *        Specify a specific pkg list path, if not, default will be used.
     * --
     * @throws mysli\toolkit\exception\pkg 10 Already initialized.
     * @throws mysli\toolkit\exception\pkg 20 File not found.
     */
    static function __init($path=null)
    {
        $path = $path ?: MYSLI_CFGPATH."/toolkit.pkg.list";

        if (static::$list_file)
        {
            throw new exception\pkg("Already initialized.", 10);
        }

        if (!file_exists($path))
        {
            throw new exception\pkg("File not found: `{$path}`", 20);
        }

        static::$list_file = $path;
        static::read();
        static::reload_all();
    }

    /**
     * Return list of enabled packages.
     * --
     * @return array
     */
    static function list_enabled()
    {
        return array_keys(static::$enabled);
    }

    /**
     * Return list of diabled packages.
     * --
     * @return array
     */
    static function list_disabled()
    {
        $disabled = [];

        foreach (static::$all as $package)
        {
            if (!static::is_enabled($package))
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
        return static::$all;
    }


    /**
     * Get list of command line scripts, for each enabled package.
     * --
     * @example
     *     [
     *         'script' => 'package.script',
     *         // ...
     *     ]
     * --
     * @throws mysli\toolkit\exception\pkg 20 Cannot construct package's unique ID.
     * --
     * @return array
     */
    static function list_cli()
    {
        $cli = [];
        $enabled = static::list_enabled();

        foreach ($enabled as $package)
        {
            if (!($path = static::get_path($package)))
            {
                continue;
                \log::warning("Package not found: `{$package}`.", __CLASS__);
            }

            /*
            Does this package contain `script` folder
             */
            if (file_exists("{$path}/script"))
            {
                // Get list of scripts
                $scripts = scandir("{$path}/script");

                foreach ($scripts as $script)
                {
                    // Remove .php if not there, then just continue.
                    if (substr($script, -4) !== '.php')
                        continue;
                    else
                        $script = substr($script, 0, -4);

                    // Divide package into segments, this will be used if script
                    // with a same name is already defined by another package.
                    $spackage = explode('.', $package);

                    // Script full name
                    if (substr($package, strrpos($package, '.')+1) !== $script)
                        $full = "{$package}.{$script}";
                    else
                        $full = $package;

                    // Add to the list
                    $cli[$full] = [
                        'script'   => $script,
                        'absolute' => "{$package}.{$script}",
                        'class'    =>
                            str_replace('.', '\\', $package).
                            '\root\script\\'.$script
                    ];
                }
            }
        }

        return $cli;
    }

    /**
     * Take package base name, and return full absolute path, taking `phar` into
     * consideration.
     * --
     * @example
     *
     *      $pkg = pkg::get_path('mysli.toolkit');
     *      echo $pkg;
     *      //    Src:  /home/path/to/bin/mysli.toolkit
     *      // or Phar: phar:///home/path/to/bin/mysli.toolkit.phar
     * --
     * @param  string $package
     * --
     * @return string
     */
    static function get_path($package)
    {
        $phar   = 'phar://'.MYSLI_BINPATH."/{$package}.phar";
        $source = MYSLI_BINPATH."/{$package}";

        if     (file_exists($phar))   return $phar;
        elseif (file_exists($source)) return $source;
        else                          return null;
    }


    /**
     * List dependees (the packages which require provided package,
     * i.e. are dependant on it)
     * --
     * @param string $package
     * --
     * @throws mysli\toolkit\exception\pgk 10 Infinite loop, cross dependencies.
     * --
     * @return array
     */
    static function get_dependees($package, $deep=false, array $proc=[])
    {
        $enabled = static::list_enabled();
        $list = [];

        foreach ($enabled as $spackage)
        {
            $meta = static::get_meta($spackage);

            if ($meta['require'] && isset($meta['require'][$package]))
            {
                $list[] = $spackage;
            }
        }

        if (!$deep)
            return $list;

        /*
        Prevent infinite loops.
         */
        if (in_array($package, $proc))
        {
            throw new exception\pkg(
                f_error("Infinite loop, cross dependencies.", $proc, -1), 10
            );
        }
        else
        {
            $proc[] = $package;
        }

        foreach ($list as $spackage)
        {
            $list = array_merge(
                static::get_dependees($spackage, true, $proc),
                $list
            );
        }

        // Eliminate duplicated entries
        $list = array_unique($list);

        return $list;
    }

    /**
     * List dependencies of package.
     * If you set $deep to true, it will resolve deeper relationships,
     * i.e. dependencies of dependencies.
     * --
     * @param  string  $package
     * @param  boolean $deep
     * @param  array   $proc
     *         Internal helper. Prevent infinite loop,
     *         if cross dependency situation occurs (a require b and b require a).
     * --
     * @throws mysli\toolkit\exception\pgk 10 Infinite loop, cross dependencies.
     * --
     * @return array
     */
    static function get_dependencies($package, $deep=false, array $proc=[])
    {
        $meta = static::get_meta($package);

        $list = [
            'enabled'  => [],
            'disabled' => [],
            'missing'  => [],
            'version'  => []
        ];

        foreach ($meta['require'] as $dependency => $req_version)
        {
            // Extension?
            if (substr($dependency, 0, 14) === 'php.extension.')
            {
                $extension = substr($dependency, 14);

                if (extension_loaded($extension))
                {
                    $list['enabled'][] = $dependency;
                }
                else
                {
                    $list['missing'][] = $dependency;
                }

                continue;
            }

            // Normal package
            if (!static::exists($dependency))
            {
                $list['missing'][] = $dependency;
            }
            else
            {
                if (static::get_version($dependency) !== $req_version)
                {
                    $list['version'][] = $dependency;
                }
                else if (static::is_enabled($dependency))
                {
                    $list['enabled'][] = $dependency;
                }
                else
                {
                    $list['disabled'][] = $dependency;
                }
            }
        }

        /*
        Return if deep list is not needed.
         */
        if (!$deep)
            return $list;

        /*
        Prevent infinite loops.
         */
        if (in_array($package, $proc))
        {
            throw new exception\pkg(
                f_error("Infinite loop, cross dependencies.", $proc, -1), 10
            );
        }
        else
        {
            $proc[] = $package;
        }


        foreach ($list['disabled'] as $dependency)
        {
            $nlist = static::get_dependencies($dependency, true, $proc);

            $list['enabled']  = array_merge($nlist['enabled'],  $list['enabled']);
            $list['disabled'] = array_merge($nlist['disabled'], $list['disabled']);
            $list['missing']  = array_merge($nlist['missing'],  $list['missing']);
            $list['version']  = array_merge($nlist['version'],  $list['version']);
        }

        // Eliminate duplicated entries
        $list['enabled']  = array_unique($list['enabled']);
        $list['disabled'] = array_unique($list['disabled']);
        $list['missing']  = array_unique($list['missing']);
        $list['version']  = array_unique($list['version']);

        return $list;
    }

    /**
     * Get version for package.
     * --
     * @param string  $package
     * @param boolean $release Return version.release
     * --
     * @return string
     */
    static function get_version($package, $release=false)
    {
        try
        {
            $meta = static::get_meta($package);
        }
        catch (\Exception $e)
        {
            return null;
        }

        if (!is_array($meta))
        {
            return null;
        }

        $version = $meta['version'];

        if ($release)
        {
            $version .= '.'.(isset($meta['release']) ? $meta['release'] : 'rsrc');
        }

        return $version;
    }

    /**
     * Get meta of particular package. Package must exists in filesystem.
     * --
     * @param string  $package
     * @param boolean $reload Weather to reload cached meta.
     * --
     * @throws mysli\toolkit\exception\pkg 10 Package's meta not found.
     * --
     * @return array
     */
    static function get_meta($package, $reload=false)
    {
        if ($reload || !isset(static::$meta_cache[$package]))
        {
            $path = static::get_path($package);
            $path .= '/mysli.pkg.ym';

            if (!file::exists($path))
            {
                throw new exception\pkg(
                    "Package's meta not found: `{$package}`.", 10
                );
            }

            static::$meta_cache[$package] = ym::decode_file($path);
        }

        return static::$meta_cache[$package];
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

        if (static::exists(implode('.', array_slice($s, 0, 3))))
        {
            return implode('.', array_slice($s, 0, 3));
        }
        elseif (static::exists(implode('.', array_slice($s, 0, 2))))
        {
            return implode('.', array_slice($s, 0, 2));
        }
        elseif (static::exists(implode('.', array_slice($s, 0, 1))))
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
        $phar   = 'phar://'.MYSLI_BINPATH."/{$package}.phar/mysli.pkg.ym";
        $source = MYSLI_BINPATH."/{$package}/mysli.pkg.ym";

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
        return !!(static::exists_as($package));
    }

    /**
     * Enable particular package.
     * --
     * @param string  $package
     *
     * @param boolean $with_setup
     *        Execute __setup::enable for package.
     *
     * @param boolean $inc_dependencies
     *        Enable all packages required by this package.
     * --
     * @throws mysli\toolkit\exception\pkg
     *         10 Package is already enabled.
     *
     * @throws mysli\toolkit\exception\pkg
     *         20 Package is doesn't exists.
     *
     * @throws mysli\toolkit\exception\pkg
     *         30 Package's dependencies are missing.
     *
     * @throws mysli\toolkit\exception\pkg
     *         40 Package's dependencies are at the wrong version.
     *
     * @throws mysli\toolkit\exception\pkg
     *         50 Failed to enable dependency.
     *
     * @throws mysli\toolkit\exception\pkg
     *         60 Setup failed.
     *
     * --
     * @return boolean
     */
    static function enable($package, $with_setup=true, $inc_dependencies=true)
    {
        \log::info("Will enable package: `{$package}`", __CLASS__);

        if (static::is_enabled($package))
            throw new exception\pkg(
                "Package is already enabled: `{$package}`", 10
            );

        if (!static::exists($package))
            throw new exception\pkg(
                "Package doesn't exists: `{$package}`", 20
            );

        /*
        Enable all dependencies too.
         */
        if ($inc_dependencies)
        {
            // Get list of dependencies for this package.
            $dependencies = static::get_dependencies($package, true);

            if (!empty($dependencies['missing']))
                throw new exception\pkg(
                    "Package `{$package}` cannot be enabled, ".
                    "because some dependencies are missing: `".
                    implode(', ', $dependencies['missing']).'`.', 30
                );


            if (!empty($dependencies['version']))
                throw new exception\pkg(
                    "Package `{$package}` cannot be enabled, ".
                    "because some dependencies are at the wrong version: `".
                    implode(', ', $dependencies['version']).'`.', 40
                );

            if (count($dependencies['disabled']))
            {
                foreach ($dependencies['disabled'] as $dependency)
                {
                    if (!static::enable($dependency, $with_setup, false))
                        throw new exception\pkg(
                            "Failed to enable dependency: `{$dependency}` ".
                            "for `{$package}`.", 50
                        );
                }
            }
        }

        if (!static::run_setup($package, 'enable'))
            throw new exception\pkg(
                "Setup failed for: `{$package}`.", 60
            );

        static::add($package, static::get_version($package));
        return static::write();
    }

    /**
     * Disable particular package.
     * --
     * @param string  $package
     *
     * @param boolean $with_setup
     *        Execute __setup::disable for package.
     *
     * @param boolean $inc_dependees
     *        Disable all packages that require this package.
     * --
     * @throws mysli\toolkit\exception\pkg
     *         10 Package is already disabled.
     *
     * @throws mysli\toolkit\exception\pkg
     *         20 Package is doesn't exists.
     *
     * @throws mysli\toolkit\exception\pkg
     *         30 Failed to disable dependee.
     *
     * @throws mysli\toolkit\exception\pkg
     *         40 Setup failed.
     * --
     * @return boolean
     */
    static function disable($package, $with_setup=true, $inc_dependees=true)
    {
        \log::info("Will disable package: `{$package}`", __CLASS__);

        if (static::is_disabled($package))
            throw new exception\pkg(
                "Package is already disabled: `{$package}`", 10
            );

        if (!static::exists($package))
            throw new exception\pkg(
                "Package doesn't exists: `{$package}`", 20
            );

        /*
        Disable all dependencies too.
         */
        if ($inc_dependees)
        {
            // Get list of dependees for this package.
            $dependees = static::get_dependees($package, true);

            foreach ($dependees as $dependee)
            {
                // No need to process PHP Extension.
                if (substr($dependee, 0, 14) === 'php.extension.')
                    continue;

                if (!static::disable($dependee, $with_setup, false))
                    throw new exception\pkg(
                        "Failed to disable dependee: ".
                        "`{$dependee}` for `{$package}`.", 30
                    );
            }
        }

        if (!static::run_setup($package, 'disable'))
            throw new exception\pkg(
                "Setup failed for: `{$package}`.", 40
            );

        static::remove($package);
        return static::write();
    }

    /**
     * Call setup for for particular package is exists.
     * --
     * @param string $package
     * @param string $action  Either `enable` or `disable`.
     * --
     * @throws mysli\toolkit\exception\pkg
     *         10 Found __setup file but doesn't contain a valid class.
     * --
     * @return boolean
     */
    static function run_setup($package, $action)
    {
        $file = static::get_path($package);
        $file .= '/lib/__setup.php';

        // There's no __setup file,
        // such file is not required hence true will be returned.
        if (!file::exists($file))
            return true;

        $class = str_replace('.', '\\', $package).'\\__setup';
        \log::debug("Setup will be run for: `{$package}` from `{$class}`.", __CLASS__);

        autoloader::load($class);

        // If there is __setup file, but no class,
        // that means something is wrong.
        if (!class_exists($class, false))
            throw new exception\pkg(
                "Found `__setup` file for `{$package}`, ".
                "but it doesn't contain a valid class.", 10
            );

        // If there's no required method in setup file, that's fine.
        // There are cases, when only one or another will be available.
        if (!method_exists($class, $action))
            return true;

        // If we came so far, action can be called.
        // Action should return boolean!
        return call_user_func("{$class}::{$action}");
    }

    /**
     * Add a new package to the registry.
     * --
     * @param string  $package
     * @param integer $version
     * --
     * @throws mysli\toolkit\exception\pkg 10 Package already on the list.
     */
    static function add($package, $version)
    {
        if (isset(static::$enabled[$package]))
            throw new exception\pkg(
                "Package {$package} already on the list.", 10
            );

        static::$enabled[$package] = $version;
    }

    /**
     * Remove package from the list.
     * --
     * @param string $package
     * --
     * @throws mysli\toolkit\exception\pkg 10 Trying to remove non-existent package.
     * --
     * @return boolean
     */
    static function remove($package)
    {
        if (!isset(static::$enabled[$package]))
        {
            throw new exception\pkg(
                "Trying to remove a non-existant package: `{$package}`", 10
            );
        }

        unset(static::$enabled[$package]);
    }

    /**
     * Update a package version.
     * --
     * @param string  $package
     * @param integer $new_version
     */
    static function update($package, $new_version)
    {
        if (isset(static::$enabled[$package]))
        {
            static::$enabled[$package] = $new_version;
        }
    }

    /**
     * Check if particular package is enabled.
     * --
     * @param string $package
     * --
     * @return boolean
     */
    static function is_enabled($package)
    {
        return isset(static::$enabled[$package]);
    }

    /**
     * Check if particular package is disabled.
     * --
     * @param string $package
     * --
     * @return boolean
     */
    static function is_disabled($package)
    {
        return !static::is_enabled($package);
    }

    /*
    --- Read / write -----------------------------------------------------------
     */

    /**
     * Find all packages.
     *
     * @throws mysli\toolkit\exception\pkg
     *         20 Found directory which is actually not a package.
     */
    static function reload_all()
    {
        static::$all = [];
        $binfiles = scandir(MYSLI_BINPATH);

        foreach ($binfiles as $package)
        {
            if ($package === 'mysli')
                continue;

            if (substr($package, 0, 1) === '.' || substr($package, 0, 1) === '~')
                continue;

            if (substr($package, -5) === '.phar')
                $package = substr($package, 0, -5);


            if (in_array($package, static::$all))
                \log::warning(
                    "Package `{$package}` exists both as a `phar` and a `source`.",
                    __CLASS__
                );
                // throw new exception\pkg(
                //     "Package `{$package}` exists both as `phar` and as `source`, ".
                //     "please remove one of them or system will not boot.", 10
                // );

            $type = static::exists_as($package);

            if (!$type)
                throw new exception\pkg(
                    "File in `bin/` directory appears not to be ".
                    "a valid package: `{$package}`, please remove it.", 20
                );

            static::$all[] = $package;
        }
    }

    /**
     * Read and process packages list.
     */
    static function read()
    {
        static::$enabled = [];
        $list = file_get_contents(static::$list_file);
        $list = explode("\n", $list);

        foreach ($list as $line)
        {
            if (!strpos($line, ' '))
                continue;

            // name, version, hash
            list($name, $version, $_) = explode(' ', $line, 3);

            if (($name = trim($name)) && ($version = $version))
            {
                static::$enabled[$name] = $version;
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

        foreach (static::$enabled as $name => $version)
            $list .= "{$name} {$version} 0\n";

        return !!file_put_contents(static::$list_file, $list);
    }
}
