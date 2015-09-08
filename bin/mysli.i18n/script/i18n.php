<?php

namespace mysli\i18n\root\script; class i18n
{

    const __use = '
        .{ parser, i18n }
        mysli.toolkit.cli.{ prog, param, ui, output, util }
        mysli.toolkit.{ pkg, fs.fs -> fs, fs.file, fs.dir }
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
        $prog = new prog('Mysli I18n', 'mysli.i18n.i18n');

        $prog->set_help(true);
        $prog->set_version('mysli.i18n', true);

        $prog
        ->create_parameter('PACKAGE', [
            'required' => true,
            'help'     => 'Package\'s language to be parsed, in format: '.
                        '`vendor.package`. '.
                        'Alternatively relative path to the templates root '.
                        'can be used. Use `./` for current directory.'
        ])
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch directory and re-parse when changes occurs.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $watch) = $prog->get_values('package', '-w');

        // Package to path...
        $path = static::resolve_path($package);

        return static::parse($path, $watch);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Parse lnaguage in particular path, and watch for change.
     * --
     * @param string  $path
     * @param boolean $path
     * --
     * @return boolean
     */
    protected static function parse($path, $watch)
    {
        // Check if package // Dir exists...
        if (!$path || !fs\dir::exists($path))
        {
            ui::error("Invalid path: `{$path}`.");
            return false;
        }

        // Create ~dist folder if not there...
        if (!fs\dir::exists("{$path}/~dist"))
            fs\dir::create("{$path}/~dist");

        // Wait for changes
        return fs\file::observe($path, function ($changes) use ($path, $watch)
        {
            // Watch only for specific changes
            foreach ($changes as $file => $change)
            {
                // Relative version of file for nice output and parsing
                $rfile = substr($file, strlen($path)+1);

                // Dist filename and path
                $rpfile = substr($rfile, 0, -4).'.json'; // Cut .lng
                $dspath = "{$path}/~dist/{$rpfile}";

                // Print action and file...
                ui::info(ucfirst($change['action']), $rfile);

                // Finally process file...

                if ($change['action'] == 'removed' || isset($change['to']))
                {
                    if (fs\file::exists($dspath))
                    {
                        if (fs\file::remove($dspath))
                            ui::success('Removed', $rpfile);
                        else
                            ui::success('Failed removing', $rpfile);
                    }
                    continue;
                }

                try
                {
                    // Process
                    $language = substr(basename($file), 0, -4);
                    $contents = parser::process($lng, $language);

                    fs\file::create_recursive($dspath, true);
                    if (fs\file::write($dspath, $contents))
                        ui::success('Saved', $rpfile);
                    else
                        ui::error('Failed saving', $rpfile);
                }
                catch (\Exception $e)
                {
                    ui::error($e->getMessage());
                    continue;
                }
            }

            // Creak, e.g run only once...
            if (!$watch)
                return true;

        }, "*.lng", true, 2, true);
    }

    /**
     * Get full absolute path from package or relative path.
     * --
     * @param string $path
     * --
     * @return string
     */
    protected static function resolve_path($path)
    {
        // Package
        if (preg_match('/^[a-z0-9\.]$/', $path))
        {
            $path = pkg::get_path($path);
        }
        // Or relative path
        else
        {
            $path = realpath(getcwd()."/{$path}");
        }

        return rtrim($path, '\\/');
    }
}
