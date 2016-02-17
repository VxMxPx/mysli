<?php

namespace mysli\i18n\root\script; class i18n
{

    const __use = '
        .{ parser, i18n -> lib.i18n }
        mysli.toolkit.{ json, pkg }
        mysli.toolkit.fs.{ fs, file, dir, observer }
        mysli.toolkit.cli.{ prog, param, ui, output, util }
    ';

    /**
     * Run testing utility.
     * --
     * @param array $args
     * --
     * @return boolean
     */
    static function __run(array $args)
    {
        /*
        Set params.
         */
        $prog = new prog('Mysli I18n', __CLASS__);

        $prog->set_help(true);
        $prog->set_version('mysli.i18n', true);

        $prog
        ->create_parameter('PACKAGE', [
            'required' => true,
            'help'     => 'Package\'s language to be parsed, in format: '.
                        '`vendor.package`. '.
                        'Alternatively relative path to the templates root '.
                        'can be used. Use `./` for current directory. '.
                        'Alternatively if `-f` is used, absolute path to a '.
                        'specific file to be processed.'
        ])
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch directory and re-parse when changes occurs.'
        ])
        ->create_parameter('--file/-f', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Specific file to be processed rather than package.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $watch, $file) = $prog->get_values('package', '-w', '-f');

        // Package to path...
        if ($file)
        {
            $file = file::name($package);
            $package = dirname($package);
        } else $file = null;

        $path = static::resolve_path($package, ($file?true:false));

        return static::parse($path, $file, $watch);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Parse lnaguage in particular path, and watch for change.
     * --
     * @param string  $path
     * @param string  $file Specific file to be processed.
     * @param boolean $path
     * --
     * @return boolean
     */
    protected static function parse($path, $file, $watch)
    {
        // Check if package // Dir exists...
        if (!$path || !dir::exists($path))
        {
            ui::error('ERROR', "Invalid path: `{$path}`.");
            return false;
        }

        // Create dist~ folder if not there...
        if (!dir::exists("{$path}/dist~"))
            dir::create("{$path}/dist~");

        // Setup observer
        $observer = new observer($path);
        $observer->set_filter($file?$file:'*.lng');
        $observer->set_interval(2);

        // Wait for changes
        return $observer->observe(function ($changes) use ($path, $watch)
        {
            // Watch only for specific changes
            foreach ($changes as $file => $change)
            {
                // Relative version of file for nice output and parsing
                $rfile = substr($file, strlen($path)+1);

                // Dist filename and path
                $rpfile = substr($rfile, 0, -4).'.json'; // Cut .lng
                $dspath = "{$path}/dist~/{$rpfile}";

                // Print action and file...
                ui::info(strtoupper($change['action']), $rfile);

                // Finally process file...

                if ($change['action'] == 'removed' || isset($change['to']))
                {
                    if (file::exists($dspath))
                    {
                        if (file::remove($dspath))
                            ui::success('REMOVED', $rpfile, 1);
                        else
                            ui::error('REMOVE FAILED', $rpfile, 1);
                    }
                    continue;
                }

                try
                {
                    // Process
                    $language = substr(basename($file), 0, -4);
                    $contents = parser::process(file::read($file), $language);

                    file::create_recursive($dspath, true);
                    if (json::encode_file($dspath, $contents))
                        ui::success('SAVED', $rpfile, 1);
                    else
                        ui::error('SAVE FAILED', $rpfile, 1);
                }
                catch (\Exception $e)
                {
                    ui::error('ERROR', $e->getMessage(), 1);
                    continue;
                }
            }

            // Creak, e.g run only once...
            if (!$watch)
            {
                return true;
            }
        });
    }

    /**
     * Get full absolute path from package or relative path.
     * --
     * @param string  $path
     * @param boolean $absolute
     * --
     * @return string
     */
    protected static function resolve_path($path, $absolute)
    {
        // Package
        if (preg_match('/^[a-z0-9\.]+$/', $path))
        {
            $path = lib\i18n::get_path($path);
        }
        else if ($absolute)
        {
            $path = realpath($path);
        }
        // Or relative path
        else
        {
            $path = realpath(getcwd()."/{$path}");
        }

        return rtrim($path, '\\/');
    }
}
