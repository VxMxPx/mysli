<?php

namespace mysli\web\assets\sh;

__use(__namespace__, '
    ./assets -> root\assets
    mysli.framework.fs/fs,file,dir
    mysli.framework.type/arr,str
    mysli.framework.cli/output,input,param,util -> cout,cinput,cparam,cutil
');

class assets
{
    /**
     * CLI front-end.
     * @param array $arguments
     * @return null
     */
    static function __init(array $args)
    {
        $params = new cparam('Mysli Assets Builder', $args);
        $params->command = 'assets';

        $params->add('--watch/-w', [
            'type'    => 'bool',
            'default' => false,
            'help'    => 'Watch package\'s assets and rebuild if changed'
        ]);
        $params->add('--file/-f', [
            'type' => 'str',
            'help' => 'Observe only specific file (defined in map.ym)'
        ]);
        $params->add('--map/-m', [
            'type'    => 'str',
            'default' => 'assets.ym*',
            'help'    => 'Specify costume map file location (can be .json)'
        ]);
        $params->add('--source/-s', [
            'type'    => 'str',
            'default' => 'src*',
            'help'    => 'Directory where assets are located'
        ]);
        $params->add('--publish/-p', [
            'type'    => 'bool',
            'default' => false,
            'help'    => 'Publish changes to web directory'
        ]);
        $params->add('--interval/-i', [
            'type'    => 'int',
            'min'     => 1,
            'default' => 3,
            'help'    => 'How often (in seconds) should files be re-checked '.
                         'when watching (-w).'
        ]);
        $params->add('PACKAGE', [
            'type'       => 'str',
            'help'       => 'Package name, e.g.: mysli/web/ui',
            'required'   => true
        ]);

        $params->parse();

        if (!$params->is_valid())
        {
            cout::line($params->messages());
            return;
        }

        $v = $params->values();
        $package  = $v['package'];
        $file     = $v['file'];
        $publish  = $v['publish'];
        $watch    = $v['watch'];
        $interval = $v['interval'];

        // Check weather path was set || was defined in mysli.pkg || default
        try
        {
            list($source, $destination, $map) = root\assets::get_paths($package);
        }
        catch (\Exception $e)
        {
            cout::error("Error: {$e->getMessage()}");
            return;
        }

        if (substr($v['source'], -1) !== '*')
        {
            $source = $v['source'];
        }

        if (substr($v['map'], -1) !== '*')
        {
            $map = $v['map'];
        }

        return self::observe_or_build(
            $package, $file, $source, $destination, $map, $publish, $watch, $interval
        );
    }

    /**
     * Check if all required modules are available.
     * @param  array $required list of required modules
     * @return boolean
     */
    static function check_required_modules(array $required)
    {
        cout::line("\n* Checking if required modules are available:");

        foreach ($required as $id => $params)
        {
            $command = str_replace('{id}', $id, $params['command']);
            $expect  = str_replace('{id}', $id, $params['expect']);
            $expect  = preg_quote($expect);
            $expect  = str_replace('\\*', '.*?', $expect);
            $expect  = "/{$expect}/";
            $result  = cutil::execute($command);

            if (preg_match($expect, $result))
            {
                cout::format("    {$id}+right+green OK");
            }
            else
            {
                if ($params['level'] === 'warn')
                {
                    cout::format("    {$id}+right+yellow WARNING");
                }
                else
                {
                    cout::format("    {$id}+right+red FAILED");
                }

                $message = str_replace(
                    ['{id}', '{expect}', '{result}'],
                    [$id, $params['expect'], $result],
                    $params['message']
                );

                cout::line('    '.$message);

                if ($params['level'] === 'error')
                {
                    return false;
                }
            }
        }

        return true;
    }
    /**
     * Parse command, replace variables with data.
     * @param  string $command
     * @param  string $src
     * @param  string $dest
     * @return string
     */
    static function parse_command($command, $src, $dest)
    {
        $src = escapeshellcmd($src);
        $dest = escapeshellcmd($dest);

        return str_replace(
            [
                '{source}',
                '{dest}',
                '{source_dir}',
                '{dest_dir}',
                '{package}'
            ],
            [
                $src,
                $dest,
                dirname($src),
                dirname($dest),
                dirname(dirname($dest))
            ],
            $command
        );
    }
    /**
     * Grab multiple files, and merge them into one.
     * @param  array  $map
     * @param  string $t_file   target file
     * @param  string $assets   assets path
     * @param  string $dest     destination path
     * @param  array  $changes
     * @return null
     */
    static function assets_merge(array $map, $t_file, $assets, $dest, array $changes)
    {
        // For easy short access
        $sett = $map['settings'];

        foreach ($map['files'] as $main => $props)
        {
            if ($t_file && $main !== $t_file)
            {
                continue;
            }

            if (!isset($props['compress']))
            {
                $props['compress'] = true;
            }

            if (!isset($props['merge']))
            {
                $props['merge'] = true;
            }

            if (!isset($props['include']))
            {
                cout::error("No files to include for: `{$main}`");
                continue;
            }

            // All processed files...
            $merged = '';
            // Number of files that were actually modified ...
            $modified = 0;

            foreach ($props['include'] as $file)
            {
                $file_ext = file::extension($file);
                $src_file = fs::ds($assets, $file);

                // defined in ../util
                $dest_file = fs::ds(
                    $dest, root\assets::parse_extention($file, $sett['ext'])
                );

                if (!file::exists($src_file))
                {
                    cout::warn("[!] File not found: `{$src_file}`");
                    continue;
                }

                if (!arr::key_in($changes, $file))
                {
                    // Does it exists?
                    if (!file::exists($dest_file)) {
                        cout::warn("[!] Cannot append file: `{$dest_file}`, not found!");
                        continue;
                    }

                    // Still needs to be appened, but doesn't count as change...
                    if ($props['merge'])
                    {
                        $merged .= "\n\n" . file::read($dest_file);
                    }
                    continue;
                }

                if (!arr::key_in($sett['process'], $file_ext))
                {
                    cout::warn(
                        "[!] Unknown extension, cannot process: `{$file_ext}`"
                    );
                    continue;
                }

                // Execute action for file
                if (!dir::exists(dirname($dest_file)))
                {
                    cout::line(
                        "[i] Directory will be created: `".
                        substr(dirname($dest_file), strlen($dest)+1)."`",
                        false
                    );

                    if (!dir::create(dirname($dest_file)))
                    {
                        cout::format("+red+right FAILED");
                    }
                    else
                    {
                        cout::format("+green+right OK");
                    }
                }

                cout::line('    Processing: ' . $file);
                cutil::execute(
                    self::parse_command($sett['process'][$file_ext], $src_file, $dest_file)
                );

                // Do we have destination file?
                if (!file::exists($dest_file)) {
                    cout::error("Destination file not found: `{$dest_file}`");
                    continue;
                }

                // Add content to the merged content
                if ($props['merge'])
                {
                    $merged .= "\n\n" . file::read($dest_file);
                    $modified++;
                }
            }

            // Some file were processed
            cout::line("    > `{$main}`");
            if (!$props['merge'])
            {
                cout::format('        Done');
            }
            elseif ($modified)
            {
                $dest_main = fs::ds($dest, $main);
                $main_ext = file::extension($main);
                file::create_recursive($dest_main);

                try
                {
                    file::write($dest_main, $merged);
                }
                catch (\Exception $e)
                {
                    cout::error('[!] '.$e->getMessage());
                    continue;
                }

                cout::format('        Saving+right+green OK');

                if ($props['compress'] && arr::key_in($sett['compress'], $main_ext))
                {
                    cout::line("        Compressing");
                    cutil::execute(
                        self::parse_command($sett['compress'][$main_ext], $dest_main, $dest_main)
                    );
                }
            }
            else
            {
                cout::format('        Nothing to do...');
            }
        }

        return true;
    }
    /**
     * Compare two lists of files, and return changes for each file.
     * @param  array   $one
     * @param  array   $two
     * @param  integer $cutoff how much of path to remove (for pretty reports)
     * @return array
     */
    static function what_changed(array $one, array $two, $cutoff=0)
    {
        $changes = [];

        foreach ($one as $file => $hash)
        {
            if (!arr::key_in($two, $file))
            {
                $changes[str::slice($file, $cutoff)] = 'Added';
            }
            else
            {
                if ($two[$file] !== $hash)
                {
                    $changes[str::slice($file, $cutoff)] = 'Updated';
                }

                unset($two[$file]);
            }
        }

        if (!empty($two))
        {
            foreach ($two as $file => $hash)
            {
                $changes[str::slice($file, $cutoff)] = 'Removed';
            }
        }

        return $changes;
    }
    /**
     * Observe (and) build assets.
     * @param  string  $package
     * @param  string  $file    file to observe (if any)
     * @param  string  $assets  dir
     * @param  string  $dest    dir
     * @param  string  $map_fn  file
     * @param  boolean $publish
     * @param  boolean $loop
     * @param  integer $interval
     */
    static function observe_or_build(
        $package, $file, $assets, $dest, $map_fn, $publish, $loop, $interval)
    {
        cout::line("\n* Mysli Web Assets");
        cout::line(
            "    ".
            "{$package} | ".
            ($file ? "file: {$file} | " : '').
            ($publish ? 'publish | ' : '').
            ($loop    ? 'watch (interval: '.$interval.'s)' : '')
        );

        // Check weather assets path is valid
        $assets_path = fs::pkgreal($package, $assets);

        if (!dir::exists($assets_path))
        {
            cout::yellow("[!] Assets path is invalid: `{$assets_path}`");
            return false;
        }

        // Get map file
        try
        {
            $map = root\assets::get_map($package, $map_fn);
        }
        catch (\Exception $e)
        {
            cout::warn("[!] ".$e->getMessage());
            return false;
        }

        // Check required modules set in map file
        if ($map['settings']['require'] !== false)
        {
            if (!self::check_required_modules($map['settings']['require']))
            {
                return false;
            }
        }

        // Set destinatination path
        $dest_path = fs::pkgreal($package, $dest);

        if (!dir::exists($dest_path))
        {
            if (!cinput::confirm(
                "\n[?] Destination directory (`{$dest}`) not found. ".
                "Create it now?"))
            {
                cout::line('    Terminated.');
                return false;
            }
            else
            {
                if (dir::create($dest_path))
                {
                    cout::success("[^] Directory successfully created.");
                }
                else
                {
                    cout::error("[!] Failed to create directory.");
                    return false;
                }
            }
        }

        // Execute `before` commands
        if (isset($map['before']))
        {
            cout::line("\n* Executing `before` commands:");
            foreach ($map['before'] as $before)
            {
                $command = self::parse_command(
                    $before,
                    fs::ds($assets_path, 'null'),
                    fs::ds($dest_path, 'null')
                );

                cout::line('    '.$command);

                if ($co = cutil::execute($command))
                {
                    cout::line('    '.$co);
                }
            }
        }

        // Files signature
        $signature = [];
        $rsignature = null;
        $observable_files = self::observable_files($assets_path, $file, $map['files']);

        // Map signature
        $map_sig  = file::signature(fs::pkgreal($package, $map_fn));
        $map_rsig = null;

        do
        {
            // Get new map signature
            $map_rsig = file::signature(fs::pkgreal($package, $map_fn));

            // Reload map...
            if ($map_sig !== $map_rsig)
            {
                cout::line("\n* Map changed, it will be reloaded.");
                try
                {
                    $map = root\assets::get_map($package, $map_fn, true);
                    $observable_files = self::observable_files(
                        $assets_path, $file, $map['files']
                    );
                }
                catch (\Exception $e)
                {
                    cout::warn('[!] '.$e->getMessage());
                    return false;
                }

                $map_sig = $map_rsig;
            }

            // Get new signature of observable files
            try
            {
                $rsignature = file::signature($observable_files);
            }
            catch (\Exception $e)
            {
                cout::warn('[!] '.$e->getMessage());
                cout::warn('    Retrying in '.($interval*2).' seconds.');
                sleep($interval*2);
                continue;
            }

            // Signature is the same, continue or break...
            if ($rsignature !== $signature)
            {
                $changes = self::what_changed(
                    $rsignature, $signature, strlen($assets_path)+1
                );

                if (empty($changes))
                {
                    cout::line("\n* No changes in source files.");
                }
                else
                {
                    cout::line("\n* What changed: \n".arr::readable($changes, 4));
                    cout::line("\n* Rebuilding assets:");

                    self::assets_merge($map, $file, $assets_path, $dest_path, $changes);

                    if ($publish)
                    {
                        cout::line("\n* Publishing changes:");
                        if (root\assets::publish($package, $dest, $map))
                        {
                            cout::format("+green     DONE");
                        }
                        else
                        {
                            cout::format("+red     FAILED");
                        }
                    }
                }

                $signature = $rsignature;
            }

            $loop and sleep($interval);

        } while ($loop);
    }
    /**
     * Get list of files to observe
     * @param  string $dir
     * @param  string $t_file
     * @param  array  $files
     * @return array
     */
    static function observable_files($dir, $t_file, $files)
    {
        $observable = [];

        foreach ($files as $id => $prop)
        {
            if (!isset($prop['include']))
            {
                cout::warn("[!] Include statement is missing. Skip: `{$id}`");
                continue;
            }

            if ($t_file && $t_file !== $id)
            {
                continue;
            }

            foreach ($prop['include'] as $file)
            {
                $ffile = fs::ds($dir, $file);

                if (!file::exists($ffile))
                {
                    cout::warn("[!] File not found: `{$file}`");
                }

                $observable[] = $ffile;
            }
        }

        return $observable;
    }
}
