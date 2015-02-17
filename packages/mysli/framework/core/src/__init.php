<?php

namespace mysli\framework\core;

/**
 * Init the mysli system, set base paths and register autoloader
 * @param  string $datpath
 * @param  string $pkgpath
 * @return null
 */
function __init($datpath, $pkgpath)
{
    $tmppath = rtrim($datpath,'\\/').DIRECTORY_SEPARATOR.'temp';

    if (defined('MYSLI_PKGPATH') || defined('MYSLI_DATPATH'))
        throw new \Exception(
            "MYSLI_PKGPATH or MYSLI_DATPATH is already defined.", 1);

    if (!$datpath || !is_dir($datpath))
        throw new \Exception("Invalid datpath: `{$datpath}`.", 2);

    if (!$tmppath || !is_dir($tmppath))
        throw new \Exception("Invalid tmppath: `{$tmppath}`.", 3);

    $abs = substr(__DIR__, 0, 7) === 'phar://' ? substr(__DIR__, 7) : __DIR__;

    if (!$pkgpath || !is_dir($pkgpath) ||
        mb_substr($abs, 0, mb_strlen($pkgpath)) !== $pkgpath)
    {
        throw new \Exception("Invalid pkgpath: `{$pkgpath}`.", 4);
    }

    define('MYSLI_DATPATH', $datpath);
    define('MYSLI_PKGPATH', $pkgpath);
    define('MYSLI_TMPPATH', $tmppath);

    include(rtrim(__DIR__, '\\/') . '/common.php');

    // Boot
    $boot_path = $datpath.'/boot/r.json';

    if (!file_exists($boot_path))
        throw new \Exception("File not found: `{$boot_path}`", 5);

    $bootr = json_decode(file_get_contents($boot_path), true);

    define('MYSLI_CORE_PKG',     $bootr['boot']['core']);
    define('MYSLI_CORE_PKG_REL', $bootr['map'][MYSLI_CORE_PKG]);

    __get_pkg($bootr['boot']['pkg'], $bootr['map']);
    \core\pkg::__init($boot_path);
    __get_autoloader($bootr['boot']['autoloader'], $bootr['map']);
}
// Load: pkg
function __get_pkg(array $pkg, array $packages)
{
    list($package, $file) = explode('/', $autoloader, 2);
    $class = str_replace('.', '\\', $package);
    $class = $class.'\\'.str_replace('/', '\\', $file);
    __get_std_class($package, $packages, $file, $class);
    class_alias($class, 'core\\pkg');
}
// Load: autoloader
function __get_autoloader(array $autoloader, array $packages)
{
    list($package, $call) = explode(':', $autoloader, 2);
    list($package, $file) = explode('/', $autoloader, 2);
    $class = str_replace('.', '\\', $package);
    $class = $class.'\\'.str_replace('/', '\\', $file);
    __get_std_class($package, $packages, $file, $class);
    class_alias($class, 'core\\autoloader');
    spl_autoload_register(['core\\autoloader', $call]);
}
// Standard loader for class
function __get_std_class($package, $packages, $file, $class)
{
    if (!isset($packages[$package]))
        throw new \Exception(
            "Package not available: `{$package}`", 1);
    else
        $release = $packages[$package];

    $is_phar = !strpos($release, '/');
    $path = ($is_phar?'phar://':'').MYSLI_PKGPATH.'/'.$release.
            ($is_phar?'.phar':'').'/src/'.$file.'.php';

    if (!file_exists($path))
        throw new \Exception("File not found: `{$path}`", 2);
    else
        include $path;

    if (!class_exists($class, false))
        throw new \Exception(
            "Class: `{$class}` not found in `{$path}`.", 3);
}
