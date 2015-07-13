<?php

namespace dot\cli;

use dot\ui;
use dot\prog;
use dot\param;

/**
 * Installer will fetch toolkit from remote location (if needed),
 * run toolkit setup, and created necessary basic files and folders.
 */
class install
{
    /**
     * Loc file skeleton.
     * --
     * @var string
     */
    private static $loc_template = <<<LOC
<?php

/*
This file was automatically generated when system was installed.
 */

// Loc file creation timestamp
define(
    'MYSLI_LOC_TIMESTAMP',
    '{{TIMESTAMP}}'
);

// All paths MUST be relative to the application (this file's) folder.
//
// BINPATH is binary path, where all system .phar files are stored.
define('MYSLI_LOC_BINPATH', '{{BINPATH}}');
// PUBPATH is public path, files saved in it, will be browser accessible.
define('MYSLI_LOC_PUBPATH', '{{PUBPATH}}');
// TMPPATH is temp path, where temporary files will be saved.
LOC;

    /**
     * Run installer.
     * --
     * @param  string  $apppath   the application path to which this system will
     *                            be installed.
     * @param  array   $arguments list of arguments provided to installer.
     * --
     * @return boolean
     */
    static function __run(array $arguments)
    {
        /*
        Gather arguments.
         */
        $prog = new prog(
            'Mysli Platform Installer',
            "Please execute installer in the root directory of your application. ".
            "The installer will create `bin/` folder (if it doesn't already exists) ".
            "and acquire Mysli Toolkit automatically.",
            'install',
            'NOTE: paths MUST be relative to the (current) application path.'
        );
        $papppath = new param('--apppath');
        $papppath
            ->required(true)
            ->help('Full absolute application path.');
        $pbinpath = new param('--binpath');
        $pbinpath
            ->def('./bin')
            ->help('Binaries path (where all .phar packages are located).');
        $ppubpath = new param('--pubpath');
        $ppubpath
            ->def('./public')
            ->help('Publicly accessible directory.');
        $prog->parameters($papppath, $pbinpath, $ppubpath);
        $prog->arguments($arguments);

        if (!$prog->validate())
        {
            ui::line($prog->messages());
            return false;
        }
        else if ($prog->is_help())
        {
            ui::line($prog->help());
            return true;
        }

        // Get values now
        $apppath   = $papppath->get_value();
        $r_binpath = self::fix_dir($pbinpath->get_value());
        $r_pubpath = self::fix_dir($ppubpath->get_value());

        /*
        Set base variables.
         */
        $toolkit = 'mysli.toolkit';
        $is_toolkit_phar = false;

        ui::nl();
        ui::title("Mysli Platform Installer");
        ui::nl();
        ui::line('Checking for directories...');

        if (!@is_writable($apppath))
        {
            ui::error(
                '!!', "Application directory must be writable `{$apppath}`."
            );
            return false;
        }

        /*
        Resolve and create bin and public paths.
         */
        if (!($binpath = self::do_dir('binpath', $apppath, $r_binpath)))
            return false;

        if (!($pubpath = self::do_dir('pubpath', $apppath, $r_pubpath)))
            return false;

        /*
        Check if toolkit exists in the directory.
         */
        if (file_exists("{$binpath}/{$toolkit}.phar"))
        {
            $is_toolkit_phar = true;
        }
        else
        {
            if (!file_exists("{$binpath}/{$toolkit}"))
            {
                ui::error(
                    '!!', "Toolkit ({$toolkit}) not found in: `{$binpath}`."
                );
                return false;
            }
        }
        ui::success('OK', "`{$toolkit}`");

        /*
        Run toolkit setup
         */
        try
        {
            // Get dnyamic name, in came of `mysli.toolkit` it would be `toolkit`,
            // but in case of vendor.package, would be package.
            $tk_name = substr($toolkit, strrpos($toolkit, '.')+1);

            // Setup filename, different if phar.
            $toolkit_setup_file = $is_toolkit_phar
                ? "phar://{$binpath}/{$toolkit}.phar/src/__setup.php"
                : "{$binpath}/{$toolkit}/src/__setup.php";

            // Setup file must exists.
            if (!file_exists($toolkit_setup_file))
            {
                ui::error(
                    "!!", "Toolkit setup file not found: `{$toolkit_setup_file}`."
                );
                return false;
            }

            // Include setup file
            include $toolkit_setup_file;

            // Construct classname & namespace
            $tk_class = str_replace('.', '\\', $toolkit.".__setup");

            if (!class_exists($tk_class, false))
            {
                ui::error(
                    '!!',
                    "Toolkit setup file was loaded, ".
                    "but it doesn't contain class: `{$tk_class}`."
                );
                return false;
            }

            if (!call_user_func_array(
                [$tk_class, 'enable'], [$apppath, $binpath, $pubpath]))
            {
                ui::error(
                    '!!', "Toolkit setup failed, without explanation."
                );
                return false;
            }
            else
            {
                ui::success('OK', 'Toolkit setup successful.');
            }
        }
        catch (\Exception $e)
        {
            ui::error(
                "!!", "Toolkit setup failed, with message:\n".$e->getMessage()
            );
            return false;
        }

        /*
        Write mysli.loc.php file
         */
        $loc = str_replace(
            ['{{TIMESTAMP}}', '{{BINPATH}}', '{{PUBPATH}}'],
            [gmdate('c'), $r_binpath, $r_pubpath],
            self::$loc_template
        );

        if (!file_put_contents("{$apppath}/mysli.loc.php", $loc."\n"))
        {
            ui::error(
                '!!', "Couldn't write `loc` file to: `{$apppath}/mysli.loc.php`."
            );
            return false;
        }
        else
        {
            ui::success(
                'OK', "Wrote `loc` file to: `{$apppath}/mysli.loc.php`."
            );
        }

        /*
        Done.
         */
        ui::success('OK', 'Install is now done. System should be usable.');
        return true;
    }


    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Append ./ for relative paths. Used when saving loc file.
     * --
     * @param  string $value
     * --
     * @return string
     */
    private static function fix_dir($value)
    {
        if (substr($value, 0, 3) !== '../' && substr($value, 0, 2) !== './' &&
            substr($value, 0, 1) !== '/')
        {
            return "./{$value}";
        }
        else
        {
            return $value;
        }
    }

    /**
     * Read this package's version, without utilizing `ym` class.
     * --
     * @throws \Exception 10 Cannot find mysli.pkg.ym file.
     * @throws \Exception 20 Cannot find version key in mysli.pkg.ym file.
     * --
     * @return integer
     */
    private static function get_version()
    {
        /*
        Check if file needs to be loaded from phar.
         */
        if (substr(__FILE__, -5) === '.phar')
            $file = realpath('phar://'.__FILE__.'/mysli.pkg.ym');
        else
            $file = dirname(__DIR__).'/mysli.pkg.ym';

        if (!$file)
            throw new \Exception(
                "Couldn't find `mysli.pkg.ym` file to read version.", 10
            );

        // Get mysli.pkg contents
        $meta = file_get_contents($file);

        // Find `version: <number>` line in the file.
        if (preg_match(
            '/^[ \t]*?version[ \t]*?:[ \t]*?([0-9]+)$/ms',
            $meta,
            $matches))
        {
            $version = (int) $matches[1];
        }
        else
        {
            throw new \Exception(
                "Couldn't find `version` key in `mysli.pkg.ym`.", 20
            );
        }

        return $version;
    }

    /**
     * Attempt to create directory if not there already.
     * --
     * @param  string $name
     * @param  string $apppath
     * @param  string $realpath
     * --
     * @return string
     */
    private static function do_dir($name, $apppath, $realpath)
    {
        $path = self::resolve_relative($apppath, $realpath);

        if ($path[1])
        {
            if (!@is_writable($path[0]))
            {
                ui::error(
                    '!!',
                    "Couldn't create `{$name}`: `{$path[1]}`, ".
                    "the directory is not writable: `{$path[0]}`."
                );
                return null;
            }

            if (!preg_match('/^[a-z0-9\/_.\-]+$/i', $path[1]))
            {
                ui::error(
                    '!!',
                    "Couldn't create `{$name}`: `{$path[1]}`, ".
                    "please limit your directory name to: alphanumeric with ".
                    "spaces and `._-` characters."
                );
                return null;
            }

            $path = implode('', $path);

            if (mkdir($path, 0777, true))
            {
                ui::success('OK', "The `{$name}` was created: `{$path}`");
            }
            else
            {
                ui::error('!!', "Couldn't create `{$name}`: `{$path}`");
                return null;
            }
        }
        else
        {
            $path = $path[0];
            ui::info('OK', "The `{$name}` exists: `{$path}`");
        }

        return $path;
    }

    /**
     * Resolve relative path (to be absolute).
     * This works even if (part of the) path doesn't exists.
     * Return array with two elements, first is the existing part, and second is
     * non existing path.
     * --
     * @example
     * if we have such path: /home/user/non-existing-dir/sub
     * Result will be: ['/home/user/', 'non-existing-dir/sub']
     * --
     * @param string $relative_to
     * @param string $path
     * --
     * @return array [ string $exists, string $create ]
     */
    private static function resolve_relative($relative_to, $path)
    {
        // We're dealing with absolute path
        if (substr($path, 1, 1) !== ':' && substr($path, 0, 1) !== '/')
        {
            $path =
                rtrim($relative_to, '\\/').
                DIRECTORY_SEPARATOR.
                ltrim($path, '\\/');
        }

        $existing = $path;
        $cut_off  = '';

        do
        {
            if (is_dir($existing))
            {
                break;
            }

            if ($existing === dirname($existing))
            {
                break;
            }

            $cut_off .= $cut_off . DIRECTORY_SEPARATOR . basename($existing);
            $existing = dirname($existing);

        } while (true);

        return [realpath($existing), $cut_off];
    }
}
