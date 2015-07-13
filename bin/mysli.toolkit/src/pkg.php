<?php

/**
 * # Pkg
 *
 * Manage packages.
 */
namespace mysli\toolkit; class pkg
{
    const __use = '.{
        ym,
        fs.file,
        exception.*
    }';

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
     * @throws \Exception 10 Already initialized.
     * @throws \Exception 20 File not found.
     * --
     * @param  string registry path
     */
    static function __init($path=null)
    {
        $path = $path ?: MYSLI_CFGPATH."/toolkit.pkg.list";

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
     *         'script' => 'package.script',
     *         // ...
     *     ]
     * --
     * @throws \Exception 10 Package not found.
     * @throws \Exception 20 Cannot construct package's unique ID.
     * --
     * @return array
     */
    static function list_cli()
    {
        $cli = [];
        $enabled = self::list_enabled();

        foreach ($enabled as $package)
        {
            if (!($path = self::get_path($package)))
                throw new \Exception("Package not found: `{$package}`.", 10);

            /*
            Does this package contain `src/cli` folder
             */
            if (file_exists("{$path}src/cli"))
            {
                // Get list of scripts
                $scripts = scandir("{$path}src/cli");

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

                    // Script's unique id
                    $id = $script;

                    // Script full name
                    $full = "{$package}.{$script}";

                    /*
                     * If such script name already exist, then package name
                     * will be pre-pended, if that exists too, vendor will
                     * be pre-pended, ...
                     *
                     * Example:
                     *   vendor/package/i_am_script => i_am_script
                     *   another/package/i_am_script => package.i_am_script
                     *   yet_another/package/i_am_script => yet_another.package.i_am_script
                     *   4th/package/i_am_script => 4th.package.i_am_script
                     */
                    while (isset($cli[$id]))
                    {
                        if (empty($spackage))
                            throw new \Exception(
                                "Cannot construct package's unique ID, ".
                                "for: `{$package}`", 20
                            );

                        $id = array_pop($spackage).'.'.$id;
                    }

                    // Shortcut
                    $cli[$id] = $full;
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
     *      //    Src:  /home/path/to/bin/mysli.toolkit/
     *      // or Phar: phar:///home/path/to/bin/mysli.toolkit.phar/
     * --
     * @param  string $package
     * --
     * @return string
     */
    static function get_path($package)
    {
        $source = MYSLI_BINPATH."/{$package}/";
        $phar   = 'phar://'.MYSLI_BINPATH."/{$package}.phar/";

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
        $enabled = self::list_enabled();
        $list = [];

        foreach ($enabled as $spackage)
        {
            $meta = self::get_meta($spackage);

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
                self::get_dependees($dependency, true, $proc),
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
        $meta = self::get_meta($package);

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
            if (!self::exists($dependency))
            {
                $list['missing'][] = $dependency;
            }
            else
            {
                if (self::get_version($dependency) !== $req_version)
                {
                    $list['version'][] = $dependency;
                }
                else if (self::is_enabled($dependency))
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
            $nlist = self::get_dependencies($dependency, true, $proc);

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
     * @param string $package
     * --
     * @return integer
     */
    static function get_version($package)
    {
        $meta = self::get_meta($package);
        return (int) $meta['version'];
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
        if ($reload || !isset(self::$meta_cache[$package]))
        {
            $path = self::get_path($package);
            $path .= 'mysli.pkg.ym';

            if (!file::exists($path))
            {
                throw new exception\pkg(
                    "Package's meta not found: `{$package}`.", 10
                );
            }

            self::$meta_cache[$package] = ym::decode_file($path);
        }

        return self::$meta_cache[$package];
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
        log::info("Will enable package: `{$package}`", __CLASS__);

        if (self::is_enabled($package))
            throw new exception\pkg(
                "Package is already enabled: `{$package}`", 10
            );

        if (!self::exists($package))
            throw new exception\pkg(
                "Package doesn't exists: `{$package}`", 20
            );

        /*
        Enable all dependencies too.
         */
        if ($inc_dependencies)
        {
            // Get list of dependencies for this package.
            $dependencies = self::get_dependencies($package, true);

            if (!empty($dependencies['missing']))
                throw new exception\pkg(
                    "Package `{$package}` cannot be enabled, ".
                    "because some dependencies are missing: `".
                    implode(', ', $dependencies['missing'].'`.'), 30
                );


            if (!empty($dependencies['version']))
                throw new exception\pkg(
                    "Package `{$package}` cannot be enabled, ".
                    "because some dependencies are at the wrong version: `".
                    implode(', ', $dependencies['version'].'`.'), 40
                );

            if (count($dependencies['disabled']))
            {
                foreach ($dependencies['disabled'] as $dependency)
                {
                    if (!self::enable($dependency, $with_setup, false))
                        throw new exception\pkg(
                            "Failed to enable dependency: `{$dependency}` ".
                            "for `{$package}`.", 50
                        );
                }
            }
        }

        if (!self::run_setup($package, 'enable'))
            throw new exception\pkg(
                "Setup failed for: `{$package}`.", 60
            );

        self::add($package, self::get_version($package));
        return self::write();
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
        log::info("Will disable package: `{$package}`", __CLASS__);

        if (self::is_disabled($package))
            throw new exception\pkg(
                "Package is already disabled: `{$package}`", 10
            );

        if (!self::exists($package))
            throw new exception\pkg(
                "Package doesn't exists: `{$package}`", 20
            );

        /*
        Disable all dependencies too.
         */
        if ($inc_dependees)
        {
            // Get list of dependees for this package.
            $dependees = self::get_dependees($package, true);

            foreach ($dependees as $dependee)
            {
                // No need to process PHP Extension.
                if (substr($dependee, 0, 14) === 'php.extension.')
                    continue;

                if (!self::disable($dependee, $with_setup, false))
                    throw new exception\pkg(
                        "Failed to disable dependee: ".
                        "`{$dependee}` for `{$package}`.", 30
                    );
            }
        }

        if (!self::run_setup($package, 'disable'))
            throw new exception\pkg(
                "Setup failed for: `{$package}`.", 40
            );

        self::remove($package);
        return self::write();
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
        $file = self::get_path($package);
        $file .= 'src/__setup.php';

        // There's no __setup file,
        // such file is not required hence true will be returned.
        if (!file::exists($file))
            return true;

        $class = str_replace('.', '\\', $package).'\\__setup';

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
     * @throws \Exception 10 Package already on the list.
     */
    static function add($package, $version)
    {
        if (isset(self::$enabled[$package]))
        {
            throw new \Exception("Package {$package} already on the list.", 10);
        }

        self::$enabled[$package] = $version;
    }

    /**
     * Remove package from the list.
     * --
     * @param string $package
     * --
     * @throws \Exception 10 Trying to remove non-existent package.
     * --
     * @return boolean
     */
    static function remove($package)
    {
        if (!isset(self::$enabled[$package]))
        {
            throw new \Exception(
                "Trying to remove a non-existant package: `{$package}`", 10
            );
        }

        unset(self::$enabled[$package]);
    }

    /**
     * Update a package version.
     * --
     * @param string  $package
     * @param integer $new_version
     */
    static function update($package, $new_version)
    {
        if (isset(self::$enabled[$package]))
        {
            self::$enabled[$package] = $new_version;
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
        return isset(self::$enabled[$package]);
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
        return !self::is_enabled($package);
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
            $list .= "{$name} {$version}\n";

        return !!file_put_contents(self::$list_file, $list);
    }
}
