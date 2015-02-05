<?php

namespace mysli\installer\common;

/**
 * Execute setup for particular package.
 * @param  string   $pkg      e.g.: mysli/framework/core
 * @param  string   $pkgpath  full absolute packages path
 * @param  string   $datpath  full absolute data path
 * @param  callable $errout   function to handle errors
 * @return string             core init function name
 */
function exe_setup($pkg, $pkgpath, $datpath, callable $errout) {
    $ns = str_replace('/', '\\', $pkg);
    $setupfile = dst($pkgpath, $pkg, 'src/setup.php');

    if (file_exists($setupfile) && !function_exists($ns.'\\setup\\enable')) {
        include($setupfile);
    }
    if (function_exists($ns.'\\setup\\enable')) {
        if (!call_user_func_array($ns.'\\setup\\enable', [$pkgpath, $datpath]))
        {
            $errout("Setup failed for: `{$pkg}`");
            return false;
        }
    }
    return true;
}
/**
 * Get particular class
 * @param  string   $pkg
 * @param  string   $class
 * @param  string   $pkgpath
 * @param  callable $errout
 * @return string
 */
function pkg_class($pkg, $class, $pkgpath, callable $errout) {
    $ns = str_replace('/', '\\', $pkg);
    $classfile = dst($pkgpath, $pkg, "src/{$class}.php");

    if (!file_exists($classfile)) {
        $errout("Cannot find file `{$classfile}`.");
        return false;
    }

    if (!function_exists("{$ns}\\{$class}") &&
        !class_exists("{$ns}\\{$class}"))
    {
        include $classfile;
    }

    if (!function_exists("{$ns}\\{$class}") &&
        !class_exists("{$ns}\\{$class}"))
    {
        $errout(
            "Main file was loaded, but function not found: `{$ns}\\{$class}`");
        return false;
    } else {
        return "{$ns}\\{$class}";
    }
}
/**
 * Find packages folder, relative to path.
 * @param  string $path
 * @param  string $name
 * @return string null if path not found
 */
function discover_path($path, $name) {
    $relative = $path;
    do {
        $relative = substr($relative, 0, strrpos($relative, DIRECTORY_SEPARATOR));
        $path = $relative.DIRECTORY_SEPARATOR.$name;
        if (file_exists($path) && is_dir($path)) {
            return $path;
        }
    } while (strlen($relative) > strpos($path, DIRECTORY_SEPARATOR));
    return false;
}
/**
 * Resolve relative path (to be absolute).
 * This works even if (part of the) path doesn't exists.
 * Return array with two elements, first is the existing part, and second is
 * non existing path.
 * For example, if we have such path: /home/user/non-existing-dir/sub - result
 * will be: ['/home/user/', 'non-existing-dir/sub']
 * @param  string $path
 * @param  string $relative_to
 * @return array
 */
function resolve_path($path, $relative_to) {
    // We're dealing with absolute path
    if (substr($path, 1, 1) !== ':' && substr($path, 0, 1) !== '/') {
        $path = rtrim($relative_to, '\\/').DIRECTORY_SEPARATOR.
                ltrim($path, '\\/');
    }

    $existing = $path;
    $cut_off  = '';
    do {
        if (is_dir($existing)) break;
        if ($existing === dirname($existing)) break;
        $cut_off .= $cut_off . DIRECTORY_SEPARATOR . basename($existing);
        $existing = dirname($existing);
    } while (true);

    return [realpath($existing), $cut_off];
}
/**
 * Proper directory separator.
 * @param  ...    path segments
 * @return string
 */
function dst() {
    $path = func_get_args();
    $path = implode(DIRECTORY_SEPARATOR, $path);
    return $path
        ? preg_replace('/[\/\\\\]+/', DIRECTORY_SEPARATOR, $path)
        : null;
}
