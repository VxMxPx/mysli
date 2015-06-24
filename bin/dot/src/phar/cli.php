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
dot.loc.php file contains paths, the file is present only if system is installed.
 */
$dotloc = $apppath.'/dot.loc.php';

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
            $available[] = substr($script, 0, -4);
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
    if (!defined('MYSLI_DOT_LOC_BINPATH'))
        trigger_error(
            "Binary path not defined! `MYSLI_DOT_LOC_BINPATH`. ".
            "Check `dot.loc.php` file.",
            E_USER_ERROR
        );
    if (!defined('MYSLI_DOT_LOC_PUBPATH'))
        trigger_error(
            "Public path not defined! `MYSLI_DOT_LOC_PUBPATH`. ".
            "Check `dot.loc.php` file.",
            E_USER_ERROR
        );
}

if ($safe_mode)
{
    $argv = $_SERVER['argv'];
    $comm = isset($argv[1]) ? $argv[1] : false;

    // Before we can proceed, we need to include base cli classes.
    foreach (scandir("{$basepath}/src/") as $cli_class)
    {
        if (substr($cli_class, -4) !== '.php')
            continue;

        include "{$basepath}/src/{$cli_class}";
    }

    // If there's no command, or command is -h or --help, display help
    if (!$comm || $comm === '-h' || $comm === '--help')
    {
        ui::t(SAFE_MODE_MESSAGES, ['list' => $available]);
        exit(0);
    }

    // Check if script exists.
    if (!in_array($comm, $available))
    {
        ui::warn("Invalid command! Use `-h` to see list available commands");
        exit(1);
    }

    // Execute script.
    include "{$basepath}/src/cli/{$comm}.php";

    $r = call_user_func(
        ['dot\cli\\'.$comm, '__run'],
        $apppath, array_slice($argv, 2)
    );

    if ($r)
    {
        exit(0);
    }
    else
    {
        exit(1);
    }
}
else
{
    // Toolkit's CLI should handle it!
}
