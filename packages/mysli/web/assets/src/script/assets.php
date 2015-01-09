<?php

namespace mysli\web\assets\script;

class assets {

    /**
     * CLI front-end.
     * @param array $arguments
     * @return null
     */
    static function run(array $args) {
        $params = new cparam('Mysli Assets Builder', $args);
        $params->command = 'assets';
        $params->description_long = l("* If --map, --source and
            --destination are not provided, they'll be set from `mysli.pkg.ym`
            (assets section), if not defined there, defaults will be used.");

        $params->add(
            '--watch/-w',
            ['type'    => 'bool',
             'default' => false,
             'help'    => 'Watch package\'s assets and rebuild if changed']);
        $params->add(
            '--file/-f',
            ['type' => 'str',
             'help' => 'Observe only specific file (defined in map.ym)']);
        $params->add(
            '--map/-m',
            ['type'    => 'str',
             'default' => 'map.ym*',
             'help'    => 'Specify costume map file location (can be .json)']);
        $params->add(
            '--source/-s',
            ['type'    => 'str',
             'default' => 'assets*',
             'help'    => 'Directory where assets are located']);
        $params->add(
            '--destination/-d',
            ['type'    => 'str',
             'default' => '_dist/assets*',
             'help'    => 'Build destination']);
        $params->add(
            '--publish/-p',
            ['type'    => 'bool',
             'default' => false,
             'help'    => 'Publish changes to web directory']);
        $params->add(
            '--interval/-i',
            ['type'    => 'int',
             'min'     => 1,
             'default' => 3,
             'help'    => 'How often (in seconds) should files be re-checked '.
                          'when watching (-w).']);
        $params->add(
            'PACKAGE',
            ['type'       => 'str',
             'help'       => 'Package name, e.g.: mysli/web/ui',
             'required'   => true]);

        $params->parse();

        if (!$params->is_valid()) {
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
        list(
            $source,
            $destination,
            $map
        ) = root\assets::get_default_paths($package);

        if (substr($v['source'], -1) !== '*') {
            $source = $v['source'];
        }
        if (substr($v['destination'], -1) !== '*') {
            $destination = $v['destination'];
        }
        if (substr($v['map'], -1) !== '*') {
            $map = $v['map'];
        }

        return self::observe_or_build(
            $package, $file, $source, $destination, $map, $publish, $watch,
            $interval);
    }

    /**
     * Check if all required modules are available.
     * @param  array $required list of required modules
     * @return boolean
     */
    private static function check_required_modules(array $required) {

        cout::line('Checking if required modules are available...');

        foreach ($required as $id => $params) {
            $command = str_replace('{id}', $id, $params['command']);
            $expect  = str_replace('{id}', $id, $params['expect']);
            $expect  = preg_quote($expect);
            $expect  = str_replace('\\*', '.*?', $expect);
            $expect  = "/{$expect}/";
            $result  = cutil::execute($command);
            if (preg_match($expect, $result)) {
                cout::format("{$id}+right+green OK");
            } else {
                if ($params['type'] === 'warn') {
                    cout::format("{$id}+right+yellow WARNING");
                } else {
                    cout::format("{$id}+right+red FAILED");
                }
                $message = str_replace([
                    '{id}', '{expect}', '{result}'],
                    [$id, $params['expect'], $result],
                    $params['message']);
                count::line($message);

                if ($params['type'] === 'error') { return false; }
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
    private static function parse_command($command, $src, $dest) {
        return str_replace(
            ['{source}', '{dest}', '{source_dir}', '{dest_dir}'],
            [$src, $dest, dirname($src), dirname($dest)],
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
    private static function assets_merge(
        array $map, $t_file, $assets, $dest, array $changes)
    {
        // For easy short access
        $sett = $map['settings'];

        foreach ($map['files'] as $main => $props) {

            if ($t_file && $main !== $t_file) {
                continue;
            }

            // All processed files...
            $merged = '';

            foreach ($props['include'] as $file) {

                $file_ext = file::extension($file);
                $src_file = fs::ds($assets, $file);
                // defined in ../util
                $dest_file = fs::ds($dest, root\assets::parse_extention(
                                                                $file,
                                                                $sett['ext']));

                if (!file::exists($src_file)) {
                    cout::warn('File not found: `'.$src_file.'`');
                    continue;
                }

                if (!arr::key_in($changes, $file)) {
                    // Still needs to be appened...
                    $merged .= "\n\n" . file::read($dest_file);
                } else {
                    cout::line('Processing: ' . $file);
                }

                if (!file::exists($src_file)) {
                    cout::warn("File not found: {$src_file}");
                    continue;
                }

                if (!arr::key_in($sett['process'], $file_ext)) {
                    cout::warn(
                        "Unknown extension, cannot process: `{$file_ext}`");
                    continue;
                }

                // Execute action for file
                if (!dir::exists(dirname($dest_file))) {
                    cout::line(
                        "Directory will be created: `".dirname($dest_file)."`",
                        false);

                    if (!dir::create(dirname($dest_file))) {
                        cout::format("+red+right FAILED");
                    } else {
                        cout::format("+green+right OK");
                    }
                }

                cutil::execute(self::parse_command(
                                                $sett['process'][$file_ext],
                                                $src_file,
                                                $dest_file));

                // Add content to the merged content
                $merged .= "\n\n" . file::read($dest_file);
            }
            // Some file were processed
            if ($merged) {
                cout::line("File: `{$main}`");
                $dest_main = fs::ds($dest, $main);
                $main_ext = file::extension($main);
                file::create_recursive($dest_main);
                try {
                    file::write($dest_main, $merged);
                } catch (\Exception $e) {
                    cout::error($e->getMessage());
                    continue;
                }
                cout::format('  Saving+right+green OK');
                if ($props['compress']
                    && arr::key_in($sett['compress'], $main_ext)) {
                    cout::line("  Compress!");
                    cutil::execute(self::parse_command(
                                        $sett['compress'][$main_ext],
                                        $dest_main,
                                        $dest_main));
                }
            }
        }
    }
    /**
     * Compare two lists of files, and return changes for each file.
     * @param  array   $one
     * @param  array   $two
     * @param  integer $cutoff how much of path to remove (for pretty reports)
     * @return array
     */
    private static function what_changed(array $one, array $two, $cutoff=0) {
        $changes = [];

        foreach ($one as $file => $hash) {
            if (!arr::key_in($two, $file)) {
                $changes[str::slice($file, $cutoff)] = 'Added';
            } else {
                if ($two[$file] !== $hash) {
                    $changes[str::slice($file, $cutoff)] = 'Updated';
                }
                unset($two[$file]);
            }
        }

        if (!empty($two)) {
            foreach ($two as $file => $hash) {
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
    private static function observe_or_build(
        $package, $file, $assets, $dest, $map_fn, $publish, $loop, $interval)
    {
        // Check weather assets path is valid
        $assets_path = fs::pkgpath($package, $assets);
        if (!dir::exists($assets_path)) {
            cout::yellow("Assets path is invalid: `{$assets_path}`");
            return false;
        }

        // Get map file
        try {
            $map = root\assets::get_map($package, $assets, $map_fn);
        } catch (\Exception $e) {
            cout::warn($e->getMessage());
            return false;
        }

        // Check required modules set in map file
        if ($map['settings']['require'] !== false) {
            if (!self::check_required_modules($map['settings']['require'])) {
                return false;
            }
        }

        // Set destinatination path
        $dest_path = fs::pkgpath($package, $dest);
        if (!dir::exists($dest_path)) {
            if (!cinput::confirm(
                "Destination directory (`{$dest}`) not found. Create it now?"))
            {
                cout::line('Terminated.');
                return false;
            } else {
                if (dir::create($dest_path)) {
                    cout::success("Directory successfully created.");
                } else {
                    cout::error("Failed to create directory.");
                    return false;
                }
            }
        }

        // Execute `before` commands
        if (isset($map['before'])) {
            foreach ($map['before'] as $before) {
                $command = self::parse_command(
                    $before, fs::ds($assets_path, 'null'),
                    fs::ds($dest_path, 'null'));

                cout::line('Call: ' . $command);
                cout::line(cutil::execute($command));
            }
        }

        // Files signature
        $signature = [];
        $rsignature = null;
        $observable_files = self::observable_files(
            $assets_path, $file, $map['files']);

        // Map signature
        $map_sig  = file::signature(fs::pkgpath($package, $assets, $map_fn));
        $map_rsig = null;

        do {
            // Get new map signature
            $map_rsig = file::signature(
                fs::pkgpath($package, $assets, $map_fn));

            // Reload map...
            if ($map_sig !== $map_rsig) {
                cout::line("Map changed, reloading...");
                try {
                    $map = root\assets::get_map(
                        $package, $assets, $map_fn, true);

                    $observable_files = self::observable_files(
                        $assets_path, $file, $map['files']);
                } catch (\Exception $e) {
                    cout::warn('Error: '.$e->getMessage());
                    return false;
                }
                $map_sig = $map_rsig;
            }

            // Get new signature of observable files
            try {
                $rsignature = file::signature($observable_files);
            } catch (\Exception $e) {
                cout::warn($e->getMessage());
                cout::warn('Retrying in '.($interval*2).' seconds...');
                sleep($interval*2);
                continue;
            }

            // Signature is the same, continue or break...
            if ($rsignature !== $signature) {

                $changes = self::what_changed(
                    $rsignature, $signature, strlen($assets_path)+1);

                if (empty($changes)) {
                    cout::line('No changes in source files.');
                } else {

                    cout::line("What changed: \n" . arr::readable($changes));
                    cout::line('Rebuilding assets...');
                    $signature = $rsignature;

                    self::assets_merge(
                        $map, $file, $assets_path, $dest_path, $changes);

                    if ($publish) {
                        cout::line("Will publish changes...", false);
                        if (root\assets::publish($package, $dest)) {
                            cout::format("+green+right OK");
                        } else {
                            cout::format("+red+right FAILED");
                        }
                    }
                }
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
    private static function observable_files($dir, $t_file, $files) {
        $observable = [];

        foreach ($files as $id => $prop) {

            if (!isset($prop['include'])) {
                cout::warn(
                    "Include statement is missing. Skip: `{$id}`");
                continue;
            }

            if ($t_file && $t_file !== $id) {
                continue;
            }

            foreach ($prop['include'] as $file) {
                $ffile = fs::ds($dir, $file);
                if (!file::exists($ffile)) {
                    cout::warn("File not found: `{$file}`");
                }

                $observable[] = $ffile;
            }
        }

        return $observable;
    }
}
