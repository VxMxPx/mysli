<?php

namespace mysli\assets\script {

    \inject::to(__namespace__)
    ->from('mysli/cli/{output,cinput,param}', 'cout,cinput,cparam')
    ->from('mysli/config')
    ->from('mysli/fs')
    ->from('mysli/core/type/{arr,str}')
    ->from('mysli/web')
    ->from('mysli/core/exception/*');

    class assets {

        use mysli\assets\util;

        /**
         * CLI frontend.
         * @param array $arguments
         * @return null
         */
        static function run(array $arguments) {
            $params = new cparam('Mysli Assets Builder', $arguments);
            $params->add(
                '--watch/-w',
                ['type'    => 'bool',
                 'default' => false,
                 'help'    => 'Observe assets']);
            $params->add(
                '--build/-b',
                ['type'    => 'bool',
                 'default' => true,
                 'help'    => 'Build assets']);
            $params->add(
                '--map/-m',
                ['type'    => 'str',
                 'default' => 'assets/map.json',
                 'help'    => 'Specify costume map file location']);
            $params->add(
                '--source/-s',
                ['type'    => 'str',
                 'default' => 'src',
                 'help'    => 'Directory where assets are located']);
            $params->add(
                '--destination/-d',
                ['type'    => 'str',
                 'default' => 'dist',
                 'help'    => 'Build destination']);
            $params->add(
                '--public/-p',
                ['type'    => 'str',
                 'default' => null,
                 'help'    => 'Public directry, where assets will be placed']);
            $params->add(
                'PACKAGE',
                ['type'       => 'str',
                 'help'       => 'Package name, e.g.: mysli/ui',
                 'required'   => true]);

            $params->parse();
            cout::info($params->messages());
            if ($params->is_valid()) {
                $values = $params->values();
                return self::observe_or_build(
                    $values['package'], $values['map'], $values['source'],
                    $values['public'], $values['watch']);
            }
        }

        // private methods

        /**
         * Read assets file for particular package.
         * @param  string $package
         * @param  string $assets relative path
         * @return array
         */
        private static function read_assets($package, $assets) {
            $filename = fs::pkgpath($package, $assets);
            if (!fs\file::exists($filename)) {
                throw new exception\not_found(
                    "File not found: {$filename}.", 1);
            }
            return json::decode_file($filename);
        }
        /**
         * Run sstem diagnostic (check if required commands are available).
         * @return boolean
         */
        private static function diagnostic() {
            $valid = true;
            $require = config::select('mysli/assets', 'require', []);

            cout::info('Starting self test!');

            foreach ($require as $command => $require) {
                $res = [];
                cout::info('Test: `%s`.', $command);
                exec($command, $out, $res);
                if ($require !== true) {
                    if (arr::index(0, $out) &&
                        str::pos($out[0], $require) > -1) {
                        cout::warn(
                            'In: `%s`, expect: `%s`, got: `%s` .',
                            [$command, $require, $out[0]]);
                        $valid = false;
                    }
                } else {
                    if ($res !== 0) {
                        cout::warn('Missing: `%s`.', $command);
                        $valid = false;
                    }
                }
            }

            if (!$valid) {
                cout::warn('Some functions might not work properly.');
            } else {
                cout::success('All ok!');
            }

            return $valid;
        }
        /**
         * Parse command, replace variables with data.
         * @param  string $command
         * @param  string $source_file
         * @param  string $dest_file
         * @return string
         */
        private static function parse_command($command, $source_file,
                                              $dest_file) {
            return str::replace(
                ['{source}', '{dest}', '{source_dir}', '{dest_dir}'],
                [$source_file, $dest_file,
                fs\file::name($source_file), fs\file::name($dest_file)],
                $command
            );
        }
        /**
         * Grab multiple files, and merge them into one.
         * @param  array  $assets
         * @param  string $dir
         * @param  array  $changes
         * @return null
         */
        private static function assets_merge(array $assets, $dir,
                                             array $changes = []) {
            list($process, $compress) = config::get(
                'mysli/assets', ['process', 'compress'], []);

            foreach ($assets as $file => $assets_c) {
                $main_content = '';
                $processed = $changes ? 0 : 1;

                foreach ($assets_c as $asset) {
                    $ext = fs\file::extension($asset);
                    $source_file = fs::ds($dir, 'src', $asset);
                    $dest_file = fs::ds(
                        $dir, 'dist', self::parse_extention($asset));

                    if ($changes) {
                        if (!arr::key(fs::ds('/src', $asset), $changes)) {
                            continue;
                        } else {
                            cout::info(
                                'Processing: %s', fs::ds('/src', $asset));
                            $processed++;
                        }
                    }

                    if (!fs\file::exists($source_file)) {
                        cout::warn('File not found: %s.', $source_file);
                        continue;
                    }

                    if (!arr::key($ext, $process)) {
                        cout::warn(
                            'Unknown extention, cannot process: %s.', $ext);
                        continue;
                    }

                    // Excute action for file
                    system(self::parse_command(
                        $process[$ext], $source_file, $dest_file));
                    $main_content .= "\n\n" . fs\file::read($dest_file);
                }
            }

            if ($processed) {
                // Finally create asset file
                $asset_file = fs::ds($dir, 'dist', $file);
                $asset_ext  = fs\file::extension($asset_file);
                fs\file::write($asset_file, $main_content);

                // Compress(?)
                if (arr::key($asset_ext, $compress)) {
                    cout::info('Compress: %s.', basename($asset_file));
                    system(
                        self::parse_command(
                            $compress[$asset_ext], $asset_file,
                            $asset_file));
                }
            }
        }
        /**
         * Compare two lists of files, and return changes for each file.
         * @param  array   $one
         * @param  array   $two
         * @param  integer $cutoff how much of pth to remove (for pretty repots)
         * @return array
         */
        private static function what_changed(array $one, array $two,
                                             $cutoff = 0) {
            $changes = [];
            foreach ($one as $file => $hash) {
                if (!arr::key($file, $two)) {
                    $changes[str::slice($file, $cutoff)] = 'Added';
                } else {
                    if ($two[$file] !== $hash) {
                        $changes[str::slice($file, $cutoff)] = 'Updated';
                    }
                    unset($two[$file]);
                }
            }
            if (!arr::is_empty($two)) {
                foreach ($two as $file => $hash) {
                    $changes[str::slice($file, $cutoff)] = 'Removed';
                }
            }
            return $changes;
        }
        /**
         * Observe (and) build assets.
         * @param  string  $package
         * @param  string  $assets_file
         * @param  string  $assets_dir
         * @param  string  $web_dir
         * @param  boolean $loop
         * @return null
         */
        private static function observe_or_build($package, $assets_file,
                                                 $assets_dir, $web_dir, $loop) {
            if (!self::diagnostic()) {
                if (!cinput::confirm(
                    'Some components are missing. Continue anyway?')) {
                    cout::info('Canceled by user.');
                    return;
                }
            }

            if (!$package) {
                cout::warn('Please enter a valid package name.');
                return;
            }

            try {
                $assets = self::read_assets($package, $assets_file);
            } catch (exception\not_found $e) {
                cout::warn('File not found: %s.', $assets_file);
                return;
            }
            if (arr::valid($assets)) {
                cout::warn(
                    'Invalid file format: %s.', $assets_file);
                return;
            }

            $assets_path = fs::pkgpath($package, $assets_dir);
            if (!fs\dir::exists($assets_path)) {
                cout::warn(
                    'Assets path seems to be invalid. Cannot continue.');
                return;
            }

            if (!$web_dir) {
                $web_dir = $package;
            }
            $web_dir = web::path($web_dir);
            if (!fs\dir::exists($web_dir)) {
                cout::warn(
                    'Public web path seems to be invalid. Cannot continue.');
                return;
            }

            $signature = [];

            // Do we have @ commands
            if (isset($assets['@before'])) {
                $before = $assets['@before'];
                unset($assets['@before']);
                foreach ($before as $before_command) {
                    $command = self::parse_command(
                        $before_command,
                        fs::ds($assets_path, 'src/null'),
                        fs::ds($assets_path, 'dist/null'));
                    cout::info('Call: %s.', $command);
                    system($command);
                }
            }

            do {
                $rsignature = fs\dir::signature(fs::ds($assets_path, 'src'));
                if ($rsignature !== $signature) {
                    $changes = self::what_changed(
                        $rsignature, $signature, str::len($assets_path));
                    cout::info('What changed: %s', arr::readable($changes));
                    cout::info('Rebuilding assets...');
                    $signature = $rsignature;
                    self::assets_merge($assets, $assets_path, $changes);
                    fs\dir::copy($assets_path, $web_dir);
                } else {
                    $loop and sleep(3);
                }
            } while ($loop);
        }
    }
}
