<?php

namespace mysli\assets\root\script; class assets
{
    const __use = '
        .{ assets -> lib.assets }
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
            'def'    => true,
            'invert' => true,
            'help'   => 'This will not compress nor merge assets, resulting in faster processing.'
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

        list($package, $publish, $dev, $file, $watch) =
            $prog->get_values('package', '-p', '-d', '-f', '-w');

        if (!preg_match('/^[a-z0-9\.]+$/', $package))
        {
            // Relative path
            $path = realpath(getcwd()."/{$package}");
            $package = pkg::by_path($path);
        }

        // Get assets path
        $path = lib\assets::path($package);

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

        return static::process(
            $map,
            $path,
            $package,
            $publish,
            $dev,
            $file,
            $watch
        );
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
     * @param string $package
     *        Package ID.
     *
     * @param boolean $publish
     *        Weather assets shold be published on build.
     *        This will copy modified assets to the public directory.
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
    protected static function process(
        array $map, $path, $package, $publish, $dev, $id, $watch)
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

        // Discover includes
        $incd = lib\assets::get_dev_list($path, $map, $id);

        // List of IDs we're observing
        $ids  = array_keys($incd);

        // Make flat includes list of quick access --- get rid of IDs
        // e.g. from ID => [ file, file, file ], ID => [], ...
        //        to file => [..., ID], file => [..., ID], file => [ID], ...
        $includes = [];
        foreach ($incd as $inc_id => $inc_files)
        {
            foreach ($inc_files as $inc_file_id => &$inc_file_opt)
            {
                $inc_file_opt['id'] = $inc_id;
                $includes[$inc_file_id] = $inc_file_opt;
            }
        }

        // Setup observer
        $observer = new observer($path);
        $observer->set_interval(3);

        // Start observing FS for changes
        // added|removed|modified|renamed|moved
        $observer->observe(
        function ($changes)
        use ($map, $path, $package, $publish, $dev, &$includes, $ids)
        {
            // List of IDs to fully rebuild!
            $rebuild = [];

            foreach ($changes as $file => $mod)
            {
                if (strpos($file, 'dist~') !== false)
                    continue;

                // Removed (Alos covers: moved, renamed (which will have `to` set))
                if ($mod['action'] === 'removed' || isset($mod['to']))
                {
                    if (!isset($includes[$file])) continue;

                    ui::warning(
                        '(Del '.date('H:i:s').')',
                        "{$includes[$file]['rel_path']}/{$includes[$file]['rel_file']}"
                    );

                    // Remove file(s) from dist folder
                    $remove = [
                        // dist~/processed
                        fs::ds($path, $includes[$file]['processed']),
                        // dist~/compressed
                        fs::ds($path, $includes[$file]['compressed']),
                        // public/processed
                        fs::ds(lib\assets::pubpath($package), $includes[$file]['processed']),
                        // public/compressed
                        fs::ds(lib\assets::pubpath($package), $includes[$file]['compressed']),
                    ];

                    // Remove files from dist
                    foreach ($remove as $rfile)
                    {
                        if (file::exists($rfile))
                            file::remove($rfile);
                    }

                    // Remove file from list
                    $id = $includes[$file]['id'];
                    unset($includes[$file]);

                    // Not dev? Then rebuild
                    if (!$dev)
                        $rebuild[] = $id;
                }
                else if ($mod['action'] === 'added' || isset($mod['from'])) // added|moved|renamed
                {
                    if (!isset($includes[$file]))
                    {
                        // Anyone cares about this file?
                        if ( ! ($id = lib\assets::id_from_file($file, $path, $map)))
                            continue;

                        // Care only about target ID
                        if (!in_array($id, $ids))
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
                        $includes[$file] = $filemeta;
                    }
                    else
                    {
                        $id = $includes[$file]['id'];
                    }

                    ui::success(
                        '(Add '.date('H:i:s').')',
                        "{$includes[$file]['rel_path']}/{$includes[$file]['rel_file']}"
                    );

                    // Now rebuild
                    // TODO: Implement triggers (e.g. `!` to rebuild whole stack)
                    if (!in_array($id, $rebuild))
                    {
                        if (!$dev)
                        {
                            $rebuild[] = $id;
                        }
                        else
                        {
                            static::rebuild($map, $id, $file, $publish);
                        }
                    }
                }
                else // modified
                {
                    if (!isset($includes[$file])) continue;

                    ui::info(
                        '(Mod '.date('H:i:s').')',
                        "{$includes[$file]['rel_path']}/{$includes[$file]['rel_file']}"
                    );

                    $id = $includes[$file]['id'];

                    // Now rebuild
                    // TODO: Implement triggers (e.g. `!` to rebuild whole stack)
                    if (!in_array($id, $rebuild))
                    {
                        if (!$dev)
                        {
                            $rebuild[] = $id;
                        }
                        else
                        {
                            static::rebuild($map, $id, $file, $publish);
                        }
                    }
                }
            }

            // Rebuild entire IDs
            $rebuild = array_unique($rebuild);
            foreach ($rebuild as $rid)
            {
                static::rebuild($map, $rid, null, $publish);
            }
        });
    }

    /**
     * Rebuild file(s) for particulad map.
     * --
     * @param array   $map
     * @param string  $id
     * @param string  $file     Null to rebuld all files under ID.
     * @param boolean $publish  Weather to publish files.
     * --
     * @return null
     */
    protected static function rebuild(array $map, $id, $file, $publish)
    {
        ui::nl();
        ui::line('Rebuilding '.($file ? "file {$id}/".file::name($file) : "all files in {$id}"));
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
