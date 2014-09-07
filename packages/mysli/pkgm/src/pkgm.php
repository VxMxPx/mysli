<?php

namespace mysli\pkgm {

    \inject::to(__namespace__)
    ->from('mysli/fs')
    ->from('mysli/json');

    class pkgm {

        private static $packages = [];

        /**
         * Check weather package exists (is available) in fs.
         * @param  string  $package
         * @return boolean
         */
        static function exists($package) {
            return file_exists(fs::pkgpath($package, 'mysli.pkg.json'));
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

            foreach (scandir(fs::pkgpath()) as $vendor) {
                if (substr($vendor, 0, 1) === '.') { continue; }
                foreach (scandir(fs::pkgpath($vendor)) as $package_name) {
                    if (substr($package, 0, 1) === '.') { continue; }
                    $package = $vendor . '/' . $package_name;
                    if (self::is_enabled($package)) { continue; }
                    $disabled[] = $package;
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
         * List dependees (the packages which require procided package,
         * i.e. are dependant on it)
         * @param  string  $package
         * @param  boolean $deep
         * @param  array   $listed internal helper
         * @return array
         */
        static function list_dependees($package, $deep=false,
                                       array &$listed=[]) {
            $meta = self::meta($package);
            if (!$deep) {
                return isset($meta['required_by']) ? $meta['required_by'] : [];
            }
            $dependees = [$package];
            // optimization
            if (!in_array($package, $listed)) {
                $listed[] = $package;
                foreach ($meta['required_by'] as $dependee) {
                    $dependees[] = $dependee;
                    $dependees = array_merge(
                        self::list_dependees($dependee, true, $listed),
                        $dependees);
                }
            }
            return array_values(array_unique($dependees));
        }
        /**
         * List dependencies of package.
         * If you set $deep to true, it will resolve deeper relationships,
         * i.e. dependencies of dependencies
         * @param  string  $package
         * @param  boolean $deep
         * @param  array   $process internal helper. Prevent infinite loop,
         * if cross dependency situation occurs (a require b and b require a).
         * @return array
         */
        static function list_dependencies($package, $deep=false,
                                          array $process=[]) {
            $meta = self::meta($package);

            $list = [
                'enabled'  => [],
                'disabled' => [],
                'missing'  => []
            ];

            foreach ($meta['require'] as $dependency => $version) {
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
                ', ', array_keys($meta['require']));

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
                $nlist = self::list_dependencies($dependency, true, $process);
                $list['enabled'] = array_merge(
                    $nlist['enabled'], $list['enabled']);
                $list['disabled'] = array_merge(
                    $nlist['disabled'], $list['disabled']);
                $list['missing'] = array_merge(
                    $nlist['missing'], $list['missing']);
            }

            return $list;
        }
        /**
         * Get meta for particular package.
         * @param  string $package
         * @return array
         */
        static function meta($package) {
            if (self::is_enabled($package)) {
                return self::$packages[$package];
            } elseif (self::exists($package)) {
                $file = fs::pkgpath($package, 'meta/mysli.pkg.json');
                if (fs\file::exists($file)) {
                    return json::decode_file($file);
                } else {
                    throw new exception\not_found(
                        "Fild `meta/mysli.pkg.json` not found for: ".
                        "`{$package}`.", 1);
                }
            } else {
                throw new exception\not_found(
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
                throw new exception\not_found(
                    "The package doesn't exists: `{$package}`.", 2);
            }
            $meta = self::meta($package);

            foreach ($meta['require'] as $dependency => $version) {
                self::$packages[$dependency]['required_by'][] = $package;
            }

            $meta['enabled_by']  = $enabled_by;
            $meta['enabled_on']  = time();
            $meta['required_by'] = [];

            // Does any of the enabled packages require this package?
            // This happened sometimes, especially when replacing packages.
            foreach (self::$packages as $lpkg => $lmeta) {
                foreach ($lmeta['require'] as $depends_on => $version) {
                    if ($depends_on === $package) {
                        $meta['required_by'][] = $lpkg;
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
                if (in_array($package, $required_by)) {
                    $rkey = array_search($package, $required_by);
                    unset($required_by, $rkey);
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
                fs::datpath('pkgm/r.json'));
            return is_array(self::$package);
        }
        /**
         * Write packages to the file.
         * @return boolean
         */
        private static function write() {
            return json::enacode_file(
                fs::datpath('pkgm/r.json'), self::$packages);
        }
    }
}
