<?php

namespace mysli\framework\core {
    class autoloader {

        private static $initialized = ['mysli/framework/core'];

        /**
         * Autoloader
         * @param  string $class
         * @return boolean
         */
        static function load($class) {
            // Cannot handle non-namespaced requests!
            if (strpos($class, '\\') === false) { return false; }

            $class = ltrim($class, '\\');
            $segments = explode('\\', $class);

            // Convert mysli\core => mysli\core\core
            if (count($segments) === 2) {
                $segments[] = $segments[1];
                $alias = $class;
                $class = implode('\\', $segments);
            } else {
                $alias = false;
            }

            // Add src segment
            $segments = array_merge(
                array_slice($segments, 0, 2),
                ['src'],
                array_slice($segments, 2));

            $path = MYSLI_PKGPATH . '/' . implode('/', $segments) . '.php';

            if (!file_exists($path)) {
                return false;
            }

            include($path);

            if ($alias) {
                class_alias($class, $alias);
            }

            if (class_exists($class, false)) {
                self::execute_init($class);
                return true;
            } else {
                return false;
            }
        }
        /**
         * Run init if this package is loader first time.
         * @param  string $class
         * @return null
         */
        private static function execute_init($class) {
            $namespace = explode('\\', $class);
            $package = $namespace[0] . '/' . $namespace[1];
            $namespace = $namespace[0] . '\\' . $namespace[1];
            if (in_array($package, self::$initialized)) {
                return;
            }
            if (function_exists($namespace . '\\__init')) {
                call_user_func($namespace . '\\__init');
            } else {
                $path = MYSLI_PKGPATH . '/' . $package . '/__init.php';
                if (file_exists($path)) {
                    include $path;
                }
                if (function_exists($namespace . '\\__init')) {
                    call_user_func($namespace . '\\__init');
                }
            }
            self::$initialized[] = $package;
        }
    }
}
