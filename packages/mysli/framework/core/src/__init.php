<?php

namespace mysli\framework\core {
    /**
     * Init the mysli system, set base paths and register autoloader
     * @param  string $datpath
     * @param  string $pkgpath
     * @param  mixed  $autoloader expecting function or array class method
     * @param  string $injector expeting class
     * @return null
     */
    function __init(
                $datpath,
                $pkgpath,
                $autoloader=['\\mysli\\framework\\core\\autoloader', 'load'])
    {

        if (defined('MYSLI_PKGPATH') || defined('MYSLI_DATPATH')) {
            throw new \Exception(
                "MYSLI_PKGPATH or MYSLI_DATPATH is already defined.", 1);
        }
        if (!$datpath || !is_dir($datpath)) {
            throw new \Exception("Invalid datpath: `{$datpath}`.", 1);
        }
        if (!$pkgpath || !is_dir($pkgpath) ||
            mb_substr(__DIR__, 0, mb_strlen($pkgpath)) !== $pkgpath) {
            throw new \Exception("Invalid pkgpath: `{$pkgpath}`.", 2);
        }

        define('MYSLI_CORE',    'mysli/framework/core');
        define('MYSLI_DATPATH', $datpath);
        define('MYSLI_PKGPATH', $pkgpath);

        include(rtrim(__DIR__, '\\/') . '/common.php');

        if ($autoloader[0] === '\\mysli\\framework\\core\\autoloader') {
            include(rtrim(__DIR__, '\\/') . '/autoloader.php');
        }

        spl_autoload_register($autoloader);
        class_alias($autoloader[0], 'core\\autoloader');
    }
}
