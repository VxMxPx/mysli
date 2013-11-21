#!/usr/bin/php
<?php

/*
 * Set the timezone to UTC temporarily.
 */
date_default_timezone_set('UTC');

/*
 * How the errors supposed to be reported?
 */
error_reporting(E_ALL);
ini_set('display_errors', true);

/*
 * We'll need at least PHP version 5.4, but if you're using only
 * non-standard components, with backward compatibility, then feel free to
 * comment this section out.
 */
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

/*
 * Set some basic paths, which will be passed to the core.
 * pubpath - Public URL accessible path, where index.php is stored.
 * libpath - Libraries repository.
 * datpath - Data(base) path, where most of the application specific files will
 *           be stored. This path shouldn't be accessible through URL!
 */
$datpath = realpath(__DIR__);
$pubpath = realpath(str_replace('/', DIRECTORY_SEPARATOR, $datpath . '{{PUBPATH}}'));
$libpath = realpath(str_replace('/', DIRECTORY_SEPARATOR, $datpath . '{{LIBPATH}}'));

/**
 * Load libraries registry to locate core!
 */
$lib_registry = realpath($datpath.'/core/libraries.json');
if (!$lib_registry || !file_exists($lib_registry)) {
    trigger_error(
        "Libraries registry not found ({$lib_registry}), cannot locate core!",
        E_USER_ERROR);
}

$libraries = file_get_contents($lib_registry);
$libraries = json_decode($libraries, true);
$core_lib  = false;
$core_class = false;

foreach ($libraries as $lib_name => $lib_meta) {
    if (preg_match('/.*?\/core/i', $lib_name)) {
        $core_lib = $lib_name;
        if (isset($lib_meta['class'])) {
            $core_class = $lib_meta['class'];
        } else {
            $core_class = explode('/', $lib_name);
            $core_class = '\\' . ucfirst($core_class[0]) .
                          '\\' . ucfirst($core_class[1]);
        }
        break;
    }
}

if (!$core_lib) {
    trigger_error("Core is not enabled, cannot proceed!", E_USER_ERROR);
}

/*
 * Init & exit the system now...
 */
$core_path = str_replace('/', DIRECTORY_SEPARATOR, $libpath.'/'.$core_lib.'/core.php');
if (!file_exists($core_path)) {
    trigger_error("Cannot find core file: `{$core_path}`", E_USER_ERROR);
}
include($libpath.'/'.$core_lib.'/core.php');
$core_class::init($pubpath, $libpath, $datpath);

// Dot execution
$dot = new \Dot(json_decode(file_get_contents(datpath('core/dot.json')), true));
if (isset($_SERVER['argv'][1])) {
    $script  = $_SERVER['argv'][1];
    if ($script === '--help') {
        $dot->list_scripts();
    }
    $command = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : false;
    if (!$dot->execute($script, $command, array_slice($_SERVER['argv'], 3))) {
        \DotUtil::warn('Cannot find the command: ' . $script);
    }
} else {
    $dot->list_scripts();
}
