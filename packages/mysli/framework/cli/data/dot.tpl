#!/usr/bin/env php
<?php

// Set the timezone to UTC temporarily.
date_default_timezone_set('UTC');

// How the errors supposed to be reported?
error_reporting(E_ALL);
ini_set('display_errors', true);

// At least PHP version 5.4 is needed.
if (!function_exists('version_compare')) {
    trigger_error(
        'The function "version_compare" was not found.',
        E_USER_ERROR);
}
if (!(version_compare(PHP_VERSION, '5.4.0') >= 0)) {
    trigger_error(
        'PHP needs to be at least version 5.4.0 Your version: ' . PHP_VERSION,
        E_USER_ERROR);
}

// Set some basic paths, which will be passed to the core.
// pkgpath - Packages path.
// datpath - Data(base) path, where most of the application specific files will
//           be stored. This path shouldn't be accessible through URL!
$datpath = realpath(__DIR__);
$pkgpath = realpath($datpath . '{{PKGPATH}}');

// Get the core.
$core_id_file = "{$datpath}/core/id.json";
if (!file_exists($core_id_file)) {
    trigger_error("File not found: `{$core_id_file}`", E_USER_ERROR);
} else {
    $core_id = json_decode(file_get_contents($core_id_file), true);
}
include("{$pkgpath}/{$core_id['package']}/src/__init.php");
call_user_func_array(
    str_replace('/', '\\', $core_id['package']) . '\\__init',
    [$datpath, $pkgpath]);

// Finally run the cli
\mysli\framework\cli\cli::run($_SERVER['argv']);
