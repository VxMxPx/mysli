<?php

namespace mysli\framework\core {
    class autoloader {

        private static $aliases     = [];
        private static $initialized = ['mysli/framework/core/'];

        /**
         * Autoloader
         * FIX!!
         * @param  string $class
         * @return boolean
         */
        static function load($class) {

            // See if it's set in aliases
            if (isset(self::$aliases[$class])) {
                if (self::load_class(self::$aliases[$class])) {
                    class_alias(self::$aliases[$class], $class);
                    return true;
                } else {
                    return false;
                }
            }


            if (self::load_class($class)) {
                return true;
            }

            // See if it's set as regex in aliseas
            foreach (self::$aliases as $pattern => $required) {

                // Only regex classes here
                if (substr($pattern, 0, 1) !== '/') {
                    continue;
                }

                if (preg_match($pattern, $class, $match)) {
                    $required = str_replace('{...}', $match[1], $required);
                    if (self::load_class($required)) {
                        class_alias($required, $class);
                        return true;
                    } else {
                        return false;
                    }
                }
            }

            return false;
        }
        /**
         * Add alias to be considered when autoloading.
         * @param string $from vendor/package/{...}
         * @param string $as   vendor/package/{...}
         */
        static function add_alias($from, $as) {

            if ($from === $as) {
                return;
            }

            // Resolve {...}
            if (strpos($as, '{...}') !== false) {
                $as = str_replace('{...}', '*', $as);
                $as = str_replace('/', '\\', $as);
                $as = preg_quote($as);
                $as = str_replace('\\*', '(.*?)', $as);
                $as = "/^{$as}$/";
            } else {
                $as = str_replace('/', '\\', $as);
            }

            // Correct / in alias
            $from = str_replace('/', '\\', $from);
            if (!strpos($from, '{...}') && !strpos($from, ',')) {
                list($_, $_, $from, $_) = self::resolve_class($from);
                if (!$from) {
                    throw new \Exception(
                        "Couldn't resolve class:\n".
                        ">> {$from} << AS {$as}");
                }
            }

            if (isset(self::$aliases[$as]) &&
                self::$aliases[$as] !== $from)
            {
                throw new \Exception(
                    "Failed:\n   ".self::$aliases[$as] . " AS {$as}\n".
                    ">> {$from} AS {$as}\n");
            }

            // As is being unique here, so we set it as key
            // there can be multiple `from` for different as
            self::$aliases[$as] = $from;
        }

        /**
         * Resolve class to filename, and add actual class to namespace
         * if missing, e.g.: vendor/package => vendor/package/packag (class)
         * @param  string $class
         * @return array  [
         *         $root,   // Root package name
         *         $path,   // Class full absolute path
         *         $class,  // Full resolved class name
         *         $alias   // Short class name (to be aliased as)
         * ]
         */
        private static function resolve_class($class) {

            $segments = explode("\\", $class);

            $meta = (count($segments) > 2) &&
                        file_exists(
                            MYSLI_PKGPATH.'/'.
                            implode('/', array_slice($segments, 0, 3)).
                            '/mysli.pkg.ym');
            $rootc = $meta ? 3 : 2;

            // get root (vendor+meta+package)
            $root = implode('/', array_slice($segments, 0, $rootc)) . '/';

            // get sub-path
            if (count($segments) > $rootc+1) {
                $spath = implode('/', array_slice($segments, $rootc, -1)) . '/';
            } else {
                $spath = '';
            }

            // get file
            if (count($segments) === $rootc) {
                $alias = implode('\\', $segments);
            } else {
                $alias = false;
            }

            $file = array_slice($segments, -1)[0];

            // full path & class
            $path = MYSLI_PKGPATH . "/{$root}src/{$spath}{$file}.php";
            $class = str_replace('/', '\\', $root.$spath.$file);

            return [$root, $path, $class, $alias];
        }
        /**
         * Load actual class (and initialized it if required)
         * @param  string $class
         * @return boolean
         */
        private static function load_class($class) {

            list($root, $path, $class, $alias) = self::resolve_class($class);

            if (!file_exists($path)) {
                return false;
            }

            if (!class_exists($class, false) && !trait_exists($class, false)) {
                include($path);
            }

            if (!class_exists($class) && !trait_exists($class, false)) {
                throw new \Exception(
                    "Class: `{$class}` not found in: `{$path}`.", 1);
            }

            if ($alias && !class_exists($alias, false)) {
                class_alias($class, $alias);
            }

            if (!in_array($root, self::$initialized)) {
                self::initialize($root);
            }

            return true;
        }
        /**
         * Run init if this package is loader first time.
         * @param  string  $package
         * @return null
         */
        private static function initialize($package) {
            // under no circumstance load __init twice
            self::$initialized[] = $package;

            $function = str_replace('/', '\\', $package.'__init');
            $path = MYSLI_PKGPATH . "/{$package}src/__init.php";
            if (!function_exists($function)) {
                if (file_exists($path)) {
                    include($path);
                }
            }
            if (function_exists($function)) {
                call_user_func($function);
            }
        }
    }
}
