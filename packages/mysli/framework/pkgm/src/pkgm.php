<?php

namespace mysli\framework\pkgm;

__use(__namespace__, '
    mysli.framework.fs/fs,dir,file
    mysli.framework.json
    mysli.framework.ym
    mysli.framework.exception/* AS framework\exception\*
');

class pkgm
{
    /**
     * Get package release from path - this must be full absolute path.
     * $path  = /home/user/project/packages/mysli.framework.core-r150223.1.phar
     * return   mysli.framework.core-r150223.1
     * @param  string $path
     * @return mixed  string (package name) or false if not found
     */
    static function release_from_path($path)
    {
        $path = str_replace('\\', '/', $path);

        if (substr($path, 0, strlen(fs::pkgpath())) !== fs::pkgpath())
            return false;

        $package = substr($path, strlen(fs::pkgpath()));

        // Phar version
        if (substr(explode('/', $package, 2)[0], -5) === '.phar')
            return substr(explode('/', $package, 2)[0], 0, -5);

        // Dev version
        $package = explode('/', $package);

        if (count($package) >= 3)
        {
            if (self::exists(implode('/', array_slice($package, 0, 3))))
                return implode('/', array_slice($package, 0, 3));
            elseif (self::exists(implode('/', array_slice($package, 0, 2))))
                return implode('/', array_slice($package, 0, 2));
        }
        elseif (count($package) === 2)
        {
            $package = implode('/', $package);
            if (self::exists($package))
                return $package;
        }

        return false;
    }
    /**
     * Get package namespace from path - this must be full absolute path.
     * $path = /www/dir/packages/vendor/meta/package/src/sub/file.php
     * return  vendor\meta\package\sub\file
     * @param  string $path
     * @return mixed  string (package name) or false if not found
     */
    static function namespace_from_path($path)
    {
        // this will give us: vendor/meta/package
        if (!($pkg_name = self::release_from_path($path)))
            return false;

        // Phar
        if (strpos($pkg_name, '-r'))
            return str_replace('.', '\\', explode('-r', $pkg_name, 2)[0]);

        // Dev
        $file = substr($path, strpos($path, $pkg_name) + strlen($pkg_name));

        if (substr($file, 1, 3) === 'src')
            $file = substr($file, 5);

        if ($file)
            $file = substr($file, 0, strpos($file, '.'));
        else
            $file = substr($pkg_name, strrpos($pkg_name, '/'));

        return str_replace('/', '\\', fs::ds($pkg_name, $file));
    }
    /**
     * Get package's name from release. E.g.:
     * mysli.framework.core-r150223.1 => mysli.framework.core
     * @param  string $release
     * @return string
     */
    static function name_from_release($release)
    {
        if (strpos($release, '-r'))
            return explode('-r', $release)[0];
        elseif (strpos($release, '/'))
            return str_replace('/', '.', $release);
        else
            throw new exception\package(
                "Invalid release specefied: `{$release}`");
    }
    /**
     * Check weather package (release) exists (is available) in fs.
     * $release = mysli.framework.core-r150215.1 || mysli/framework/core
     * @param  string  $release
     * @return boolean
     */
    static function exists($release)
    {
        if (strpos($release, '-r'))
            return file::exists(fs::pkgpath($release)); // Phar
        else
            return file::exists(fs::pkgpath($release, 'mysli.pkg.ym')); // Dev
    }
    /**
     * Check weather package (release) is enabled.
     * $release = mysli.framework.core-r150215.1 || mysli/framework/core
     * @param  string  $release
     * @return boolean
     */
    static function is_enabled($release) {
        return \core\pkg::has($release);
    }
    /**
     * Get all (enabled + disabled) packages
     * @return array
     */
    static function list_all() {
        return array_merge(self::list_enabled(), self::list_disabled());
    }
    /**
     * Get all enabled packages
     * @return array
     */
    static function list_enabled()
    {
        $list = [];

        foreach(\core\pkg::get_list(true) as $pkg)
            $list[$pkg['release']] = $pkg['package'];

        return $list;
    }
    /**
     * Get all disabled packages
     * @return array
     */
    static function list_disabled()
    {
        $disabled = [];

        foreach (fs::ls(fs::pkgpath()) as $vendor)
        {
            if (substr($vendor, -5) === '.phar')
            {
                if (!self::is_enabled(substr($vendor, 0, -5)))
                {
                    $name = self::name_from_release(substr($vendor, 0, -5));
                    $disabled[substr($vendor, 0, -5)] = $name;
                }

                continue;
            }

            foreach (fs::ls(fs::pkgpath($vendor)) as $sub)
            {
                $root = "{$vendor}/{$sub}";

                if (!dir::exists(fs::pkgpath($root)))
                    continue;


                if (file::exists(fs::pkgpath($root, 'mysli.pkg.ym')))
                {
                    if (!self::is_enabled($root))
                        $disabled[$root] = self::name_from_release($root);

                    continue;
                }

                foreach (fs::ls(fs::pkgpath($root)) as $package)
                {
                    if (file::exists(
                        fs::pkgpath($root, $package, 'mysli.pkg.ym')))
                    {
                        if (!self::is_enabled("{$root}/{$package}"))
                            $disabled["{$root}/{$package}"] =
                                self::name_from_release("{$root}/{$package}");
                    }
                }
            }
        }

        return $disabled;
    }
    /**
     * List obsolete packages
     * @return array
     */
    static function list_obsolete()
    {
        $enabled = \core\get_list(true);
        $obsolete = [];

        do {
            $chg = false;

            foreach ($enabled as $package => &$meta)
            {
                if (!empty($meta['required_by']))
                {
                    foreach ($meta['required_by'] as $id => $req_pkg)
                    {
                        if (in_array($req_pkg, $obsolete))
                        {
                            unset($meta['required_by'][$id]);
                            $chg = true;
                        }
                    }
                }

                if (empty($meta['required_by']) && $meta['enabled_by'])
                {
                    unset($enabled[$package]);
                    $obsolete[$package] = $meta['package'];
                    $chg = true;
                }
            }
        } while ($chg);

        return $obsolete;
    }
    /**
     * List dependees (the packages which require provided package,
     * i.e. are dependant on it)
     * @param  string  $release
     * @param  boolean $deep
     * @param  array   $listed internal helper
     * @return array
     */
    static function list_dependees($release, $deep=false)
    {
        $meta = self::meta($release);
        $name = $meta['package'];

        if (!$deep)
            return isset($meta['required_by']) ? $meta['required_by'] : [];

        $dependees[] = $name;

        foreach ($meta['required_by'] as $dependee)
        {
            $dependees[] = $dependee;
            $dependees = array_merge(
                self::list_dependees(
                    \core\pkg::get_release_by_name($dependee), true),
                $dependees
            );
        }

        // dump($dependees);
        return array_values(array_unique($dependees));
    }
    /**
     * List dependencies of package.
     * If you set $deep to true, it will resolve deeper relationships,
     * i.e. dependencies of dependencies
     * @param  string  $release
     * @param  boolean $deep
     * @param  string  $group packages of which group to list:
     * null (required), recommend, dev, ... (other special groups)
     * @param  array   $proc internal helper. Prevent infinite loop,
     * if cross dependency situation occurs (a require b and b require a).
     * @return array
     */
    static function list_dependencies(
        $release, $deep=false, $group=null, array $proc=[])
    {
        $meta = self::meta($release);

        $list = [
            'enabled'   => [],
            'disabled'  => [],
            'missing'   => []
        ];

        // Resolve group
        $group = $group ? "require-{$group}" : 'require';

        if (!isset($meta[$group]))
            return $list;

        foreach ($meta[$group] as $dependency => $rrelease)
        {
            // Extension?
            if (substr($dependency, 0, 14) === 'php.extension.')
            {
                $extension = substr($dependency, 14);

                if (extension_loaded($extension))
                    $list['enabled'][] = $dependency;
                else
                    $list['missing'][] = $dependency;

                continue;
            }

            // Normal package
            $rdependency = self::find_by_release($dependency, $rrelease);

            if (!$rdependency)
            {
                $list['missing'][] = $dependency;
            }
            else
            {
                if (self::is_enabled($rdependency))
                    $list['enabled'][] = $rdependency;
                else
                    $list['disabled'][] = $rdependency;
            }
        }

        if (!$deep)
            return $list;

        // Prevent infinite loops
        $hash = $release . ': ' . implode(', ', array_keys($meta[$group]));
        if (in_array($hash, $proc))
        {
            $proc[count($proc)-1] = ' >> '.$proc[count($proc) - 1];
            $proc[] = ' >> '.$hash;
            array_unshift($proc, '----------');
            $proc[] = '----------';
            throw new exception\dependency(
                "Infinite loop, cross dependencies:\n".implode("\n", $proc));
        }
        $proc[] = $hash;

        foreach ($list['disabled'] as $dependency)
        {
            // Group goes only one level. If we grab -dev dependencies,
            // we need not to grab -dev dependencies of thos dependencies
            $nlist = self::list_dependencies($dependency, true, null, $proc);

            $list['enabled']  = array_merge($nlist['enabled'], $list['enabled']);
            $list['disabled'] = array_merge($nlist['disabled'], $list['disabled']);
            $list['missing']  = array_merge($nlist['missing'], $list['missing']);
        }

        // Eliminate duplicated entries
        $list['enabled']  = array_unique($list['enabled']);
        $list['disabled'] = array_unique($list['disabled']);
        $list['missing']  = array_unique($list['missing']);

        return $list;
    }
    /**
     * Find package by release (regex) and return full name of it.
     * @param  string $package
     * @param  string $release
     * @param  array  $source  list of sourced where to look for a package,
     *                         if not provided self::list_all() will be used
     * @return string full.package.name.and-release.phar | null if not found
     */
    static function find_by_release($package, $release='*', array $source=null)
    {
        if (!$source)
            $source = self::list_all();

        if (substr($release, 0, 1) !== 'r')
            $release = "r*.{$release}";

        $release = preg_quote($release);
        $rpackage = preg_quote($package);

        if (strpos($release, '\\*'))
            $release = str_replace('\\*', '.*?', $release);

        $regex = "/^{$rpackage}\\-{$release}$/";

        foreach ($source as $pkg => $_)
            if (preg_match($regex, $pkg))
                return $pkg;

        // If we came to here, check source packages (vendor/package).

        $package = str_replace('.', '/', $package);

        if (isset($source[$package]))
            return $package;
        else
            return false;
    }
    /**
     * Get meta for particular package.
     * @param  string  $release
     * @param  boolean $force_read force package meta to be read from file
     * @return array
     */
    static function meta($release, $force_read=false)
    {
        if (self::is_enabled($release) && !$force_read)
        {
            return \core\pkg::get_by_release($release);
        }
        elseif (self::exists($release))
        {
            $file = fs::pkgpath($release, 'mysli.pkg.ym');

            if (file::exists($file))
            {
                $meta = ym::decode_file($file);
                $meta['require'] = $meta['require'] ?: [];
                $meta['release'] = $release;
                return $meta;
            }
            else
                throw new framework\exception\not_found(
                    "Fild `mysli.pkg.ym` not found for: `{$release}`.", 1);
        }
        else
            throw new framework\exception\not_found(
                "The package doesn't exists: `{$release}`.", 2);
    }
    /**
     * Enable package (release). This will NOT run the setup.
     * @param  string $package
     * @param  string $enabled_by if provided, this package will become
     * obsolete when $enabled_by will be disabled (if nothing else)
     * depends on it.
     * @return boolean
     */
    static function enable($release, $enabled_by=null)
    {
        // Cannot enable if already enabled
        if (self::is_enabled($release))
            throw new exception\package(
                "The package is already enabled: `{$release}`.", 1);

        // Cannot enable if don't exists
        if (!self::exists($release))
            throw new framework\exception\not_found(
                "The package doesn't exists: `{$release}`.", 2);

        // Get meta and name
        $meta = self::meta($release);
        $name = $meta['package'];

        // Go through required package, and add itself to the required list
        foreach ($meta['require'] as $dependency => $version)
        {
            $dmeta = \core\pkg::get_by_name($dependency);

            if (!$dmeta)
                throw new exception\dependency(
                "Dependency not satisfied: `{$dependency} : {$version}`", 4);

            if (!isset($dmeta['required_by']))
                $dmeta['required_by'] = [];

            if (!in_array($name, $dmeta['required_by']))
            {
                $dmeta['required_by'][] = $name;
                \core\pkg::update($dependency, $dmeta);
            }
        }

        $meta['release']     = $release;
        $meta['enabled_by']  = $enabled_by;
        $meta['enabled_on']  = time();
        $meta['required_by'] = [];
        $meta['sh'] = self::discover_scripts($release);

        // Does any of the enabled packages require this package?
        // This happened sometimes, especially when replacing packages.
        if (!empty(\core\pkg::get_list()))
        {
            foreach (\core\pkg::get_list(true) as $lmeta)
            {
                if (!isset($lmeta['require']))
                    continue;

                foreach ($lmeta['require'] as $depends_on => $version)
                {
                    if ($depends_on && $depends_on === $name)
                    {
                        // Unlike event that package is already on the list.
                        if (!in_array($lmeta['package'], $meta['required_by']))
                            $meta['required_by'][] = $lmeta['package'];
                    }
                }
            }
        }

        \core\pkg::add($name, $meta);
        return \core\pkg::write();
    }
    /**
     * Disable package (release). This will NOT run the setup.
     * @param  string $package
     * @return boolean
     */
    static function disable($release)
    {
        // If not enabled, won't disable
        if (!self::is_enabled($release))
            throw new exception\package(
                "The package is not enabled: `{$release}`.", 1);

        // Get meta & name
        $meta = self::meta($release);
        $name = $meta['package'];

        // Remove self from list of packages on which this package depends.
        foreach ($meta['require'] as $dependency => $version)
        {
            $rmeta = \core\pkg::get_by_name($dependency);

            if (!$rmeta || !isset($rmeta['required_by']))
                continue;

            while ($rmeta['required_by'] &&
                in_array($name, $rmeta['required_by']))
            {
                $rkey = array_search($name, $rmeta['required_by']);
                unset($rmeta['required_by'][$rkey]);
            }

            \core\pkg::update($dependency, $rmeta);
        }

        \core\pkg::remove($name);
        return \core\pkg::write();
    }

    // Private

    /**
     * Discover scripts for package.
     * @param  string $package
     * @return array
     */
    private static function discover_scripts($package)
    {
        $files = [];

        if (dir::exists(fs::pkgpath($package, 'sh')))
            foreach (fs::ls(fs::pkgpath($package, 'sh'), '/\\.php$/') as $file)
                $files[] = substr($file, 0, -4);

        return $files;
    }
}
