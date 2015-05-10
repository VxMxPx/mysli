<?php

namespace mysli\framework\pkgm;

__use(__namespace__, '
    mysli.framework.fs/fs,dir,file
    mysli.framework.json
    mysli.framework.ym
    mysli.framework.exception/* -> framework\exception\*
');

class pkgm
{
    /**
     * Get package namespace from path - this must be full absolute path.
     * $path = /www/dir/packages/vendor/meta/package/src/php/sub/file.php
     * return  vendor\meta\package\sub\file
     * @param  string $path
     * @return mixed  string (package name) or false if not found
     */
    static function path_to_namespace($path)
    {
        // this will give us: vendor/meta/package
        if (!($pkg_name = \core\pkg::by_path($path)))
        {
            return false;
        }

        // Phar
        if (strpos($pkg_name, '.'))
        {
            return str_replace('.', '\\', $pkg_name)[0];
        }

        // Dev
        $file = substr($path, strpos($path, $pkg_name) + strlen($pkg_name));

        if (substr($file, 1, 7) === 'src/php')
        {
            $file = substr($file, 9);
        }

        if ($file)
        {
            $file = substr($file, 0, strpos($file, '.'));
        }
        else
        {
            $file = substr($pkg_name, strrpos($pkg_name, '/'));
        }

        return str_replace('/', '\\', fs::ds($pkg_name, $file));
    }
    /**
     * List both enabled and disabled packages
     * @param boolean $detailed
     * @return array
     */
    static function lst_all($detailed=false)
    {
        return array_merge(
            self::lst_enabled($detailed),
            self::lst_disabled($detailed)
        );
    }
    /**
     * Get list of enabled packages
     * @param  boolean $detailed
     * @return array
     */
    static function lst_enabled($detailed=false)
    {
        $packages = \core\pkg::dump();

        if ($detailed) return $packages['pkg'];
        else           return array_keys($packages['pkg']);
    }
    /**
     * Get all disabled packages
     * @param boolean $detailed
     * @return array
     */
    static function lst_disabled($detailed=false)
    {
        $disabled = [];

        foreach (fs::ls(fs::pkgpath()) as $vendor)
        {
            if (substr($vendor, -5) === '.phar')
            {
                if (!\core\pkg::is_enabled(substr($vendor, 0, -5)))
                {
                    $name = substr($vendor, 0, -5);
                    $disabled[$name] = $detailed ? self::meta($name) : null;
                }

                continue;
            } elseif (!dir::exists(fs::pkgpath($vendor))) {
                continue;
            }


            foreach (fs::ls(fs::pkgpath($vendor)) as $sub)
            {
                $root = "{$vendor}/{$sub}";

                if (!dir::exists(fs::pkgpath($root)))
                {
                    continue;
                }

                if (file::exists(fs::pkgpath($root, 'mysli.pkg.ym')))
                {
                    if (!\core\pkg::is_enabled($root))
                    {
                        $name = str_replace('/', '.', $root);
                        $disabled[$name] = $detailed ? self::meta($name) : null;
                    }

                    continue;
                }

                foreach (fs::ls(fs::pkgpath($root)) as $package)
                {
                    if (file::exists(
                        fs::pkgpath($root, $package, 'mysli.pkg.ym')))
                    {
                        $name = str_replace('/', '.', $root).".{$package}";
                        if (!\core\pkg::is_enabled($name))
                        {
                            $disabled[$name] = $detailed ? self::meta($name) : null;
                        }
                    }
                }
            }
        }

        return ($detailed) ? $disabled : array_keys($disabled);
    }
    /**
     * List obsolete packages
     * @return array
     */
    static function lst_obsolete()
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
     * @param  string  $package
     * @param  boolean $deep
     * @param  array   $listed internal helper
     * @return array
     */
    static function lst_dependees($package, $deep=false)
    {
        $meta = self::meta($package);

        if (!$deep)
        {
            return $meta['required_by'];
        }

        $dependees[] = $package;

        foreach ($meta['required_by'] as $dependee)
        {
            $dependees[] = $dependee;
            $dependees = array_merge(
                self::lst_dependees($dependee, true),
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
     * @param  string  $package
     * @param  boolean $deep
     * @param  string  $group packages of which group to list:
     * null (required), recommend, dev, ... (other special groups)
     * @param  array   $proc internal helper. Prevent infinite loop,
     * if cross dependency situation occurs (a require b and b require a).
     * @return array
     */
    static function lst_dependencies(
        $package, $deep=false, $group=null, array $proc=[])
    {
        $meta = self::meta($package);

        $list = [
            'enabled'   => [],
            'disabled'  => [],
            'missing'   => []
        ];

        // Resolve group
        $group = $group ? "require-{$group}" : 'require';

        if (!isset($meta[$group]) || !$meta[$group])
        {
            return $list;
        }

        foreach ($meta[$group] as $dependency => $required_version)
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
            if (!\core\pkg::exists($dependency))
            {
                $list['missing'][] = $dependency;
            }
            else
            {
                if (\core\pkg::is_enabled($dependency))
                {
                    $list['enabled'][] = $dependency;
                }
                else
                {
                    $list['disabled'][] = $dependency;
                }
            }
        }

        if (!$deep)
        {
            return $list;
        }

        // Prevent infinite loops
        $hash = $package . ': ' . implode(', ', array_keys($meta[$group]));
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
            $nlist = self::lst_dependencies($dependency, true, null, $proc);

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
     * Check if apropriate version of package exists.
     * @param  string $package
     * @param  string $release
     * @return string full.package.name or null if not found
     */
    static function has_version($package, $release='*')
    {
        if (!\core\pkg::exists($package))
        {
            return null;
        }

        $meta = self::meta($package);

        if (substr($release, 0, 1) !== 'r')
        {
            $release = "r*.{$release}";
        }

        $release = '/'.preg_quote($release, '/').'/';

        if (strpos($release, '\\*'))
        {
            $release = str_replace('\\*', '.*?', $release);
        }

        $srelease = isset($meta['release']) ? $meta['release'] : 'source';

        return preg_match($release, $srelease);
    }
    /**
     * Get package's meta by name
     * @param  string  $name
     * @param  boolean $source -- read meta from package itself
     *                         (rathet than from list of enabled packages)
     * @return array
     */
    static function meta($name, $source=false)
    {
        if (\core\pkg::is_enabled($name) && !$source)
        {
            return \core\pkg::dump()['pkg'][$name];
        }
        elseif (\core\pkg::exists($name))
        {
            $file = fs::pkgreal($name, 'mysli.pkg.ym');

            if (file::exists($file))
            {
                $meta = ym::decode_file($file);

                // Set proper defaults
                if (!isset($meta['require']) || !is_array($meta['require']))
                {
                    $meta['require'] = [];
                }
                if (!isset($meta['required_by']) || !is_array($meta['required_by']))
                {
                    $meta['required_by'] = [];
                }
                if (!isset($meta['release']))
                {
                    $meta['release'] = 'source';
                }

                return $meta;
            }
            else
            {
                throw new framework\exception\not_found(
                    "File `mysli.pkg.ym` not found (`{$file}`) for: `{$name}`.", 1
                );
            }
        }
        else
        {
            throw new framework\exception\not_found(
                "The package doesn't exists: `{$name}`.", 2
            );
        }
    }
    /**
     * Enable package. This will NOT run the setup.
     * @param  string $package
     * @param  string $enabled_by if provided, this package will become obsolete,
     *                            when $enabled_by will be disabled,
     *                            if nothing else depends on it.
     * @return boolean
     */
    static function enable($package, $enabled_by=null)
    {
        // Cannot enable if already enabled
        if (\core\pkg::is_enabled($package))
        {
            throw new exception\package(
                "The package is already enabled: `{$package}`.", 1
            );
        }

        // Cannot enable if don't exists
        if (!\core\pkg::exists($package))
        {
            throw new framework\exception\not_found(
                "The package doesn't exists: `{$package}`.", 2
            );
        }

        // Get meta and name
        $meta = self::meta($package);

        // Go through required package, and add itself to the required list
        foreach ($meta['require'] as $dependency => $need_version)
        {
            if (substr($dependency, 0, 14) === 'php.extension.')
            {
                // Nothing to do with extentions!
                continue;
            }

            $dmeta = self::meta($dependency);

            if (!$dmeta)
            {
                throw new exception\dependency(
                    "Dependency not satisfied: `{$dependency} : ".
                    "{$need_version}`", 4
                );
            }

            if (!in_array($package, $dmeta['required_by']))
            {
                $dmeta['required_by'][] = $package;
                \core\pkg::update($dependency, $dmeta);
            }
        }

        $meta['enabled_by']  = $enabled_by;
        $meta['enabled_on']  = time();
        $meta['required_by'] = [];
        $meta['sh'] = self::discover_scripts($package);

        // Does any of the enabled packages require this package?
        // This happened sometimes, especially when replacing packages.
        if (!empty(self::lst_enabled()))
        {
            foreach (self::lst_enabled(true) as $lmeta)
            {
                if (!isset($lmeta['require']))
                {
                    continue;
                }

                foreach ($lmeta['require'] as $depends_on => $version)
                {
                    if ($depends_on && $depends_on === $package)
                    {
                        // Unlike event that package is already on the list.
                        if (!in_array($lmeta['package'], $meta['required_by']))
                        {
                            $meta['required_by'][] = $lmeta['package'];
                        }
                    }
                }
            }
        }

        \core\pkg::add($package, $meta);
        return \core\pkg::write();
    }
    /**
     * Disable package. This will NOT run the setup.
     * @param  string $package
     * @return boolean
     */
    static function disable($package)
    {
        // If not enabled, won't disable
        if (!\core\pkg::is_enabled($package))
        {
            throw new exception\package(
                "The package is not enabled: `{$package}`.", 1
            );
        }

        // Get meta & name
        $meta = self::meta($package);

        // Remove self from list of packages on which this package depends.
        foreach ($meta['require'] as $dependency => $version)
        {
            if (substr($dependency, 0, 14) === 'php.extension.')
            {
                // No need to check when dealing with extensions
                continue;
            }

            $rmeta = self::meta($dependency);

            if (!$rmeta || empty($rmeta['required_by']))
            {
                continue;
            }

            while ($rmeta['required_by'] &&
                in_array($package, $rmeta['required_by']))
            {
                $rkey = array_search($package, $rmeta['required_by']);
                unset($rmeta['required_by'][$rkey]);
            }

            \core\pkg::update($dependency, $rmeta);
        }

        \core\pkg::remove($package);
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

        if (dir::exists(fs::pkgreal($package, 'sh')))
        {
            foreach (fs::ls(fs::pkgreal($package, 'sh'), '/\\.php$/') as $file)
            {
                $files[] = substr($file, 0, -4);
            }
        }

        return $files;
    }
}
