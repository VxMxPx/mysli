<?php

namespace mysli\framework\pkgm\setup;

function enable() {

    $selfrelease = __FILE__;
    if (substr($selfrelease, -5) === '.phar') {
        $selfrelease = basename($selfrelease);
    } else {
        $selfrelease = 'mysli/framework/pkgm';
    }

    \core\autoloader::__modify_packages_list('mysli.framework.pkgm', $selfrelease);

    if (!file_exists(MYSLI_DATPATH.'/mysli'))
        mkdir(MYSLI_DATPATH.'/mysli');
    if (!file_exists(MYSLI_DATPATH.'/mysli/framework'))
        mkdir(MYSLI_DATPATH.'/mysli/framework');
    if (!file_exists(MYSLI_DATPATH.'/mysli/framework/pkgm'))
        mkdir(MYSLI_DATPATH.'/mysli/framework/pkgm');
    file_put_contents(
        MYSLI_DATPATH.'/mysli/framework/pkgm/r.json', json_encode([])
    );

    __use(__namespace__, './pkgm');

    $pkg_fs        = __discover_package(
        'mysli.framework.fs', MYSLI_PKGPATH);
    $pkg_json      = __discover_package(
        'mysli.framework.json', MYSLI_PKGPATH);
    $pkg_ym        = __discover_package(
        'mysli.framework.ym', MYSLI_PKGPATH);
    $pkg_exception = __discover_package(
        'mysli.framework.exception', MYSLI_PKGPATH);
    $pkg_type      = __discover_package(
        'mysli.framework.type', MYSLI_PKGPATH);

    \core\autoloader::__modify_packages_list(
        'mysli.framework.fs', $pkg_fs);
    \core\autoloader::__modify_packages_list(
        'mysli.framework.json', $pkg_json);
    \core\autoloader::__modify_packages_list(
        'mysli.framework.ym', $pkg_ym);
    \core\autoloader::__modify_packages_list(
        'mysli.framework.exception', $pkg_exception);
    \core\autoloader::__modify_packages_list(
        'mysli.framework.type', $pkg_type);

    pkgm::enable(MYSLI_CORE_PKG_REL, 'installer');
    pkgm::enable($pkg_exception, 'installer');
    pkgm::enable($pkg_type, 'installer');
    pkgm::enable($pkg_fs, 'installer');
    pkgm::enable($pkg_json, 'installer');
    pkgm::enable($pkg_ym, 'installer');
    pkgm::enable($selfrelease, 'installer');

    // Came so far?
    return true;
}
function disable() {
    __use(__namespace__, 'mysli.framework.fs/fs,dir');
    return dir::remove(fs::datpath('mysli/framework/pkgm'));
}

/**
 * Discover closest version of package.
 * @param  string $name
 * @param  string $pkgpath
 * @return string full package's name or null
 */
function __discover_package($name, $pkgpath) {
    $regex = '/^'.preg_quote($name).'-r.*?\\.phar$/';
    foreach (scandir($pkgpath) as $file) {
        if (preg_match($regex, $file)) {
            return $file;
        }
    }
    // Perhaps we have source?
    $name = str_replace('.', '/', $name);
    if (file_exists("{$pkgpath}/{$name}/mysli.pkg.ym")) {
        return $name;
    }
}
