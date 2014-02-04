<?php
function enable_helper($librarian, $library, callable $errout)
{
    $setup = $librarian->construct_setup($library);

    if (method_exists($setup, 'before_enable') && !$setup->before_enable()) {
        $errout('Setup failed for: ' . $library);
        return false;
    }

    if (!$librarian->enable($library)) {
        $errout('Failed to enable: ' . $library);
        return false;
    }

    if (method_exists($setup, 'after_enable')) {
        $setup->after_enable();
    }

    return true;
}
function lib_enable($librarian, $library, callable $errout)
{
    if ($librarian->is_enabled($library)) {
        $errout("Library is already enabled: `{$library}`.");
        return false;
    }

    if (!$librarian->resolve($library, 'disabled')) {
        $errout("Library not found: `{$library}`.");
        return false;
    }

    $dependencies = $librarian->get_dependencies($library, true);
    if (!empty($dependencies['missing'])) {
        $errout('Cannot enable, following libraries are missing: ' .
            print_r($dependencies['missing'], true));
        return false;
    }

    if (count($dependencies['disabled'])) {
        foreach ($dependencies['disabled'] as $dependency => $version) {
            if (!enable_helper($librarian, $dependency, $errout)) {
                return false;
            }
        }
    }

    return enable_helper($librarian, $library, $errout);
}
function enable_librarian($librarian, $libpath, callable $errout)
{
    $setup_file = dst($libpath, $librarian, 'setup.php');
    $setup_class = lib_to_ns($librarian) . '\\Setup';
    if (!file_exists($setup_file)) {
        $errout('Cannot find the librarian setup file in: ' . $setup_file);
        return false;
    }
    include($setup_file);
    $setup = new $setup_class();
    if (method_exists($setup, 'before_enable')) {
        if (!$setup->before_enable()) {
            $errout('Cannot setup librarian: `before_enable` failed.');
            return false;
        }
    }
    if (method_exists($setup, 'after_enable'))  {
        if (!$setup->after_enable()) {
            $errout('Cannot setup librarian: `after_enable` failed.');
            return false;
        }
    }
    $lib_file = dst($libpath, get_lib_index($librarian));
    $lib_class = lib_to_ns($librarian);
    if (!file_exists($lib_file)) {
        $errout('Cannot find librarian class file in: ' . $lib_file);
        return false;
    }
    if (!class_exists($lib_class, false)) {
        include $lib_file;
    }
    $librarian = new $lib_class();
    return $librarian;
}
function enable_core($corelib, $libpath, $datpath, callable $errout)
{
    $setup_file = dst($libpath, $corelib, 'setup.php');
    $setup_class = lib_to_ns($corelib) . '\\Setup';
    if (!file_exists($setup_file)) {
        $errout('Cannot find core setup file in: ' . $setup_file);
        return false;
    }
    include($setup_file);
    $setup = new $setup_class([
        'libpath' => $libpath,
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
    $lib_file = dst($libpath, get_lib_index($corelib));
    $lib_class = lib_to_ns($corelib);
    if (!file_exists($lib_file)) {
        $errout('Cannot find core file in: ' . $lib_file);
        return false;
    }
    include $lib_file;
    $core = new $lib_class($datpath, $libpath);
    return $core;
}
// Get library index, for example from: vendor/lib => vendor/lib/lib.php
function get_lib_index($library)
{
    $library_segments = explode('/', $library);
    $library_last = array_pop($library_segments);
    return dst($library, $library_last . '.php');
}
// Convert library: `vendor/lib` to class, namespace: Vendor\\Lib
function lib_to_ns($library)
{
    if (strpos($library, '/') === false) {
        return $library;
    }

    $class = to_camelcase($library);
    $class = str_replace('/', '\\', $class);

    return $class;
}
// Convert strign to camel case
function to_camelcase($string, $uc_first=true)
{
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
function resolve_path($path, $relative_to)
{
    // We're dealing with absolute path
    if (substr($path, 1, 1) !== ':' && substr($path, 0, 1) !== '/') {
        $path = $relative_to . ltrim($path, '\\/');
    }

    $existing  = '/';
    $segments  = explode(DIRECTORY_SEPARATOR, $path);
    foreach ($segments as $key => &$segment) {
        if (!is_dir(realpath($existing . $segment))) {
            break;
        }
        $existing = realpath($existing . $segment) . DIRECTORY_SEPARATOR;
        unset($segments[$key]);
    }
    return [$existing, implode('/', $segments)];
}

// Correct path
function dst()
{
    $path = func_get_args();
    $path = implode(DIRECTORY_SEPARATOR, $path);

    if ($path) {
        return preg_replace('/[\/\\\\]+/', DIRECTORY_SEPARATOR, $path);
    }
    else {
        return null;
    }
}
