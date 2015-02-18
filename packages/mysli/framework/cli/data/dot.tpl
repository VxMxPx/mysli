#!/usr/bin/env php
<?php

// Set the timezone to UTC temporarily.
date_default_timezone_set('UTC');

// How the errors supposed to be reported?
error_reporting(E_ALL);
ini_set('display_errors', true);

// At least PHP version 5.4 is needed.
if (!function_exists('version_compare'))
    trigger_error('Function `version_compare` was not found.', E_USER_ERROR);

if (!(version_compare(PHP_VERSION, '5.4.0') >= 0))
    trigger_error(
        'PHP needs to be at least version 5.4.0 Your version: `'.PHP_VERSION.'`',
        E_USER_ERROR
    );

// Set some basic paths, which will be passed to the core.
// pkgpath - Packages path.
// datpath - Data(base) path, where most of the application specific files will
//           be stored. This path shouldn't be accessible through URL!
$datpath = realpath(__DIR__);
$pkgpath = realpath($datpath . '{{PKGPATH}}');

// Get the core.
$boot_file = "{$datpath}/boot/r.json";

if (!file_exists($boot_file))
    trigger_error("File not found: `{$boot_file}`", E_USER_ERROR);
else
    $boot_r = json_decode(file_get_contents($boot_file), true);

$core_name = $boot_r['boot']['core'];
if (!isset($boot_r['pkg'][$core_name]))
    throw new \Exception("Core package not found: `{$core_name}`");

$core_release = $boot_r['pkg'][$core_name]['release'];
$is_phar = strpos($core_release, '.');
$core_path = ($is_phar?'phar://':'')."{$pkgpath}/{$core_release}".
             ($is_phar?'.phar':'').'/src/__init.php';

if (!file_exists($core_path))
    throw new \Exception("Core `__init` file not found: `{$core_path}`");
else
    include $core_path;

call_user_func_array(
    str_replace('.', '\\', $core_name) . '\\__init',
    [$datpath, $pkgpath]
);

// Finally run the cli
\mysli\framework\cli\cli::run($_SERVER['argv']);
