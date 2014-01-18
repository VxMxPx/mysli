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
 * I suppose we'll need at least PHP version 5.4, but if you're using only
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

$ds = DIRECTORY_SEPARATOR;

/*
 * Set some basic paths, which will be passed to the core.
 * pubpath - Public URL accessible path, where index.php is stored.
 * libpath - Libraries repository.
 * datpath - Data(base) path, where most of the application specific files will
 *           be stored. This path shouldn't be accessible through URL!
 */
$pubpath = realpath(__DIR__);
$libpath = realpath(str_replace('/', $ds, $pubpath . '{{LIBPATH}}'));
$datpath = realpath(str_replace('/', $ds, $pubpath . '{{DATPATH}}'));

/**
 * Get core!
 */
$core_id_file = $datpath . $ds . 'core' . $ds . 'id.json';
if (!file_exists($core_id_file)) {
    trigger_error("File not found: `{$core_id_file}`", E_USER_ERROR);
} else {
    $core_id = json_decode(file_get_contents($core_id_file), true);
}
include($libpath . $ds . str_replace('/', $ds, $core_id['file']));
$core = new $core_id['class']($pubpath, $libpath, $datpath);
$core->init();

exit($core->event->trigger('index:done'));
