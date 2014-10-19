<?php

namespace mysli\web\assets\script;

__use(__namespace__, [
    'mysli/framework' => [
        'fs/{file,fs,dir}',
        'type/{arr,str}',
        'exception/*'                   => 'framework/exception/%s',
        'cli/{output,input,param,util}' => 'cout,cinput,cparam,cutil',
    ],
    './{util,assetsc}',
    '../web'
]);

class assets {

    use util;

    /**
     * CLI frontend.
     * @param array $arguments
     * @return null
     */
    static function run(array $args) {
        $params = new cparam('Mysli Assets Builder', $args);
        $params->command = 'assets';
        $params->add(
            '--watch/-w',
            ['type'    => 'bool',
             'default' => false,
             'help'    => 'Watch package\'s assets and rebuild if changed']);
        $params->add(
            '--build/-b',
            ['type'    => 'bool',
             'default' => true,
             'help'    => 'Build assets']);
        $params->add(
            '--map/-m',
            ['type'    => 'str',
             'default' => 'map.ym',
             'help'    => 'Specify costume map file location (can be .json)']);
        $params->add(
            '--source/-s',
            ['type'    => 'str',
             'default' => 'assets',
             'help'    => 'Directory where assets are located']);
        $params->add(
            '--destination/-d',
            ['type'    => 'str',
             'default' => '_dist/assets',
             'help'    => 'Build destination']);
        $params->add(
            '--publish/-p',
            ['type'    => 'str',
             'default' => false,
             'help'    => 'Publish changes to web directory']);
        $params->add(
            'PACKAGE',
            ['type'       => 'str',
             'help'       => 'Package name, e.g.: mysli/web/ui',
             'required'   => true]);

        $params->parse();
        if (!$params->is_valid()) {
            cout::line($params->messages());
        } else {
            // Set values and run process
            $values = $params->values();
            return self::observe_or_build(
                $values['package'], $values['source'], $values['destination'],
                $values['map'], $values['publish'], $values['watch']);
        }
    }

    // private methods


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
            [$src, $dest, file::name($src), file::name($dest)],
            $command
        );
    }
    /**
     * Grab multiple files, and merge them into one.
     * @param  array  $map
     * @param  string $assets   assets path
     * @param  string $dest     dist path
     * @param  array  $changes
     * @return null
     */
    private static function assets_merge(array $map, $assets, $dest,
                                                            array $changes) {
        // For easy short access
        $sett = $map['settings'];

        foreach ($map['files'] as $main => $props) {
            // All processed files...
            $merged = '';

            foreach ($props['include'] as $file) {
                $file_ext = file::extension($file);
                $src_file = fs::ds($assets, $file);
                // defined in ../util
                $dest_file = fs::ds($dest, self::parse_extention($file,
                                                                 $sett['ext']));

                if (!arr::key_in($changes, $file)) {
                    continue;
                } else {
                    cout::line('Processing: ' . $file);
                    $processed++;
                }

                if (!file::exists($src_file)) {
                    cout::warn("File not found: {$src_file}");
                    continue;
                }

                if (!arr::key_in($sett['ext'], $ext)) {
                    cout::warn("Unknown extension, cannot process: `{$ext}`");
                    continue;
                }

                // Execute action for file
                cutil::execute(self::parse_command($sett['process'][$ext],
                                                    $src_file,
                                                    $dest_file));

                // Add content to the merged content
                $merged .= "\n\n" . file::read($dest_file);
            }
            // Some file were processed
            if ($merged) {
                cout::line("File: `{$main}`", false);
                $dest_main = fs::ds($dest, $main);
                $main_ext = file::extension($main);
                file::create_recursive($dest_main);
                try {
                    file::write($dest_file, $merged);
                } catch (\Exception $e) {
                    cout::error($e->getMessage());
                    continue;
                }
                cout::format('Saving+right+green OK');
                if ($props['compress']
                    && arr::key_in($sett['compress'], $main_ext)) {
                    if (cutil::execute(self::parse_command(
                                        $sett['compress'][$main_ext],
                                        $dest_main,
                                        $dest_main)))
                    {
                        cout::format("Compressing +right+green OK");
                    } else {
                        cout::format("Compressing +right+red OK");
                    }
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
     * @param  string  $assets  dir
     * @param  string  $dest    dir
     * @param  string  $map     file
     * @param  boolean $publish
     * @param  boolean $loop
     * @return null
     */
    private static function observe_or_build($package, $assets, $dest, $map,
                                             $publish, $loop) {
        // Check if we have a valid assets path
        $assets_path = fs::pkgpath($package, $assets);
        if (!dir::exists($assets_path)) {
            cout::yellow("Assets path is invalid: `{$assets_path}`");
            return false;
        }

        // Get map file if available...
        try {
            $map = self::get_map($package, $assets, $map);
        } catch (\Exception $e) {
            cout::warn($e->getMessage());
            return false;
        }

        // Check weather required modules are available
        if ($map['settings']['require'] !== false) {
            if (!self::check_required_modules($map['settings']['required'])) {
                return false;
            }
        }

        // Dest path
        $dest_path = fs::pkgpath($package, $dest);
        if (!dir::exists($dest_path)) {
            if (!cinput::confirm(
                "Dist directory (`{$dest}`) not found. Create it now?")) {
                cout::line('Terminated.');
                return false;
            } else {
                if (dir::create($dest)) {
                    cout::success("Directory successfully created.");
                } else {
                    cout::error("Failed to create directory.");
                    return false;
                }
            }
        }

        // `before` command
        if (isset($map['before'])) {
            foreach ($map['before'] as $before) {
                $command = self::parse_command($before,
                                               fs::ds($assets_path, 'null'),
                                               fs::ds($dest_path, 'null'));
                cout::line('Call: ' . $command);
                cout::line(cutil::execute($command));
            }
        }

        $signature = dir::signature($assets_path);

        do {
            $rsignature = dir::signature(fs::ds($assets_path, 'src'));

            if ($rsignature !== $signature) {
                $changes = self::what_changed(
                    $rsignature, $signature, strlen($assets_path));
                if (!empty($changes)) {
                    cout::line('What changed: ' . arr::readable($changes));
                    cout::line('Rebuilding assets...');
                    $signature = $rsignature;
                    // Process files...
                    self::assets_merge($map,
                                        $assets_path,
                                        $dest_path,
                                        $changes);

                    if ($publish) {
                        cout::line("Will publish changes...");
                        assetsc::publish($package, $dest);
                    }
                } else {
                    cout::line('No changes in source files.');
                }
            }

            $loop and sleep(3);
        } while ($loop);
    }
}
