<?php

namespace mysli\tplp\root\script; class template
{
    const __use = '
        .{ parser, extender, tplp }
        mysli.toolkit.fs.{ fs, file, dir, observer }
        mysli.toolkit.cli.{ prog, ui, input }
        mysli.toolkit.type.{ arr }
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
                        'can be used. Use `./` for current directory. '.
                        'Alternatively if `-f` is used, absolute path to a '.
                        'specific file to be processed.'
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
        ])
        ->create_parameter('--file/-f', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Specific file to be processed rather than package.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $static, $watch, $interactive, $file) =
            $prog->get_values('package', '-s', '-w', '-i', '-f');

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
            if ($file)
            {
                $file = file::name($package);
                $package = dirname($package);
            } else $file = null;

            // Package to path...
            $path = static::resolve_path($package, ($file?true:false));
            return static::parse($path, $static, $file, $watch);
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
        $parser = new parser();

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
                        ui::line( $parser->process($template) );
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
     * @param string  $file   Filename of a specific file to be parsed.
     * @param boolean $path
     * --
     * @return boolean
     */
    protected static function parse($path, $static, $file, $watch)
    {
        // Check if package // Dir exists...
        if (!$path || !dir::exists($path))
        {
            ui::error("Invalid path: `{$path}`.");
            return false;
        }

        // Create dist~ folder if not there...
        if (!dir::exists("{$path}/dist~"))
        {
            dir::create("{$path}/dist~");
        }

        $parser = new parser();
        $extender = new extender($path);

        // Setup observer
        $observer = new observer($path);
        $observer->set_filter($file?$file:'*.tpl.html');
        $observer->set_interval(2);

        return $observer->observe(
        function ($changes) use ($parser, $extender, $static, $path, $watch) {

            // Flush tmp folder
            file::remove(file::find(fs::tmppath('tplp'), '*.*'));

            // Watch only for specific changes
            foreach ($changes as $file => $change)
            {
                // Relative version of file for nice output and parsing
                $rfile = substr($file, strlen($path)+1);
                $bfile = substr($rfile, 0, -9); // Cut .tpl.html

                // Dist filename and path
                $rpfile = $bfile.'.tpl.php';
                $dspath = "{$path}/dist~/{$rpfile}";

                // Print action and file...
                ui::info(ucfirst($change['action']), $rfile);

                // If static, then reload everything on any change
                if (substr($bfile, 0, 1) === '_' && $static && $watch)
                {
                    static::parse($path, true, false);
                    break;
                }

                if ($change['action'] == 'removed' || isset($change['to']))
                {
                    if (file::exists($dspath))
                    {
                        if (file::remove($dspath))
                        {
                            ui::success('Removed', $rpfile);
                        }
                        else
                        {
                            ui::success('Failed removing', $rpfile);
                        }
                    }
                    if (file::exists($dspath.'.composed'))
                    {
                        if (file::remove($dspath.'.composed'))
                        {
                            ui::success('Removed', $rpfile);
                        }
                        else
                        {
                            ui::success('Failed removing', $rpfile);
                        }
                    }

                    continue;
                }

                try
                {
                    // Process
                    if ($static)
                    {
                        $template = $extender->process($bfile);
                        $dspath .= '.composed';
                    }
                    else
                    {
                        $template = $parser->process(file::read($file));
                    }

                    file::create_recursive($dspath, true);

                    if (file::write($dspath, $template))
                    {
                        ui::success('Saved', $rpfile);
                    }
                    else
                    {
                        ui::error('Failed saving', $rpfile);
                    }
                }
                catch (\Exception $e)
                {
                    ui::error($e->getMessage());
                    if (!$watch)
                    {
                        return false;
                    }
                    else
                    {
                        continue;
                    }
                }
            }

            ui::nl();

            // Drop cache
            file::remove(file::find(fs::tmppath('tplp'), '*.php'));

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
    protected static function resolve_path($path, $absolute=false)
    {
        // Package
        if (preg_match('/^[a-z0-9_\.]+$/', $path))
        {
            $path = tplp::get_path($path);
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
