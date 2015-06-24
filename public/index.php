<?php

namespace mysli\toolkit\index;

/*
At least this version is needed to proceed.
 */
const NEED_VERSION = '5.6.0';

/*
Set timezone to UTC temporarily
 */
date_default_timezone_set('UTC');

/*
Report all errors.
 */
error_reporting(E_ALL);
/*
For now display all error too, this might be changed by toolkit later.
 */
ini_set('display_errors', true);

/*
Check if current PHP version is sufficient to proceed.
 */
if (!(version_compare(PHP_VERSION, NEED_VERSION) >= 0))
    trigger_error(
        'PHP needs to be at least version `'.NEED_VERSION.'` '.
        'Your version: `'.PHP_VERSION.'`',
        E_USER_ERROR
    );

/*
Check if there are any path instructions in this folder, if not, loc file is
located one level bellow current directory.
 */
if (file_exists(__DIR__.'/mysli.loc.php'))
{
    include __DIR__.'/mysli.loc.php';
    if (!defined('MYSLI_LOC_INDEX_APPPATH'))
        trigger_error(
            "Expected const not found: `MYSLI_LOC_INDEX_APPPATH` in ".
            "`./mysli.loc.php`",
            E_USER_ERROR
        );

    $apppath = realpath(__DIR__.MYSLI_LOC_INDEX_APPPATH);
}
else
{
    $apppath = dirname(__DIR__);
}

$mysli_loc = "{$apppath}/mysli.loc.php";
if (!file_exists($mysli_loc))
    trigger_error("File not foun: `mysli.loc.php` in APPPATH", E_USER_ERROR);

include $mysli_loc;

$binpath = realpath($apppath.'/'.MYSLI_LOC_BINPATH);
$pubpath = __DIR__;

if (!$binpath)
    trigger_error(
        "Bin path not found in: `{$apppath}` looking for: `".
        MYSLI_LOC_BINPATH."`.",
        E_USER_ERROR
    );

/*
Load toolkit now.
 */
$toolkit_conf = "{$apppath}/configuration/toolkit.php";
if (!file_exists($toolkit_conf))
    trigger_error(
        "Toolkit configuration not found in `{apppath}/configuration/toolkit.php`",
        E_USER_ERROR
    );

// Toolkit conf will define TOOLKIT_LOAD, which will hold information on how to
// initialize toolkit. This file will also allow toolkit to be replace by any
// other vendor.
include $toolkit_conf;

// TOOLKIT_LOAD is written in format:
// binary_name:::init_filename_to_load:::namespaced_method_to_call
// Example: mysl.toolkit:::toolkit.init:::mysli\toolkit\toolkit_init::__init
list($tk_bin, $tk_file, $tk_call) = explode(':::', TOOLKIT_LOAD);

// Toolkit base directory
$tk_dir = "{$binpath}/{$tk_bin}";

// If it doesn't exists, it might be phar
if (!file_exists($tk_dir))
{
    // If not phar, then something went wrong
    if (!file_exists($tk_dir.'.phar'))
        trigger_error("Toolkit not found `{$tk_dir}`.", E_USER_ERROR);
    else
        $tk_dir = "phar://{$tk_dir}.phar";
}

// Toolkit file, which contains init class.
$tk_file = "{$tk_dir}/src/{$tk_file}.php";

if (!file_exists($tk_file))
    trigger_error(
        "Toolkit `init` file not found: `{$tk_file}`.",
        E_USER_ERROR
    );

include $tk_file;

list($tk_class, $tk_method) = explode('::', $tk_call, 2);

if (!class_exists($tk_class, false))
    trigger_error("Toolkit class not foun: `{$tk_class}`", E_USER_ERROR);

// Run toolkit, with paths.
call_user_func_array($tk_call, [$apppath, $binpath, $pubpath]);

// Done.
// EOF
