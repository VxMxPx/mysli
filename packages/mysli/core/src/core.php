<?php

namespace mysli\core {
    class core {
        static function init($datpath, $pkgpath) {
            if (!$datpath || !is_dir($datpath)) {
                throw new \Exception("Invalid datpath: `{$datpath}`.", 1);
            }

            if (!$pkgpath || !is_dir($pkgpath) ||
                mb_substr(__DIR__, 0, mb_strlen($pkgpath)) !== $pkgpath) {
                throw new \Exception("Invalid pkgpath: `{$pkgpath}`.", 2);
            }

            define('MYSLI_DATPATH', $datpath);
            define('MYSLI_PKGPATH', $pkgpath);

            include(rtrim(__DIR__, '\\/') . '/common.php');

            spl_autoload_register(['\\mysli\\core\\core', 'autoload']);
        }
        static function autoload($class) {
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

            // Check if exception or script
            if (in_array($segments[count($segments) - 2], ['exception', 'script'])) {
                $segments[count($segments) - 2] .= 's';
            }

            $path = MYSLI_PKGPATH . '/' . implode('/', $segments) . '.php';

            if (!file_exists($path)) {
                return false;
            }

            include($path);

            if ($alias) {
                class_alias($class, $alias);
            }

            return class_exists($class, false);
        }
    }
}
