<?php

namespace mysli\framework\pkgm;

__use(__namespace__, '
    mysli.framework.fs/fs,dir,file
    mysli.framework.json
    mysli.framework.ym
    mysli.framework.exception/* AS framework\exception\*
');

class pkgm {

    // List of enabled packages
    private static $packages = [
        // Map is a list from {datpath}/boot/packages.json
        'map_path' => null,
        'map' => [],
        // Full is a list from {datpath}/mysli/framework/pkgm/r.json
        'full_path' => null,
        'full' => []
    ];

    /**
     * Init package manager
     * @param  string $map_path
     * @param  string $full_path
     * @return null
     */
    static function __init($map_path, $full_path) {

        if (self::$packages['map_path'] || self::$packages['full_path']) {
            throw new framework\exception\init("Already initialized.", 10);
        }

        if (!file::exists($map_path)) {
            throw new framework\exception\not_found(
                "File not found: `{$map_path}`", 20);
        }
        if (!file::exists($full_path)) {
            throw new framework\exception\not_found(
                "File not found: `{$full_path}`", 21);
        }

        self::$packages['map_path']  = $map_path;
        self::$packages['full_path'] = $full_path;
        self::read();
    }

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

        // Phar version
        if (substr(explode('/', $package, 2)[0], -5) === '.phar') {
            return explode('/', $package, 2)[0];
        }

        // Dev version
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

        // Phar
        if (substr($pkg_name, -5) === '.phar') {
            return str_replace('.', '\\', explode('-r', $pkg_name, 2)[0]);
        }

        // Dev
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
        if (strpos($package, '.')) {
            // Phar
            return file::exists(fs::pkgpath($package));
        } else {
            // Dev
            return file::exists(fs::pkgpath($package, 'mysli.pkg.ym'));
        }
    }
    /**
     * Check weather package is enabled.
     * @param  string  $package
     * @return boolean
     */
    static function is_enabled($package) {
        return isset(self::$packages['full'][$package]);
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
        return array_values(self::$packages['map']);
    }
    /**
     * Get all disabled packages
     * @return array
     */
    static function list_disabled() {
        $disabled = [];

        foreach (fs::ls(fs::pkgpath()) as $vendor) {
            if (substr($vendor, -5) === '.phar') {
                $disabled[] = substr($vendor, 0, -5);
                continue;
            }
            foreach (fs::ls(fs::pkgpath($vendor)) as $sub) {

                $root = "{$vendor}/{$sub}";

                if (!dir::exists($root)) {
                    continue;
                }

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

        $enabled = self::$packages['full'];
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
                self::list_dependees($dependee, true),
                $dependees
            );
        }
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
    static function list_dependencies(
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

        if (!isset($meta[$group])) {
            return $list;
        }

        foreach ($meta[$group] as $dependency => $rrelease) {

            // Extension?
            if (substr($dependency, 0, 14) === 'php.extension.') {
                $extension = substr($dependency, 14);
                if (extension_loaded($extension)) {
                    $list['enabled'][$dependency] = $dependency;
                } else {
                    $list['missing'][$dependency] = $dependency;
                }
                continue;
            }

            // Normal package
            $rdependency = self::find_by_release($dependency, $rrelease);
            if (!$rdependency) {
                $list['missing'][$dependency] = $rdependency;
            } else {
                if (self::is_enabled($rdependency)) {
                    $list['enabled'][$dependency] = $rdependency;
                } else {
                    $list['disabled'][$dependency] = $rdependency;
                }
            }
        }

        if (!$deep) return $list;

        // Prevent infinite loops
        $hash = $package . ': ' . implode(', ', array_keys($meta[$group]));

        if (in_array($hash, $proc)) {
            $proc[count($proc)-1] = ' >> '.$proc[count($proc) - 1];
            $proc[] = ' >> '.$hash;
            array_unshift($proc, '----------');
            $proc[] = '----------';
            throw new exception\dependency(
                "Infinite loop, cross dependencies:\n".implode("\n", $proc));
        }
        $proc[] = $hash;

        foreach ($list['disabled'] as $dependency => $rdependency) {
            $nlist = self::list_dependencies($rdependency, true, null, $proc);
            $list['enabled']  = array_merge(
                $nlist['enabled'], $list['enabled']
            );
            $list['disabled'] = array_merge(
                $nlist['disabled'], $list['disabled']
            );
            $list['missing']  = array_merge(
                $nlist['missing'], $list['missing']
            );
        }

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
        if (!$source) {
            $source = self::list_all();
        }

        if (substr($release, 0, 1) !== 'r') {
            $release = "r*.{$release}";
        }
        $release = preg_quote($release);
        if (strpos($release, '\\*')) {
            $release = str_replace('\\*', '.*?', $release);
        }
        $regex = "/^{$package}-{$release}$/";

        foreach ($source as $pkg) {
            if (preg_match($regex, $pkg)) {
                return $pkg;
            }
        }

        // Take care of source packages
        $package = str_replace('.', '/', $package);
        if (in_array($package, $source)) {
            return $package;
        } else {
            return false;
        }
    }
    /**
     * Get meta for particular package.
     * @param  string  $package
     * @param  boolean $force_read force package meta to be read from file
     * @return array
     */
    static function meta($package, $force_read=false) {
        if (self::is_enabled($package) && !$force_read) {
            return self::$packages['full'][$package];
        } elseif (self::exists($package)) {
            $file = fs::pkgpath($package, 'mysli.pkg.ym');
            if (file::exists($file)) {
                $meta = ym::decode_file($file);
                $meta['require'] = $meta['require'] ?: [];
                return $meta;
            } else {
                throw new framework\exception\not_found(
                    "Fild `mysli.pkg.ym` not found for: `{$package}`.", 1);
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
        // if (isset(self::$packages['map'][$meta['package']])) {
        //     throw new exception\package(
        //         "Different version of this package `{$package}` is already ".
        //         "enabled: `".self::$packages['map'][$meta['package']].'`', 3);
        // }

        foreach ($meta['require'] as $dependency => $version) {
            $rdependency = self::find_by_release($dependency, $version);
            if ($rdependency && self::is_enabled($rdependency)) {
                self::$packages['full'][$rdependency]['required_by'][] = $package;
            } else {
                throw new exception\dependency(
                    "Dependency not satisfied: `{$dependency} : {$version}`", 4);
            }
        }

        $meta['enabled_by']  = $enabled_by;
        $meta['enabled_on']  = time();
        $meta['required_by'] = [];
        $meta['sh'] = self::discover_scripts($package);

        // Does any of the enabled packages require this package?
        // This happened sometimes, especially when replacing packages.
        if (!empty(self::$packages['full'])) {
            foreach (self::$packages['full'] as $lpkg => $lmeta) {
                if (!isset($lmeta['require'])) {
                    continue;
                }
                foreach ($lmeta['require'] as $depends_on => $version) {
                    $depends_on = self::find_by_release($depends_on, $version);
                    if ($depends_on && $depends_on === $package &&
                        !in_array($lpkg, $meta['required_by']))
                    {
                        $meta['required_by'][] = $lpkg;
                    }
                }
            }
        }

        self::$packages['full'][$package] = $meta;
        self::$packages['map'][$meta['package']] = $package;
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
        $meta = self::$packages['full'][$package];
        foreach ($meta['require'] as $dependency => $version) {
            $required_by =& self::$packages['full'][$dependency]['required_by'];
            while ($required_by && in_array($package, $required_by)) {
                $rkey = array_search($package, $required_by);
                unset($required_by[$rkey]);
            }
        }

        unset(self::$packages['full'][$package]);
        unset(self::$packages['map'][$meta['package']]);
        return self::write();
    }

    // Private

    /**
     * Discover scripts for package.
     * @param  string $package
     * @return array
     */
    private static function discover_scripts($package) {
        $files = [];
        if (dir::exists(fs::pkgpath($package, 'sh'))) {
            foreach (fs::ls(fs::pkgpath($package, 'sh'), '/\\.php$/') as $file)
            {
                $files[] = substr($file, 0, -4);
            }
        }
        return $files;
    }
    /**
     * Open packages file.
     * @return boolean
     */
    private static function read() {

        if (!self::$packages['map_path'] || !self::$packages['full_path']) {
            throw new framework\exception\init("Not initialized.", 20);
        }

        self::$packages['map'] = json::decode_file(
            self::$packages['map_path'], true);

        self::$packages['full'] = json::decode_file(
            self::$packages['full_path'], true);

        return is_array(self::$packages['map']) &&
            is_array(self::$packages['full']);
    }
    /**
     * Write packages to the file.
     * @return boolean
     */
    private static function write() {

        if (!self::$packages['map_path'] || !self::$packages['full_path']) {
            throw new framework\exception\init("Not initialized.", 30);
        }

        // \core\autoloader::__init(self::$packages['map']);

        return json::encode_file(
            self::$packages['map_path'], self::$packages['map']
        ) && json::encode_file(
            self::$packages['full_path'], self::$packages['full']
        );
    }
}
