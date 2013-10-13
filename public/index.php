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
    trigger_error('The function "version_compare" was not found.', E_USER_ERROR);
}
if (!(version_compare(PHP_VERSION, '5.4.0') >= 0)) {
    trigger_error('PHP needs to be at least version 5.4.0 Your version: ' . PHP_VERSION, E_USER_ERROR);
}

/*
 * Set some basic paths, which will be passed to the core.
 * pubpath - Public URL accessible path, where index.php is stored.
 * libpath - Libraries repository.
 * datpath - Data(base) path, where most of the application specific files will
 *           be stored. This path shouldn't be accessible through URL!
 */
$pubpath = realpath(dirname(__FILE__));
$libpath = realpath($pubpath.'/../lib');
$datpath = realpath($pubpath.'/../data');

/*
 * Init & exit the system now...
 * If you want to use another core, then do change the path here.
 */
$core_path = str_replace('/', DIRECTORY_SEPARATOR, $libpath.'/mysli/core/core.php');
if (!file_exists($core_path)) {
    trigger_error("Cannot find core file: `{$core_path}`", E_USER_ERROR);
}
include($libpath.'/mysli/core/core.php');
\Mysli\Core::init($pubpath, $libpath, $datpath);
dump(Cfg::dump());
echo \Mysli\Core::as_html();
exit(\Mysli\Core::terminate());