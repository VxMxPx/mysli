<?php

namespace mysli\toolkit\cli;

use dot\ui;
use dot\param;

/**
 * Installer will fetch toolkit from remote location (if needed),
 * run toolkit setup, and created all necessary files and folders.
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
    static function __run($apppath, array $arguments)
    {
        /*
        Gather arguments.
         */
        $params = new param('Mysli Platform Installer', $arguments);
        $params->command = 'install';
        $params->description =
            "Please execute installer in the root directory of your application. ".
            "The installer will create `bin/` folder (if it doesn't already exists) ".
            "and acquire Mysli Toolkit automatically.";
        $params->description_long =
            "NOTE: paths MUST be relative to the (current) application path.";

        $params->add('--binpath', [
            'type' => 'str',
            'required' => false,
            'default' => './bin',
            'help' => 'Binaries path (where all .phar packages are located).',
            'modify' => 'dot\cli\install::fix_dir'
        ]);
        $params->add('--pubpath', [
            'type' => 'str',
            'required' => false,
            'default' => './public',
            'help' => 'Publicly accessible directory.',
            'validate' => 'dot\cli\install::fix_dir'
        ]);

        $params->parse();

        if (!$params->is_valid())
        {
            ui::line($params->messages());
            return false;
        }

        $values = $params->values();

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
                'FAILED',
                "Application directory must be writable `{$apppath}`."
            );
            return false;
        }

        /*
        Resolve and create paths.
         */
        if (!($binpath = self::do_dir('binpath', $apppath, $values['binpath'])))
            return false;
        if (!($pubpath = self::do_dir('pubpath', $apppath, $values['pubpath'])))
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
                    'ERROR',
                    "toolkit ({$toolkit}) not found in: `{$binpath}`."
                );
                return false;
            }
        }
        ui::success('FOUND', "`{$toolkit}`");

        /*
        Run toolkit setup
         */

        /*
        Write dot.loc.php file
         */
        $loc = str_replace(
            ['{{TIMESTAMP}}', '{{BINPATH}}', '{{PUBPATH}}'],
            [gmdate('c'), $values['binpath'], $values['pubpath']],
            self::$loc_template
        );

        if (!file_put_contents("{$apppath}/dot.loc.php", $loc."\n"))
        {
            ui::error(
                'FAILED',
                "Couldn't write `loc` file to: `{$apppath}/dot.loc.php`."
            );
            return false;
        }
        else
        {
            ui::success('OK', "Wrote `loc` file to: `{$apppath}/dot.loc.php`.");
        }
    }

    /**
     * Append ./ for relative paths. Used when saving loc file.
     * --
     * @param  string $value
     * --
     * @return string
     */
    static function fix_dir($value)
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

    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Attempt to create directory if not there already.
     * --
     * @param  string $name
     * @param  string $apppath
     * @param  string $realpath
     * --
     * @return string
     */
    protected static function do_dir($name, $apppath, $realpath)
    {
        $path = self::resolve_relative($apppath, $realpath);

        if ($path[1])
        {
            if (!@is_writable($path[0]))
            {
                ui::error(
                    'FAILED',
                    "Couldn't create `{$name}`: `{$path[1]}`, ".
                    "the directory is not writable: `{$path[0]}`."
                );
                return null;
            }

            if (!preg_match('/^[a-z0-9\/_.\-]+$/i', $path[1]))
            {
                ui::error(
                    'FAILED',
                    "Couldn't create `{$name}`: `{$path[1]}`, ".
                    "please limit your directory name to: alphanumeric with ".
                    "spaces and `._-` characters."
                );
                return null;
            }

            $path = implode('', $path);

            if (mkdir($path, 0777, true))
            {
                ui::success(
                    'OK',
                    "The `{$name}` was created: `{$path}`"
                );
            }
            else
            {
                ui::error(
                    'FAILED',
                    "Couldn't create `{$name}`: `{$path}`"
                );
                return null;
            }
        }
        else
        {
            $path = $path[0];
            ui::info('FOUND', "The `{$name}` exists: `{$path}`");
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
    protected static function resolve_relative($relative_to, $path)
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
