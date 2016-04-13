<?php

namespace mysli\assets\root\script; class assets
{
    const __use = <<<fin
        .{ assets -> lib.assets, exception.assets }
        mysli.toolkit.{ log, pkg, type.str -> str }
        mysli.toolkit.cli.{ prog, param, ui, output }
        mysli.toolkit.fs.{ observer, fs, file, dir }
fin;

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
            'help'   => 'This will not compress nor merge assets, resulting in faster processing. '.
                        'Processed files will be published.'
        ])
        ->create_parameter('--id/-i', [
            'type' => 'array',
            'def'  => [],
            'help' => 'Observe only specific ID(s) (defined in map.ym). '.
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
                ui::title('Reload');

                try
                {
                    $map = lib\assets::map($package);
                    lib\assets::resolve_map($map, $root);

                    if (is_array($map))
                        ui::success('OK', 'map.ym', 1);
                    else
                        ui::warning('WARNING', 'map.ym', 1);
                }
                catch (\Exception $e)
                {
                    ui::error('ERROR', $e->getMessage(), 1);
                    return false;
                }

                if (!static::requirements($map, $ids))
                {
                    ui::error(
                        'ERROR',
                        'Your system did not meet requirements, '.
                        'please install missing modules.'
                    );
                    return false;
                }

                $rebuild = $ids ? $ids : array_keys($map['includes']);
                $changes = [];

                // Now cleanup!
                ui::title('Cleanup');

                foreach ($rebuild as $id)
                {
                    if (dir::exists($root, $id, 'dist~'))
                    {
                        dir::remove(fs::ds($root, $id, 'dist~'))
                            ? ui::success('OK', "Removed {$id}/dist~", 1)
                            : ui::error('ERROR', "Failed to removed {$id}/dist~", 1);
                    }
                    if ($dev && dir::exists($pubpath, $id))
                    {
                        dir::remove(fs::ds($pubpath, $id))
                            ? ui::success('OK', "Removed public:{$id}", 1)
                            : ui::error('ERROR', "Failed to remove public:{$id}", 1);
                    }
                }
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
                $oact  = ucfirst($mod['action']);
                $ohead = "{$oact} (".date('H:i:s').") {$relative_file}";

                ui::nl();
                ui::strong($ohead);

                // Removed (Also covers: moved, renamed (which will have `to` set))
                if ($mod['action'] === 'removed' || isset($mod['to']))
                {
                    if (!is_array($file)) continue;

                    $remove = [
                        fs::ds($root, $file['id'], $file['resolved_dist']),
                        fs::ds($root, $file['id'], $file['compressed_dist']),
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

                    if (!$dev || in_array('reload', $file['flags']))
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
                        if (!($id = lib\assets::id_from_file($file, $root, $map)))
                        {
                            ui::info("No one cares about this file.", null, 1);
                            continue;
                        }

                        // Care only about target ID
                        if ($ids && !isset($ids[$id]))
                        {
                            ui::info(
                                "Not in target: `{$id}`, watching: ".
                                implode(', ', array_keys($ids)).".", null, 1);
                            continue;
                        }

                        // Copy map & find newly added file.
                        // Some optimization is possible at this point.
                        lib\assets::resolve_map($map, $root);

                        // ui::line($relative_file); dump_s($cp_map);
                        $file = $map['includes'][$id]['resolved'][$relative_file];
                    }
                }

                if (!is_array($file)) continue;

                // Rebuild single file or whole stack
                if (!in_array($file['id'], $rebuild))
                {
                    if (!$dev || in_array('reload', $file['flags']))
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

                ui::title("Dir {$rid}");
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
    {
        $buffer = '';

        // No need to process anything
        if (!$section['process'])
        {
            ui::line($section['id'], 1);

            // Perhaps needs to be published
            if ($section['publish'])
            {

                if (dir::copy(fs::ds($root, $section['id']), fs::ds($pubpath, $section['id'])))
                {
                    ui::success("Published", null, 2);
                    return true;
                }
                else
                {
                    ui::error("Failed to Publish", null, 2);
                    return false;
                }
            }
            else
            {
                ui::info("Skipped", null, 2);
                return true;
            }
        }

        $remove_on_merge = [];

        foreach ($section['resolved'] as $file => $fileopt)
        {
            if (!isset($fileopt['module']))
            {
                continue;
            }

            $in_dir  = fs::ds($root, $fileopt['id']);
            $in_file = fs::ds($root, $file);
            $out_dir = fs::ds($root, $fileopt['id'], 'dist~');
            $out_file = fs::ds($root, $fileopt['resolved_dist']);

            if (!dir::exists($out_dir))
            {
                ui::line(file::name($out_dir), 1);
                dir::create($out_dir)
                    ? ui::success('Created', null, 2)
                    : ui::error('Failed', null, 2);
            }

            ui::line(file::name($fileopt['source']), 1);

            // Process
            $command = $fileopt['module']['process'];
            $command = str_replace(
                [ '{in/file}', '{out/file}', '{in/}', '{out/}' ],
                [ "'{$in_file}'", "'{$out_file}'", "'{$in_dir}'", "'{$out_dir}'" ],
                $command
            );

            unset($out, $r);
            exec($command, $out, $r);

            if ($r === 0)
            {
                ui::success('Processed', null, 2);
            }
            else
            {
                ui::error('Process Failed', null, 2);
                ui::error('ERROR', trim(implode("\n", $out)), 2);
                continue;
            }

            if ($dev)
            {
                // Publish processed file, append buffer
                if (file::exists($out_file) && $section['publish'])
                {
                    if (!dir::exists(fs::ds($pubpath, $fileopt['id'])))
                    {
                        dir::create(fs::ds($pubpath, $fileopt['id']));
                    }

                    ui::success('Published', null, 2);

                    file::copy($out_file, fs::ds($pubpath, $fileopt['resolved']))
                        or ui::error('ERROR', "Couldn't copy `{$out_file}`", 2);

                    $remove_on_merge[] = $out_file;
                }
            }
            elseif (file::exists($out_file))
            {
                // Build, reset variables
                $in_file  = $out_file;
                $in_dir   = $out_dir;
                $out_file = fs::ds($root, $fileopt['compressed_dist']);

                $command = $fileopt['module']['build'];
                $command = str_replace(
                    [ '{in/file}', '{out/file}', '{in/}', '{out/}' ],
                    [ "'{$in_file}'", "'{$out_file}'", "'{$in_dir}'", "'{$out_dir}'" ],
                    $command
                );

                if ($command)
                {
                    unset($out, $r);
                    exec($command, $out, $r);

                    if ($r === 0)
                    {
                        ui::success('Build', null, 2);
                    }
                    else
                    {
                        ui::error('Build Fail', null, 2);
                        ui::error('ERROR', trim(implode("\n", $out)), 2);
                        continue;
                    }
                }


                // Append buffer (Merge)
                if (file::exists($out_file))
                {
                    $contents = file::read($out_file);

                    if (trim($contents))
                    {
                        $buffer .= "\n{$contents}";
                    }

                    file::remove($in_file);
                    $remove_on_merge[] = $out_file;
                }
            }
        }

        if (!$dev && isset($section['merge']) && $section['merge'])
        {
            ui::line($section['merge'], 1);

            // Save buffer
            file::write(fs::ds($out_dir, $section['merge']), $buffer) !== false
                ? ui::success('Merged', null, 2)
                : ui::error('Failed to Merge', null, 2);

            foreach ($remove_on_merge as $remove)
            {
                file::exists($remove) and file::remove($remove);
            }
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
        ui::title('Modules');

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
                ui::error("Failed", $requirement, 1);
                $return = false;
            }
            else
            {
                ui::success("Ok", $requirement, 1);
            }

            log::debug("Command: `{$requirement}`.", __CLASS__);
            log::debug(implode("\n", $o), __CLASS__);
        }

        return $return;
    }
}
