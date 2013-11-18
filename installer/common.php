<?php

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
