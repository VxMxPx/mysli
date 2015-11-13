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
            'type' => 'array',
            'def'  => [],
            'help' => 'Observe only specific ID(s) (defined in map.ym).'.
                      'You can specify more than one using comma.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $dev, $ids, $watch) = $prog->get_values('package', '-d', '-i', '-w');

        if (!preg_match('/^[a-z0-9\.]+$/', $package))
        {
            $path = realpath(getcwd()."/{$package}");
            $package = pkg::by_path($path);
        }

        return static::process($package, $dev, $ids, $watch);
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
     * @param boolean $dev
     *        Weather this is a development mode.
     *        True, will not compress nor merge assets, but will publish them.
     *        False, will compress and merge, but not publish.
     *
     * @param string $ids
     *        Target only one specific id(s) defined in map file.
     *
     * @param boolean $watch
     *        Watch files for changes and rebuild when changes occurs.
     * --
     * @return boolean
     */
    protected static function process($package, $dev, array $ids, $watch)
    {
        $root = lib\assets::path($package);

        if (!dir::exists($root))
        {
            ui::warning("Couldn't resolve: `{$package}` path.");
            return false;
        }

        $map = [];

        $observer = new observer($root);
        $observer->set_interval(3);
        $observer->set_limit($watch ? 0 : 1);

        $observer->observe(function ($changes) use (&$map, $root, $package, $dev, $ids)
        {
            // Defaults
            $rebuild = [];
            $pubpath = lib\assets::pubpath($package);

            // Map Reload
            if (empty($map) || isset($changes[fs::ds($root, 'map.ym')]))
            {
                ui::line("(Re)load `map.ym`.");

                try
                {
                    $map = lib\assets::map($package);
                    lib\assets::resolve_map($map, $root);
                }
                catch (\Exception $e)
                {
                    ui::error($e->getMessage());
                    return false;
                }

                if (!static::requirements($map, $ids))
                {
                    ui::error(
                        'Error',
                        'Your system did not meet requirements, '.
                        'please install missing modules.'
                    );
                    return false;
                }

                $rebuild = $ids ? $ids : array_keys($map['includes']);
                $changes = [];
            }

            // Run through changes
            foreach ($changes as $file => $mod)
            {
                $relative_file = ltrim(substr($file, strlen($root)), '/\\');

                // Try to find file in map
                foreach ($map['includes'] as $fid => $incopt)
                {
                    if (isset($incopt['resolved'][$relative_file]))
                    {
                        $file = $incopt['resolved'][$relative_file];
                        break 1;
                    }
                }

                // Output head & action
                $oact  = substr(ucfirst($mod['action']), 0, 3);
                $ohead = "({$oact} ".date('H:i:s').')';

                // Removed (Also covers: moved, renamed (which will have `to` set))
                if ($mod['action'] === 'removed' || isset($mod['to']))
                {
                    if (!is_array($file)) continue;

                    ui::warning($ohead, $file['source']);

                    $remove = [
                        fs::ds($root, 'dist~', $file['resolved']),
                        fs::ds($root, 'dist~', $file['compressed']),
                        fs::ds($pubpath, $file['resolved']),
                        fs::ds($pubpath, $file['compressed']),
                    ];

                    foreach ($remove as $rfile)
                    {
                        if (file::exists($rfile))
                        {
                            file::remove($rfile);
                        }
                    }

                    unset($map['includes'][$file['id']]['resolved'][$file['source']]);

                    if (!$dev || in_array('rebuild', $file['flags']))
                    {
                        $rebuild[] = $file['id'];
                    }

                    continue;
                }
                else if ($mod['action'] === 'added' || isset($mod['from'])) // added|moved|renamed
                {
                    // File NOT was discovered earlier
                    if (!is_array($file))
                    {
                        // Anyone cares about this file?
                        if ( ! ($id = lib\assets::id_from_file($file, $root, $map)))
                        {
                            continue;
                        }

                        // Care only about target ID
                        if (!isset($ids[$id]))
                        {
                            continue;
                        }

                        // Copy map & find newly added file.
                        // Some optimization is possible at this point.
                        $cp_map = $map;
                        lib\assets::resolve_map($cp_map, $root);

                        $file = $cp_map['includes'][$id]['resolved'][$relative_file];
                    }
                }

                if (!is_array($file)) continue;
                ui::info($ohead, $file['source']);

                // Rebuild single file or whole stack
                if (!in_array($file['id'], $rebuild))
                {
                    if (!$dev || in_array('rebuild', $file['flags']))
                    {
                        $rebuild[] = $file['id'];
                    }
                    else
                    {
                        $single_rebuild = $map['includes'][$file['id']];
                        $single_rebuild['resolved'] = [ $file['source'] => $file ];
                        static::rebuild($single_rebuild, $root, true, $pubpath);
                    }
                }
            }

            // IDs to be entierly rebuild if any...
            foreach (array_unique($rebuild) as $rid)
            {
                if (!isset($map['includes'][$rid]['resolved']))
                {
                    continue;
                }

                ui::nl();
                ui::line("Rebuild all files in {$rid}");
                static::rebuild($map['includes'][$rid], $root, $dev, $pubpath);
            }

        });

        return true;
    }

    /**
     * Rebuild file(s) for particular map.
     * --
     * @param array   $section (Resolved) Section of includes to process.
     * @param string  $root    Absolute assets path (root)
     * @param boolean $dev     true: publish, false: build+merge
     * @param string  $pubpath Public path (to publish modified file)
     */
    protected static function rebuild(array $section, $root, $dev, $pubpath)
    // array $files, $root, $dev, $pubpath, $merged)
    {
        $buffer = '';

        if (isset($section['process']) && !$section['process'])
        {
            if (!isset($section['publish']) || $section['publish'])
            {
                ui::info('Publish', $section['id']);
                dir::copy(fs::ds($root, $section['id']), fs::ds($pubpath, $section['id']));
            }
            return true;
        }

        foreach ($section['resolved'] as $file => $fileopt)
        {
            if (!isset($fileopt['module']))
            {
                continue;
            }

            $in_dir  = fs::ds($root, $fileopt['id']);
            $in_file = fs::ds($root, $file);
            $out_dir = fs::ds($root, 'dist~', $fileopt['id']);
            $out_file = fs::ds($root, 'dist~', $fileopt['resolved']);

            if (!dir::exists($out_dir))
            {
                dir::create($out_dir);
            }

            // Process
            ui::info('Process', $fileopt['source']);
            $command = $fileopt['module']['process'];
            $command = str_replace(
                [ '{in/file}', '{out/file}', '{in/}', '{out/}' ],
                [ "'{$in_file}'", "'{$out_file}'", "'{$in_dir}'", "'{$out_dir}'" ],
                $command
            );
            exec($command);

            if ($dev)
            {
                // Publish processed file
                // Append buffer
                if (file::exists($out_file) &&
                    (!isset($section['publish']) || $section['publish']))
                {
                    if (!dir::exists(fs::ds($pubpath, $fileopt['id'])))
                    {
                        dir::create(fs::ds($pubpath, $fileopt['id']));
                    }
                    ui::info('Publish', $file);
                    file::copy($out_file, fs::ds($pubpath, $fileopt['resolved']));
                    file::remove($out_file);
                }
            }
            elseif (file::exists($out_file))
            {
                // Build, reset variables
                $in_file  = $out_file;
                $in_dir   = $out_dir;
                $out_file = fs::ds($root, 'dist~', $fileopt['compressed']);

                ui::info('Build', $fileopt['resolved']);
                $command = $fileopt['module']['build'];
                $command = str_replace(
                    [ '{in/file}', '{out/file}', '{in/}', '{out/}' ],
                    [ "'{$in_file}'", "'{$out_file}'", "'{$in_dir}'", "'{$out_dir}'" ],
                    $command
                );
                exec($command);

                // Append buffer (Merge)
                if (file::exists($out_file))
                {
                    $contents = file::read($out_file);
                    if (trim($contents))
                    {
                        $buffer .= "\n{$contents}";
                    }
                    file::remove($in_file);
                    file::remove($out_file);
                }
            }
        }

        if (!$dev && isset($section['merge']))
        {
            ui::info('Write merge', $section['merge']);
            // Save buffer
            file::write(fs::ds($out_dir, $section['merge']), $buffer);
        }
    }

    /**
     * Check weather all required modules are available.
     * This will need resolved map.
     * --
     * @param array  $rmap
     * @param string $ids   Target specific ID(s)
     * --
     * @return boolean
     */
    protected static function requirements(array $rmap, $ids=null)
    {
        $requirements = [];

        if (!$ids)
        {
            $ids = array_keys( $rmap['includes'] );
        }
        elseif (!is_array($ids))
        {
            $ids = [ $ids ];
        }

        foreach ($ids as $mid)
        {
            if (!isset($rmap['includes'][$mid]))
            {
                continue;
            }
            else
            {
                $current = $rmap['includes'][$mid];
            }

            if (!isset($current['use-modules']) ||
                !is_array($current['use-modules']))
            {
                continue;
            }

            foreach ($current['use-modules'] as $module)
            {
                if (isset($current['modules'][$module]))
                {
                    if (isset($current['modules'][$module]['require']))
                    {
                        $requirements[] = $current['modules'][$module]['require'];
                    }
                }
                elseif (isset($rmap['modules'][$module]) && isset($rmap['modules'][$module]['require']))
                {
                    $requirements[] = $rmap['modules'][$module]['require'];
                }
            }
        }

        $return = true;
        $requirements = array_unique($requirements, SORT_STRING);

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
