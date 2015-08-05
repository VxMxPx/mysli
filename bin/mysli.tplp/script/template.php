<?php

namespace mysli\tplp\root\script; class template
{

    const __use = '
        .{ parser, tplp }
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
        $prog = new prog('Mysli Template Tplp', '', 'mysli.tplp.template');
        $prog
        ->create_parameter('PACKAGE', [
            'required' => true,
            'help'     => 'Package\'s templates to be parsed, in format: '.
                        '`vendor.package`. '.
                        'Alternatively relative path to the templates root '.
                        'can be used. Use `./` for current directory.'
        ])
        ->create_parameter('--static/-s', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'This will produce complete template with all includes '.
                    'merged into final template file. This will provide a bit of '.
                    'performance gain, but none of the includes will be replaceable.'
        ])
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch directory and re-parse when changes occurs.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $static, $watch) = $prog->get_values('package', '-s', '-w');

        // Package to path...
        $path = static::resolve_path($package);

        return static::parse($path, $static, $watch);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Parse templates in particular path, and watch for change.
     * --
     * @param string  $path
     * @param boolean $static
     * @param boolean $path
     * --
     * @return boolean
     */
    protected static function parse($path, $static, $watch)
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

        $parser = new parser($path);

        // Wait for changes
        return fs\file::observe($path, function ($changes)
            use ($parser, $static, $path, $watch)
        {
            // Watch only for specific changes
            foreach ($changes as $file => $change)
            {
                // Relative version of file for nice output and parsing
                $rfile = substr($file, strlen($path)+1);

                // Dist filename and path
                $rpfile = substr($rfile, 0, -9).($static?'.php':'.tpl.php'); // Cut .tpl.html
                $dspath = "{$path}/~dist/{$rpfile}";

                // Print action and file...
                ui::info(ucfirst($change['action']), $rfile);

                // Finally process file...
                if (substr(basename($file), 0, 1) === '_' && $static)
                {
                    // Reload all files, layout was changed... but only if we're
                    // watching, if not, just skip...
                    if ($watch === true)
                    {
                        static::parse($path, false);
                        break;
                    }
                    else
                        continue;
                }
                else
                {
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
                        $contents = $parser->file($rfile);

                        if ($static)
                            $contents = $parser->extend($contents);

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
            }

            // Creak, e.g run only once...
            if (!$watch)
                return true;

        }, "*.tpl.html", true, 2, true);
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
