<?php

namespace mysli\tplp\root\script; class template
{
    const __use = '
        .{ parser, tplp }
        mysli.toolkit.cli.{ prog, param, ui, output, util, input }
        mysli.toolkit.{ pkg, fs.fs -> fs, fs.file, fs.dir, fs.observer, type.arr -> arr }
    ';

    /**
     * Run Template CLI.
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
        $prog = new prog('Mysli Template Tplp', __CLASS__);

        $prog->set_help(true);
        $prog->set_version('mysli.tplp', true);

        $prog
        ->create_parameter('PACKAGE', [
            'help'     => 'Package\'s templates to be parsed, in format: '.
                        '`vendor.package`. '.
                        'Alternatively relative path to the templates root '.
                        'can be used. Use `./` for current directory.'
        ])
        ->create_parameter('--interactive/-i', [
            // 'exclude' => ['-s', '-w'],
            'type'    => 'boolean',
            'def'     => false,
            'help'    => 'Run template parser in an interactive mode.'
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

        list($package, $static, $watch, $interactive) =
            $prog->get_values('package', '-s', '-w', '-i');

        if (!$package && !$interactive)
        {
            ui::warning('Package name is required.');
            return false;
        }
        else if ($interactive)
        {
            return static::interactive();
        }
        else
        {
            // Package to path...
            $path = static::resolve_path($package);
            return static::parse($path, $static, $watch);
        }

    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Run in a interactive mode.
     * --
     * @return boolean
     */
    protected static function interactive()
    {
        ui::line('Hi! This is an interative console for the Mysli Template.');
        ui::line('Double empty line will process and print the result.');
        ui::line('Type `!exit` to quit.');

        $buffer = [];
        $parser = new parser(fs::tmppath('tplp'));

        // Now wait for the user input
        return input::line('>> ',
            function ($stdin) use ($parser, &$buffer)
            {
                if (in_array(strtolower($stdin), ['!exit']))
                    return true;

                if ($stdin === '' && (arr::last($buffer) === ''))
                {
                    $template = trim(implode("\n", $buffer));
                    $buffer = [];

                    try
                    {
                        ui::line( $parser->template($template) );
                    }
                    catch (\Exception $e)
                    {
                        ui::error( $e->getMessage() );
                    }

                    return;
                }

                $buffer[] = $stdin;
            }
        );
    }

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

        // Create dist~ folder if not there...
        if (!fs\dir::exists("{$path}/dist~"))
            fs\dir::create("{$path}/dist~");

        $parser = new parser($path);

        // Setup observer
        $observer = new fs\observer($path);
        $observer->set_filter('*.tpl.html');
        $observer->set_interval(2);

        return $observer->observe(
        function ($changes) use ($parser, $static, $path, $watch) {

            // Watch only for specific changes
            foreach ($changes as $file => $change)
            {
                // Relative version of file for nice output and parsing
                $rfile = substr($file, strlen($path)+1);

                // Dist filename and path
                $rpfile = substr($rfile, 0, -9).($static?'.php':'.tpl.php'); // Cut .tpl.html
                $dspath = "{$path}/dist~/{$rpfile}";

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

            ui::nl();

            // Drop cache
            fs\file::remove(
                fs\file::find(fs::tmppath('tplp'), '*.php')
            );

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
     * @param string $path
     * --
     * @return string
     */
    protected static function resolve_path($path)
    {
        // Package
        if (preg_match('/^[a-z0-9\.]+$/', $path))
        {
            $path = tplp::get_path($path);
        }
        // Or relative path
        else
        {
            $path = realpath(getcwd()."/{$path}");
        }

        return rtrim($path, '\\/');
    }
}
