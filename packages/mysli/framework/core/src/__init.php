<?php

namespace mysli\framework\core;

/**
 * Init the mysli system, set base paths and register autoloader
 * @param  string $datpath
 * @param  string $pkgpath
 * @return null
 */
function __init($datpath, $pkgpath) {

    $tmppath = rtrim($datpath,'\\/').DIRECTORY_SEPARATOR.'temp';

    if (defined('MYSLI_PKGPATH') || defined('MYSLI_DATPATH')) {
        throw new \Exception(
            "MYSLI_PKGPATH or MYSLI_DATPATH is already defined.", 1);
    }
    if (!$datpath || !is_dir($datpath)) {
        throw new \Exception("Invalid datpath: `{$datpath}`.", 2);
    }
    if (!$tmppath || !is_dir($tmppath)) {
        throw new \Exception("Invalid tmppath: `{$tmppath}`.", 3);
    }
    $abs = substr(__DIR__, 0, 7) === 'phar://' ?
        substr(__DIR__, 7) :
        __DIR__;
    if (!$pkgpath || !is_dir($pkgpath) ||
        mb_substr($abs, 0, mb_strlen($pkgpath)) !== $pkgpath) {
        throw new \Exception("Invalid pkgpath: `{$pkgpath}`.", 4);
    }

    define('MYSLI_DATPATH', $datpath);
    define('MYSLI_PKGPATH', $pkgpath);
    define('MYSLI_TMPPATH', $tmppath);

    include(rtrim(__DIR__, '\\/') . '/common.php');

    // BOOT
    $boot_core_path = $datpath.'/boot/core.json';
    $boot_pkgs_path = $datpath.'/boot/packages.json';

    if (!file_exists($boot_core_path)) {
        throw new \Exception("File not found: `{$boot_core_path}`", 5);
    }
    if (!file_exists($boot_pkgs_path)) {
        throw new \Exception("File not found: `{$boot_pkgs_path}`", 6);
    }
    $boot_core = json_decode(file_get_contents($boot_core_path), true);
    $boot_pkgs = json_decode(file_get_contents($boot_pkgs_path), true);

    define('MYSLI_CORE_PKG',     $boot_core['core']['package']);
    define('MYSLI_CORE_PKG_REL', $boot_pkgs[MYSLI_CORE_PKG]);

    // Autoloader
    $autoloader = $boot_core['autoloader'];
    if (!isset($boot_pkgs[$autoloader['package']])) {
        throw new \Exception(
            "Autoloader package not enabled: `{$autoloader['package']}`", 7);
    }
    $autoloader['rpackage'] = $boot_pkgs[$autoloader['package']];
    $autoloader['is_phar'] = strpos($autoloader['rpackage'], '.');
    $autoloader['path']    = $pkgpath.'/'.$autoloader['rpackage'].'/src/'.
                             $boot_core['autoloader']['file'].'.php';
    $autoloader['path'] = $autoloader['is_phar'] ? 'phar://' : $autoloader['path'];
    if (!file_exists($autoloader['path'])) {
        throw new \Exception("File not found: `{$autoloader['path']}`", 8);
    } else {
        include $autoloader['path'];
    }
    $autoloader['class'] = str_replace('.', '\\', $boot_core['autoloader']['package']);
    $autoloader['class'] = $autoloader['class'].'\\'. $boot_core['autoloader']['file'];
    if (!class_exists($autoloader['class'], false)) {
        throw new \Exception("Autoloader class not found: `{$autoloader['class']}`");
    } else {
        class_alias($autoloader['class'], 'core\\autoloader');
        \core\autoloader::__init($boot_pkgs);
        spl_autoload_register(['core\\autoloader', $boot_core['autoloader']['call']]);
    }
}
