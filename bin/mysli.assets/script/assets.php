<?php

namespace mysli\assets\root\script; class assets
{
    const __use = '
        .{ assets -> lib.assets, exception.assets }
        mysli.toolkit.{ log, pkg }
        mysli.toolkit.cli.{ prog, param, ui }
        mysli.toolkit.fs.{ observer, fs, file, dir }
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
        $prog = new prog('Mysli Assets', __CLASS__);

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
        ->create_parameter('--dev/-d', [
            'type'   => 'boolean',
            'def'    => false,
            'help'   => 'This will not compress nor merge assets, resulting in faster processing.'.
                        'Processed files will be published.'
        ])
        ->create_parameter('--id/-i', [
            'help' => 'Observe only specific ID (defined in map.ym).'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $dev, $id, $watch) = $prog->get_values('package', '-d', '-i', '-w');

        if (!preg_match('/^[a-z0-9\.]+$/', $package))
        {
            // Relative path
            $path = realpath(getcwd()."/{$package}");
            $package = pkg::by_path($path);
        }

        return static::process($package, $dev, $id, $watch);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Process assets.
     * --
     * @param string $package
     *        Package ID.
     *
     * @param boolean $development
     *        Weather this is a development mode.
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
    protected static function process($package, $dev, $id, $watch)
    {
        // Get assets path
        $path = lib\assets::path($package);

        if (!dir::exists($path))
        {
            ui::warning("Couldn't resolve: `{$package}`, no such path: `{$path}`.");
            return false;
        }

        // Define defaults
        $map = $includes = [];

        // Setup observer
        $observer = new observer($path);
        $observer->set_interval(3);
        $observer->set_limit($watch ? 0 : 1);

        // Start observing FS for changes
        $observer->observe(function ($changes)
            use (&$map, &$includes, $path, $package, $dev, $id)
        {
            if (empty($map) || isset($changes[fs::ds($path, 'map.ym')]))
            {
                ui::line("(Re)load `map.ym`.");

                // Get map and includes
                try
                {
                    $map = static::reload_map($package, $id);
                }
                catch (\Exception $e)
                {
                    ui::error($e->getMessage());
                    return false;
                }

                // Check for requirements
                if (!static::requirements($map))
                {
                    ui::error(
                        'Error',
                        'Your system did not meet requirements, '.
                        'please install missing modules.'
                    );
                    return false;
                }

                $includes = lib\assets::get_dev_list($path, $map, $id);
                $rebuild = array_keys($includes);
                $changes = [];
            }
            else
            {
                // List of IDs to fully rebuild!
                $rebuild = [];
            }

            foreach ($changes as $file => $mod)
            {
                // Find file in includes
                foreach ($includes as $incid => $incfiles)
                {
                    if (isset($incfiles[$file]))
                    {
                        $file = $incfiles[$file];
                        $file['id'] = $incid;
                    }
                }

                // Output head & action
                $oact  = substr(ucfirst($mod['action']), 0, 3);
                $ohead = "({$oact} ".date('H:i:s').')';

                // Removed (Also covers: moved, renamed (which will have `to` set))
                if ($mod['action'] === 'removed' || isset($mod['to']))
                {
                    if (!is_array($file)) continue;

                    ui::warning(
                        $ohead,
                        "{$file['rel_path']}/{$file['rel_file']}"
                    );

                    // Remove file(s) from dist folder
                    $remove = [
                        // dist~/processed
                        fs::ds($path, $file['processed']),
                        // dist~/compressed
                        fs::ds($path, $file['compressed']),
                        // public/processed
                        fs::ds(lib\assets::pubpath($package), $file['processed']),
                        // public/compressed
                        fs::ds(lib\assets::pubpath($package), $file['compressed']),
                    ];

                    // Remove files from dist
                    foreach ($remove as $rfile)
                    {
                        if (file::exists($rfile))
                            file::remove($rfile);
                    }

                    // Remove file from list
                    $id = $file['id'];
                    unset($includes[$id][$file['abs_path']]);

                    // Not dev? Then rebuild
                    if (!$dev)
                        $rebuild[] = $id;
                }
                else if ($mod['action'] === 'added' || isset($mod['from'])) // added|moved|renamed
                {
                    if (!is_array($file))
                    {
                        // Anyone cares about this file?
                        if ( ! ($id = lib\assets::id_from_file($file, $path, $map)))
                            continue;

                        // Care only about target ID
                        if (!isset($includes[$id]))
                            continue;

                        // Resolve file paths and append it
                        $filemeta = lib\assets::get_dev_file(
                            $file,
                            $path,
                            lib\assets::get_processors(
                                file::name($file),
                                $map['files'][$id]['process'],
                                $map['process']
                            )
                        );
                        $filemeta['id'] = $id;
                        $includes[$id][$file] = $filemeta;
                        $file = $filemeta;
                    }

                    ui::success(
                        $ohead,
                        "{$file['rel_path']}/{$file['rel_file']}"
                    );

                    // Now rebuild
                    // TODO: Implement triggers (e.g. `!` to rebuild whole stack)
                    if (!in_array($file['id'], $rebuild))
                    {
                        if (!$dev)
                        {
                            $rebuild[] = $file['id'];
                        }
                        else
                        {
                            static::rebuild([ $file ], $package, $dev);
                        }
                    }
                }
                else // modified
                {
                    if (!is_array($file)) continue;

                    ui::info(
                        $ohead,
                        "{$file['rel_path']}/{$file['rel_file']}"
                    );

                    // Now rebuild
                    // TODO: Implement triggers (e.g. `!` to rebuild whole stack)
                    if (!in_array($file['id'], $rebuild))
                    {
                        if (!$dev)
                        {
                            $rebuild[] = $file['id'];
                        }
                        else
                        {
                            static::rebuild([ $file ], $package, $dev);
                        }
                    }
                }
            }

            // Rebuild entire IDs
            $rebuild = array_unique($rebuild);
            foreach ($rebuild as $rid)
            {
                ui::nl();
                ui::line("Rebuild all files in {$rid}");
                static::rebuild($includes[$rid], $package, $dev);
            }
        });

        return true;
    }

    /**
     * Rebuild file(s) for particular map.
     * --
     * @param array   $files    (Resolved) Files to process.
     * @param string  $package
     * @param boolean $dev
     */
    protected static function rebuild(array $files, $package, $dev)
    {
        foreach ($files as $file)
        {

        }
    }

    /**
     * Reload map and grab required ID.
     * --
     * @param  string $package
     * @param  string $id
     * --
     * @throws mysli\assets\exception\assets 10 Couldn't find `map.ym`.
     * @throws mysli\assets\exception\assets 20 ID not defined in `map.ym`.
     * --
     * @return array
     */
    protected static function reload_map($package, $id)
    {
        // Get map array
        $map = lib\assets::map($package);

        // Discover includes

        if (!$map)
            throw new exception\assets(
                "Couldn't find `map.ym` for: `{$package}`.", 10
            );

        // If id provided, select it.
        if ($id)
        {
            $file_arr = isset($map['files'][$id]) ? $map['files'][$id] : false;

            if (!$file_arr)
                throw new exception\assets(
                    "No such file defined in `map.ym`: `{$id}`.", 20
                );

            $map['files'] = [ $file = $file_arr ];
        }

        return $map;
    }

    /**
     * Check weather all required modules are available.
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
