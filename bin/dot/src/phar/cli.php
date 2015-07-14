<?php

namespace dot\phar\cli;

use \dot\ui as ui;

/*
At least this version is needed to proceed.
 */
const NEED_VERSION = '5.6.0';

const SAFE_MODE_MESSAGES = "
<title>Dot Utility for Mysli Platform</title>

If you'd like to mange an installed application, please execute dot from root
director of that application.

Usage: dot <command> [options...]

You can always use dot <command> -h to get help for a specific command.

List of available commands:

<ul>{list}</ul>
";

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
Get basepath of dot package. This path depends on package's format, which can be
either source or .phar
 */
$basepath = __FILE__;
if (basename($basepath) !== 'cli.php')
{
    if (basename($basepath) !== 'dot')
        trigger_error(
            "Invalid `dot` filename/format. Must be either source, or phar.",
            E_USER_ERROR
        );
    $is_phar  = true;
    $basepath = 'phar://'.$basepath;
}
else
{
    $is_phar  = false;
    $basepath = realpath(dirname($basepath).'/../../');
}

/*
Bin path is parent of this package's path. However, ./dot package might be in
actual /bin directory rather than application's bin folder.
 */
$binpath = $binself = dirname($basepath);
if ($is_phar)
{
    // Get working directory
    $workdir = getcwd();

    if ($workdir !== $binpath &&
        mb_substr($workdir, 0, mb_strlen($binpath)) !== $binpath) // if run ./me
    {
        // We have different path, so we'll use $working dir as binpath
        $binpath = $workdir;
    }
}

/*
Application path, is directory where `loc` will be located, if the system is
installed, the directory to which system should be installed.
 */
$apppath = dirname($binpath);

/*
mysli.loc.php file contains paths,
the file is present only if system is installed.
 */
$dotloc = $apppath.'/mysli.loc.php';

/*
If dot file exists, then system is installed, otherwise not, and this can only
run in safe-mode.
 */
if (!file_exists($dotloc))
{
    // Can run only internal `dot` tools at this point.
    $safe_mode = true;
    $available = [];

    foreach (scandir($basepath.'/src/cli') as $script)
    {
        if (substr($script, 0, 1) !== '.')
        {
            $script = substr($script, 0, -4);
            $available[$script] = "dot.{$script}";
        }
    }
}
else
{
    $safe_mode = false;

    // Read loc file
    include $dotloc;

    // There should be couple of constants available now, with relative paths.
    // From those we can load the toolkit and boot the system.

    // First check if all paths are defined.
    if (!defined('MYSLI_LOC_BINPATH'))
        trigger_error(
            "Binary path not defined! `MYSLI_LOC_BINPATH`. ".
            "Check `dot.loc.php` file.",
            E_USER_ERROR
        );

    if (!defined('MYSLI_LOC_PUBPATH'))
        trigger_error(
            "Public path not defined! `MYSLI_LOC_PUBPATH`. ".
            "Check `dot.loc.php` file.",
            E_USER_ERROR
        );

    /*
    Set public path.
     */
    $pubpath = realpath($apppath.'/'.MYSLI_LOC_PUBPATH);

    if (!$pubpath)
        trigger_error(
            "Public path not found in: `{$apppath}` looking for: `".
            MYSLI_LOC_PUBPATH."`.",
            E_USER_ERROR
        );
}

/*
Arguments and command
 */
$args = $_SERVER['argv'];
$command = isset($args[1]) ? $args[1] : false;

/*
Special procedure if this is running in safe (non-installed system).
 */
if ($safe_mode)
{
    // Before we can proceed, we need to include base cli classes.
    foreach (scandir("{$basepath}/src/") as $cli_class)
    {
        if (substr($cli_class, -4) !== '.php')
            continue;

        include "{$basepath}/src/{$cli_class}";
    }

    // If there's no command, or command is -h or --help, display help
    if (!$command || $command === '-h' || $command === '--help')
    {
        ui::t(SAFE_MODE_MESSAGES, ['list' => array_keys($available)]);
        exit(0);
    }

    /*
    Execute command if exists.
     */

    // Check if script exists.
    if (!isset($available[$command]))
    {
        ui::warn("Invalid command! Use `-h` to see list available commands");
        exit(1);
    }

    // Include script
    include "{$basepath}/src/cli/{$command}.php";

    // Prepare arguments
    $args = array_slice($args, 2);

    // Apppath needs to be set!
    if (!in_array('--apppath', $args))
    {
        array_unshift($args, '--apppath', $apppath);
    }

    // Execute
    $r = call_user_func(
        ['dot\\cli\\'.$command, '__run'],
        $args
    );

    exit($r ? 0 : 1);
}
else
{
    // Toolkit's CLI should handle it!

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

    // __init toolkit
    call_user_func_array($tk_call, [$apppath, $binpath, $pubpath]);

    // Run toolkit `cli`
    call_user_func("{$tk_class}::cli", array_slice($args, 1));
}
