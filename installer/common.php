<?php

namespace mysli\installer;

function enable_helper($pkgm, $package, callable $errout) {
    $factory = $pkgm->factory($package);

    if ($factory->can_produce('setup')) {
        $setup = $pkgm->factory($package)->produce('setup');
    } else {
        $setup = false;
    }

    if ($setup && method_exists($setup, 'before_enable') && !$setup->before_enable()) {
        $errout('Setup failed for: ' . $package);
        return false;
    }

    if (!$pkgm->control($package)->enable()) {
        $errout('Failed to enable: ' . $package);
        return false;
    }

    if ($setup && method_exists($setup, 'after_enable')) {
        $setup->after_enable();
    }

    return true;
}
function pkg_enable($pkgm, $package, callable $errout) {
    if ($pkgm->registry()->is_enabled($package)) {
        $errout("Package is already enabled: `{$package}`.");
        return false;
    }

    $dependencies = $pkgm->registry()->list_dependencies($package, true);
    if (!empty($dependencies['missing'])) {
        $errout('Cannot enable, following packages are missing: ' .
            print_r($dependencies['missing'], true));
        return false;
    }

    if (count($dependencies['disabled'])) {
        foreach ($dependencies['disabled'] as $dependency => $version) {
            if (!enable_helper($pkgm, $dependency, $errout)) {
                return false;
            }
        }
    }

    return enable_helper($pkgm, $package, $errout);
}
function enable_pkgm($pkgm, $pkgpath, callable $errout) {
    $setup_file = dst($pkgpath, $pkgm, 'setup.php');
    $setup_class = pkg_to_ns($pkgm . '/setup');
    if (!file_exists($setup_file)) {
        $errout('Cannot find the `pkgm` setup file in: ' . $setup_file);
        return false;
    }
    include($setup_file);
    $setup = new $setup_class();
    if (method_exists($setup, 'before_enable')) {
        if (!$setup->before_enable()) {
            $errout('Cannot setup `pkgm`: `before_enable` failed.');
            return false;
        }
    }
    if (method_exists($setup, 'after_enable'))  {
        if (!$setup->after_enable()) {
            $errout('Cannot setup `pkgm`: `after_enable` failed.');
            return false;
        }
    }
    $pkg_file = dst($pkgpath, get_pkg_index($pkgm));
    $pkg_class = pkg_to_ns($pkgm);
    if (!file_exists($pkg_file)) {
        $errout('Cannot find `pkgm` class file in: ' . $pkg_file);
        return false;
    }
    if (!class_exists($pkg_class, false)) {
        include $pkg_file;
    }
    $pkgm = new $pkg_class();
    return $pkgm;
}
function enable_core($core_pkg, $pkgpath, $datpath, callable $errout) {
    $setup_file = dst($pkgpath, $core_pkg, 'setup.php');
    $setup_class = pkg_to_ns($core_pkg . '/setup');
    if (!file_exists($setup_file)) {
        $errout('Cannot find core setup file in: ' . $setup_file);
        return false;
    }
    include($setup_file);
    $setup = new $setup_class([
        'pkgpath' => $pkgpath,
        'datpath' => $datpath
    ]);
    if (method_exists($setup, 'before_enable')) {
        if (!$setup->before_enable()) {
            $errout('Cannot setup core: `before_enable` failed.');
            return false;
        }
    }
    if (method_exists($setup, 'after_enable'))  {
        if (!$setup->after_enable()) {
            $errout('Cannot setup core: `after_enable` failed.');
            return false;
        }
    }
    $pkg_file = dst($pkgpath, get_pkg_index($core_pkg));
    $pkg_class = pkg_to_ns($core_pkg);
    if (!file_exists($pkg_file)) {
        $errout('Cannot find core file in: ' . $pkg_file);
        return false;
    }
    include $pkg_file;
    $core = new $pkg_class($datpath, $pkgpath);
    return $core;
}
// Get package's index, for example from: vendor/pkg => vendor/pkg/pkg.php
function get_pkg_index($package) {
    $package_segments = explode('/', $package);
    $package_last = array_pop($package_segments);
    return dst($package, $package_last . '.php');
}
// Convert package: `vendor/pkg` to class, namespace: Vendor\\Pkg
function pkg_to_ns($package) {
    if (strpos($package, '/') === false) {
        return $package;
    }
    $class = to_camelcase($package);
    $class = explode('/', $class);

    if (count($class) === 2) {
        $class[] = $class[1];
    }

    return implode('\\', $class);
}
// Convert strign to camel case
function to_camelcase($string, $uc_first=true) {
    // Convert _
    if (strpos($string, '_') !== false) {
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
    }

    // Convert backslashes
    if (strpos($string, '\\') !== false) {
        $string = str_replace('\\', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '\\', $string);
    }

    // Convert slashes
    if (strpos($string, '/') !== false) {
        $string = str_replace('/', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '/', $string);
    }

    if (!$uc_first) {
        $string = lcfirst($string);
    }
    else {
        $string = ucfirst($string);
    }

    return $string;
}
// Resolve relative path (to be absolute)
// This works even if (part of the) path doesn't exists.
// Return array with two elements, first is the existing part, and second is
// non existing path.
// For example, if we have such path: /home/user/non-existing-dir/sub - result
// will be: ['/home/user/', 'non-existing-dir/sub']
function resolve_path($path, $relative_to) {
    // We're dealing with absolute path
    if (substr($path, 1, 1) !== ':' && substr($path, 0, 1) !== '/') {
        $path = rtrim($relative_to, '\\/') . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
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

// Correct path
function dst() {
    $path = func_get_args();
    $path = implode(DIRECTORY_SEPARATOR, $path);

    if ($path) {
        return preg_replace('/[\/\\\\]+/', DIRECTORY_SEPARATOR, $path);
    }
    else {
        return null;
    }
}
