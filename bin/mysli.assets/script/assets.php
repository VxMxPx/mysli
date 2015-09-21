<?php

namespace mysli\assets\root\script; class assets
{
    const __use = '
        .{ assets -> lib.assets }
        mysli.toolkit.{ log, pkg }
        mysli.toolkit.cli.{ prog, param, ui }
        mysli.toolkit.fs.{ fs, file, dir }
    ';


    /**
     * Run Assets CLI.
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
        $prog = new prog('Mysli Assets', 'mysli.assets.assets');

        $prog->set_help(true);
        $prog->set_version('mysli.assets', true);

        $prog
        ->create_parameter('PACKAGE', [
            'required' => true,
            'help'     => 'Package\'s assets to be processed, in format: `vendor.package`. '.
                          'Alternativelly relative path can be provided.'
        ])
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch directory and re-parse when changes occurs.'
        ])
        ->create_parameter('--debug/-d', [
            'type' => 'boolean',
            'def'  => true,
            'help' => 'This will not compress nor merge assets, resulting in faster processing.'
        ])
        ->create_parameter('--publish/-p', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Copy files to public directory.'
        ])
        ->create_parameter('--file/-f', [
            'help' => 'Observe only specific file or directory (defined in map.ym).'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $publish, $debug, $file, $watch) =
            $prog->get_values('package', '-p', '-d', '-f', '-w');

        if (preg_match('/^[a-z0-9\.]+$/', $package))
        {
            // Was package provided rather than path?
            $path = pkg::get_path($package);
        }
        else
        {
            // Relative path
            $path = realpath(getcwd()."/{$package}");
            $package = pkg::by_path($path);
        }

        if (!dir::exists($path))
        {
            ui::warning("Couldn't resolve: `{$package}`, no such path: `{$path}`.");
            return false;
        }

        // Get map array
        $map = lib\assets::map($package);
        if (!$map)
        {
            ui::warn("Couldn't find `map.ym` for: `{$package}`.");
            return false;
        }

        return static::process($map, $path, $publish, $debug, $file, $watch);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Process assets.
     * --
     * @param array $map
     *        An array assets map (map.ym).
     *
     * @param string $path
     *        A full absolute path to the assets root.
     *
     * @param boolean $publish
     *        Weather assets shold be published on build.
     *        This will copy modified assets to the public directory.
     *
     * @param boolean $debug
     *        Weather this is a debug mode.
     *        This will not compress nor merge assets.
     *
     * @param string $id
     *        Target only one specific id, as defined in map file.
     *
     * @param boolean $watch
     *        Watch files for changes and rebuild when changes occurs.
     * --
     * @return boolean
     */
    protected static function process(array $map, $path, $publish, $debug, $id, $watch)
    {
        // If id provided, select it.
        if ($id)
        {
            $file_arr = isset($map['files'][$id]) ? $map['files'][$id] : false;
            if (!$file_arr)
            {
                ui::error("No such file defined in `map.ym`: `{$id}`.");
                return false;
            }
            else
            {
                $map['files'] = [
                    $file = $file_arr
                ];
            }
        }

        // Check for requirements
        if (!static::requirements($map))
        {
            ui::error(
                'Error',
                'Your system did not meet requirements, please install missing modules.'
            );
            return false;
        }

        // Discover files
        $includes = lib\assets::resolve_source_files($path, $map, $id);

        // Start observing FS for changes
        file::observe($path, function ($changes) use ($map, $includes)
        {
            // List of IDs + individual files within to be modified.
            $modfy = [];



        }, null, true, 3, true);
    }

    /**
     * Check weather all required modules are availables on a system.
     * --
     * @param array $map
     * --
     * @return boolean
     */
    protected static function requirements(array $map)
    {
        $requirements = [];

        // Grab all requirements defined in this map
        foreach ($map['files'] as $fid => $file)
        {
            if (!is_array($file['process']))
            {
                continue;
            }

            foreach ($file['process'] as $processor)
            {
                if (isset($map['process'][$processor]) &&
                    isset($map['process'][$processor]['require']))
                {
                    if (is_array($map['process'][$processor]['require']))
                    {
                        $requirements = array_merge(
                            $requirements,
                            $map['process'][$processor]['require']
                        );
                    }
                }
            }
        }

        // Only keep unique values not to do duplicated tests
        $requirements = array_unique($requirements);

        $return = true;

        // Check requirements
        foreach ($requirements as $requirement)
        {
            $o = null; $r = false;

            exec($requirement, $o, $r);

            if ($r !== 0)
            {
                ui::error("Failed", $requirement);
                $return = false;
            }
            else
            {
                ui::success("Ok", $requirement);
            }

            log::debug("Command: `{$requirement}`.", __CLASS__);
            log::debug(implode("\n", $o), __CLASS__);
        }

        return $return;
    }
}
