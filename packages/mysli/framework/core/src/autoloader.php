<?php

namespace mysli\framework\core {
    class autoloader {

        private static $initialized = ['mysli/framework/core/'];

        /**
         * Autoloader
         * @param  string $class
         * @return boolean
         */
        static function load($class) {
            $segments = explode("\\", $class);

            $root = '';
            $path = '';
            $file = '';

            $has_meta = (count($segments) > 2) &&
                file_exists(MYSLI_PKGPATH.'/'.
                implode('/', array_slice($segments, 0, 3)). '/mysli.pkg.ym');
            $rootc = $has_meta ? 3 : 2;

            // get root (vendor+meta+package)
            $root = implode('/', array_slice($segments, 0, $rootc)) . '/';

            // get sub-path
            if (count($segments) > $rootc+1) {
                $path = implode('/', array_slice($segments, $rootc, -1)) . '/';
            }

            // get file
            if (count($segments) === $rootc) {
                $alias = implode('\\', $segments);
            } else $alias = false;
            $file = array_slice($segments, -1)[0];

            // full path & class
            $full_path = MYSLI_PKGPATH . "/{$root}src/{$path}{$file}.php";
            $class = str_replace('/', '\\', $root.$path.$file);

            if (!file_exists($full_path)) {
                return false;
            }

            if (!class_exists($class)) {
                include($full_path);
            }

            if (!class_exists($class)) {
                throw new \Exception(
                    "Class: `{$class}` not found in: `{$full_path}`.", 1);
            }

            if ($alias) {
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
