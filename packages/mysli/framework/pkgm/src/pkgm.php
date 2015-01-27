<?php

namespace mysli\framework\pkgm;

__use(__namespace__, '
    mysli/framework/fs/{fs,file}
    mysli/framework/json
    mysli/framework/ym
    mysli/framework/exception/{...} AS framework/exception/{...}
');

class pkgm {

    private static $packages = [];

    /**
     * List of curently enabled packages.
     * @return array
     */
    static function dump() {
        return self::$packages;
    }
    /**
     * Get package name from path - this must be full absolute path.
     * @param  string $path
     * @return mixed  string (package name) or false if not found
     */
    static function name_from_path($path) {
        $path = str_replace('\\', '/', $path);
        if (substr($path, 0, strlen(fs::pkgpath())) !== fs::pkgpath()) {
            return false;
        }
        $package = substr($path, strlen(fs::pkgpath()));
        $package = explode('/', $package);
        if (count($package) >= 3) {
            if (self::exists(implode('/', array_slice($package, 0, 3)))) {
                return implode('/', array_slice($package, 0, 3));
            } elseif (
                self::exists(implode('/', array_slice($package, 0, 2)))) {
                return implode('/', array_slice($package, 0, 2));
            }
        } elseif (count($package) === 2) {
            $package = implode('/', $package);
            if (self::exists($package)) {
                return $package;
            }
        }

        return false;
    }
    /**
     * Get package namespace from path - this must be full absolute path.
     * /www/dir/packages/vendor/meta/package/src/sub/file.php =>
     *     vendor\meta\package\sub\file
     * @param  string $path
     * @return mixed  string (package name) or false if not found
     */
    static function namespace_from_path($path) {
        // this will give us: vendor/meta/package
        if (!($pkg_name = self::name_from_path($path))) {
            return false;
        }
        $file = substr($path, strpos($path, $pkg_name) + strlen($pkg_name));
        if (substr($file, 1, 3) === 'src') {
            $file = substr($file, 5);
        }
        if ($file) {
            $file = substr($file, 0, strpos($file, '.'));
        } else {
            $file = substr($pkg_name, strrpos($pkg_name, '/'));
        }

        return str_replace('/', '\\', fs::ds($pkg_name, $file));
    }
    /**
     * Check weather package exists (is available) in fs.
     * @param  string  $package
     * @return boolean
     */
    static function exists($package) {
        return file_exists(fs::pkgpath($package, 'mysli.pkg.ym'));
    }
    /**
     * Check weather package is enabled.
     * @param  string  $package
     * @return boolean
     */
    static function is_enabled($package) {
        return isset(self::$packages[$package]);
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
    static function list_enabled() {
        return array_keys(self::$packages);
    }
    /**
     * Get all disabled packages
     * @return array
     */
    static function list_disabled() {
        $disabled = [];

        foreach (fs::ls(fs::pkgpath()) as $vendor) {
            foreach (fs::ls(fs::pkgpath($vendor)) as $meta) {
                $root = "{$vendor}/{$meta}";

                if (file::exists(fs::pkgpath($root, 'mysli.pkg.ym'))) {
                    if (!self::is_enabled($root)) {
                        $disabled[] = $root;
                    }
                    continue;
                }

                foreach (fs::ls(fs::pkgpath($root)) as $package) {
                    if (file::exists(
                        fs::pkgpath($root, $package, 'mysli.pkg.ym'))) {
                        if (!self::is_enabled("{$root}/{$package}")) {
                            $disabled[] = "{$root}/{$package}";
                        }
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
    static function list_obsolete() {
        $enabled = self::$packages;
        $obsolete = [];

        do {
            $chg = false;

            foreach ($enabled as $package => &$meta) {
                if (!empty($meta['required_by'])) {
                    foreach ($meta['required_by'] as $id => $req_pkg) {
                        if (in_array($req_pkg, $obsolete)) {
                            unset($meta['required_by'][$id]);
                            $chg = true;
                        }
                    }
                }
                if (empty($meta['required_by']) && $meta['enabled_by']) {
                    unset($enabled[$package]);
                    $obsolete[] = $package;
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
    static function list_dependees($package, $deep=false) {
        $meta = self::meta($package);
        if (!$deep) {
            return isset($meta['required_by']) ? $meta['required_by'] : [];
        }
        $dependees = [$package];
        foreach ($meta['required_by'] as $dependee) {
            $dependees[] = $dependee;
            $dependees = array_merge(
                self::list_dependees($dependee, true), $dependees);
        }
        return array_values(array_unique($dependees));
    }
    /**
     * List dependencies of package.
     * If you set $deep to true, it will resolve deeper relationships,
     * i.e. dependencies of dependencies
     * @param  string  $package
     * @param  boolean $deep
     * @param  string  $group packages of which group to enable:
     * null (required), recommend, dev, ... (other special groups)
     * @param  array   $process internal helper. Prevent infinite loop,
     * if cross dependency situation occurs (a require b and b require a).
     * @return array
     */
    static function list_dependencies(
        $package, $deep=false, $group=null, array $process=[])
    {
        $meta = self::meta($package);

        $list = [
            'enabled'   => [],
            'disabled'  => [],
            'missing'   => []
        ];

        // Resolve group
        $group = $group ? "require-{$group}" : 'require';

        if (!isset($meta[$group])) {
            return $list;
        }

        foreach ($meta[$group] as $dependency => $version) {

            // Extension?
            if (substr($dependency, 0, 14) === 'php/extension/') {
                $extension = substr($dependency, 14);
                if (extension_loaded($extension)) {
                    $list['enabled'][$dependency] = $version;
                } else {
                    $list['missing'][$dependency] = $version;
                }
                continue;
            }

            // Normal package
            $dependency = self::resolve_relative($dependency, $package);
            if (!self::exists($dependency)) {
                $list['missing'][$dependency] = $version;
            } else {
                if (self::is_enabled($dependency)) {
                    $list['enabled'][$dependency] = $version;
                } else {
                    $list['disabled'][$dependency] = $version;
                }
            }
        }

        if (!$deep) return $list;

        // Prevent infinite loops
        $hash = $package . ': ' . implode(
            ', ', array_keys($meta[$group]));

        if (in_array($hash, $process)) {
            $process[count($process) - 1] = ' >> ' .
                $process[count($process) - 1];
            $process[] = ' >> ' . $hash;
            array_unshift($process, '----------');
            $process[] = '----------';
            throw new exception\dependency(
                "Infinite loop, cross dependencies:\n" .
                implode("\n", $process));
        }
        $process[] = $hash;

        foreach ($list['disabled'] as $dependency => $version) {
            $nlist = self::list_dependencies($dependency, true, null, $process);
            $list['enabled']  = array_merge(
                                    $nlist['enabled'], $list['enabled']);
            $list['disabled'] = array_merge(
                                    $nlist['disabled'], $list['disabled']);
            $list['missing']  = array_merge(
                                    $nlist['missing'], $list['missing']);
        }

        return $list;
    }
    /**
     * Get meta for particular package.
     * @param  string  $package
     * @param  boolean $force_read force package meta to be read from file
     * @return array
     */
    static function meta($package, $force_read=false) {
        if (self::is_enabled($package) && !$force_read) {
            return self::$packages[$package];
        } elseif (self::exists($package)) {
            $file = fs::pkgpath($package, 'mysli.pkg.ym');
            if (file::exists($file)) {
                $meta = ym::decode_file($file);
                $meta['require'] = $meta['require'] ?: [];
                return $meta;
            } else {
                throw new framework\exception\not_found(
                    "Fild `mysli.pkg.ym` not found for: ".
                    "`{$package}`.", 1);
            }
        } else {
            throw new framework\exception\not_found(
                "The package doesn't exists: `{$package}`.", 2);
        }
    }
    /**
     * Enable package. This will NOT run the setup.
     * @param  string $package
     * @param  string $enabled_by if provided, this package will become
     * obsolete when $enabled_by will be disabled (if nothing else)
     * depends on it.
     * @return boolean
     */
    static function enable($package, $enabled_by=null) {
        if (self::is_enabled($package)) {
            throw new exception\package(
                "The package is already enabled: `{$package}`.", 1);
        }
        if (!self::exists($package)) {
            throw new framework\exception\not_found(
                "The package doesn't exists: `{$package}`.", 2);
        }
        $meta = self::meta($package);

        foreach ($meta['require'] as $dependency => $version) {
            $dependency = self::resolve_relative($dependency, $package);
            if (self::is_enabled($dependency)) {
                self::$packages[$dependency]['required_by'][] = $package;
            }
        }

        $meta['enabled_by']  = $enabled_by;
        $meta['enabled_on']  = time();
        $meta['required_by'] = [];

        // Does any of the enabled packages require this package?
        // This happened sometimes, especially when replacing packages.
        if (!empty(self::$packages)) {
            foreach (self::$packages as $lpkg => $lmeta) {
                if (!isset($lmeta['require'])) {
                    continue;
                }
                foreach ($lmeta['require'] as $depends_on => $version) {
                    $depends_on = self::resolve_relative($depends_on, $lpkg);
                    if ($depends_on === $package &&
                        !in_array($lpkg, $meta['required_by'])) {
                        $meta['required_by'][] = $lpkg;
                    }
                }
            }
        }

        self::$packages[$package] = $meta;
        return self::write();
    }
    /**
     * Disable package. This will NOT run the setup.
     * @param  string $package
     * @return boolean
     */
    static function disable($package) {
        if (!self::is_enabled($package)) {
            throw new exception\package(
                "The package is not enabled: `{$package}`.", 1);
        }
        $meta = self::$packages[$package];
        foreach ($meta['require'] as $dependency => $version) {
            $required_by =& self::$packages[$dependency]['required_by'];
            while ($required_by && in_array($package, $required_by)) {
                $rkey = array_search($package, $required_by);
                unset($required_by[$rkey]);
            }
        }

        unset(self::$packages[$package]);
        return self::write();
    }
    /**
     * Open packages file.
     * @return boolean
     */
    static function read() {
        self::$packages = json::decode_file(
            fs::datpath('pkgm/r.json'), true);
        return is_array(self::$packages);
    }
    /**
     * Resolve relative package relationship (../ => n/s/pkg)
     * @param  string $pkg
     * @param  string $parent
     * @return string
     */
    private static function resolve_relative($pkg, $parent) {
        if (substr($pkg, 0, 3) === '../') {
            $root = substr($parent, 0, strrpos($parent, '/'));
            return $root . substr($pkg, 2);
        } else return $pkg;
    }
    /**
     * Write packages to the file.
     * @return boolean
     */
    private static function write() {
        return json::encode_file(
            fs::datpath('pkgm/r.json'), self::$packages);
    }
}
