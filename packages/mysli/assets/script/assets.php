<?php

namespace Mysli\Assets\Script;

class Assets
{
    use \Mysli\Assets\Util;

    private $web;
    private $config;

    /**
     * Assign web.
     * --
     * @param object $web mysli/web
     */
    public function __construct($web, $config)
    {
        $this->web = $web;
        $this->config = $config;
    }

    /**
     * Print general help.
     */
    public function help_index()
    {
        \Cli\Util::doc(
            'Mysli Assets Builder',
            'assets <OPTION> [ARGUMENTS...]',
            [
                'observe <PACKAGE> [map.json FILE] [assets DIR] [public DIR]' =>
                    'Will observe files according to instructions in assets/map.json file.',
                'build   <PACKAGE> [map.json FILE] [assets DIR] [public DIR]' =>
                    'Build files according to instructions in assets/map.json file.'
            ]
        );

        return true;
    }


    /**
     * Observe particular directory for changes.
     * --
     * @param  string $package
     * @param  string $assets_file
     * @param  string $assets_dir
     * @param  string $web_dir
     * --
     * @return void
     */
    public function action_observe(
        $package = null,
        $assets_file = 'assets/map.json',
        $assets_dir = 'assets',
        $web_dir = null
    ) {
        $this->observe_or_build($package, $assets_file, $assets_dir, $web_dir, true);
    }

    /**
     * Build directory.
     * --
     * @param  string $package
     * @param  string $assets_file
     * @param  string $assets_dir
     * @param  string $web_dir
     * --
     * @return void
     */
    public function action_build(
        $package = null,
        $assets_file = 'assets/map.json',
        $assets_dir = 'assets',
        $web_dir = null
    ) {
        $this->observe_or_build($package, $assets_file, $assets_dir, $web_dir, false);
    }

    /**
     * Command not found.
     * --
     * @param  string $command
     * --
     * @return null
     */
    public function not_found($command)
    {
        \Cli\Util::warn('Command not found: `' . $command . '`. Try to use `observe` or `build`.');
    }

    /**
     * Get assets instruction from _assets.json_ file.
     * --
     * @param  string $package
     * @param  string $assets
     * --
     * @return mixed  Array on success, false on failure.
     */
    private function read_assets($package, $assets)
    {
        $filename = pkgpath($package, $assets);

        if (!file_exists($filename)) { return false; }
        else return \Core\JSON::decode_file($filename, true);
    }

    /**
     * Check if we have: stylus, uglify, and cat.
     * --
     * @return boolean
     */
    private function self_test()
    {
        $valid = true;
        \Cli\Util::plain('Starting self test!');

        foreach ($this->config->get('require') as $command => $require) {
            $res = [];
            \Cli\Util::plain('Test: `' . $command . '`.');

            exec($command, $out, $res);

            if ($require !== true) {
                if (isset($out[0]) && strpos($out[0], $require) === false) {
                    \Cli\Util::warn('In: `' . $command . '`, expect: `' . $require . '`, got: `' . $out[0] . '` .');
                    $valid = false;
                }
            } else {
                if ($res !== 0) {
                    \Cli\Util::warn('Missing: `' . $command . '`.');
                    $valid = false;
                }
            }
        }

        if (!$valid) {
            \Cli\Util::warn('Some functions might not work properly.');
        } else {
            \Cli\Util::success('All ok!');
        }

        return $valid;

        // exec('stylus --version',   $stylus);
        // exec('uglifyjs --version', $uglify);

        // if (empty($stylus) || !$stylus[0]) {
        //     \Cli\Util::warn('Missing: stylus, some functions might not work properly.');
        //     $valid = false;
        // }
        // if (empty($uglify) || !$uglify[0]) {
        //     \Cli\Util::warn('Missing: uglifyjs, some functions might not work properly.');
        //     $valid = false;
        // }

        // return $valid;
    }

    /**
     * Parse cli command.
     * --
     * @param  string $command
     * @param  string $source_file
     * @param  string $dest_file
     * --
     * @return string
     */
    private function parse_command($command, $source_file, $dest_file)
    {
        return str_replace(
            ['{source}', '{dest}', '{source_dir}', '{dest_dir}'],
            [$source_file, $dest_file, dirname($source_file), dirname($dest_file)],
            $command
        );
    }

    /**
     * Parse assets using external programs.
     * --
     * @param  array  $assets
     * @param  string $dir
     * @param  array  $changes
     * --
     * @return null
     */
    private function assets_merge($assets, $dir, array $changes = [])
    {
        $process  = $this->config->get('process', []);
        $compress = $this->config->get('compress', []);

        foreach ($assets as $file => $assets_c) {

            $main_content = '';
            $processed = $changes ? 0 : 1;

            foreach ($assets_c as $asset) {

                $ext = \Core\FS::file_extension($asset);
                $source_file = ds($dir, 'src', $asset);
                $dest_file = ds($dir, 'dist', $this->convert_ext($asset));

                if ($changes) {
                    if (!isset($changes[ds('/src', $asset)])) {
                        // \Cli\Util::plain('Skip: ' . ds('/src', $asset));
                        continue;
                    } else {
                        \Cli\Util::plain('Processing: ' . ds('/src', $asset));
                        $processed++;
                    }
                }

                if (!file_exists($source_file)) {
                    \Cli\Util::warn('File not found: ' . $source_file);
                    continue;
                }

                if (!isset($process[$ext])) {
                    \Cli\Util::warn('Unknown extention, cannot process: ' . $ext);
                    continue;
                }

                // Excute action for file!
                system($this->parse_command($process[$ext], $source_file, $dest_file));
                $main_content .= "\n\n" . file_get_contents($dest_file);

                // if (substr($file, -3) === '.js') {
                //     // Rewrite itself
                //     system('uglifyjs -c -o ' . $dist_file . ' ' . $source_file);
                //     $all_contents .= file_get_contents($dist_file);
                // } else {
                //     if (substr($source_file, 0, -4) === '.css') {
                //         copy($source_file, $dist_file);
                //         continue;
                //     }
                //     system('stylus -c ' . $source_file . ' -o ' . dirname($dist_file));
                //     $all_contents .= file_get_contents($dist_file);
                // }
            }

            if ($processed) {
                // Finally create asset file
                $asset_file = ds($dir, 'dist', $file);
                $asset_ext  = \Core\FS::file_extension($asset_file);
                file_put_contents($asset_file, $main_content);

                // Compress(?)
                if (isset($compress[$asset_ext])) {
                    \Cli\Util::plain('Compress: ' . basename($asset_file));
                    system($this->parse_command($compress[$asset_ext], $asset_file, $asset_file));
                }
            } else {
                // \Cli\Util::plain('Compress: no changes.');
            }
        }


        // $contents = [];
        // foreach ($assets as $file => $assets) {
        //     foreach ($assets as $asset) {
        //         $filename = ds($dir, 'src', $asset);
        //         if (!file_exists($filename)) {
        //             \Cli\Util::warn('File not found: ' . $filename);
        //             continue;
        //         }
        //         $content = file_get_contents($filename);
        //         $contents[$file] = isset($contents[$file]) ? $contents[$file] . $content : $content;
        //     }
        // }
        // foreach ($contents as $file => $content) {
        //     $filename = ds($dir, 'dist', $file);
        //     if (file_put_contents($filename, $content) !== false) {
        //         if (substr($file, -3) === '.js') {
        //             // Rewrite itself
        //             system('uglifyjs -c -o ' . $filename . ' ' . $filename);
        //         } else {
        //             $out = [];
        //             exec('cat ' . $filename . ' | stylus -c', $out);
        //             file_put_contents($filename, implode('', $out));
        //         }
        //         \Cli\Util::success('Merged: ' . $file);
        //     } else {
        //         \Cli\Util::error('Failed to create: ' . $file);
        //     }
        // }
    }


    /**
     * What are the changes between two directories
     * (diff \Core\FS::dir_signatures).
     * --
     * @param  array   $one
     * @param  array   $two
     * @param  integer $cutoff -- Portion of path to be removed for readability purposes.
     * --
     * @return array
     */
    private function what_changed(array $one, array $two, $cutoff = 0) {
        $changes = [];
        foreach ($one as $file => $hash) {
            if (!isset($two[$file])) {
                $changes[substr($file, $cutoff)] = 'Added';
            } else {
                if ($two[$file] !== $hash) {
                    $changes[substr($file, $cutoff)] = 'Updated';
                }
                unset($two[$file]);
            }
        }
        if (!empty($two)) {
            foreach ($two as $file => $hash) {
                $changes[substr($file, $cutoff)] = 'Removed';
            }
        }
        return $changes;
    }

    /**
     * Common method for observing and/or building.
     * --
     * @param  string  $package
     * @param  string  $assets_file
     * @param  string  $assets_dir
     * @param  string  $web_dir
     * @param  boolean $loop
     * --
     * @return void
     */
    private function observe_or_build($package, $assets_file, $assets_dir, $web_dir, $loop)
    {
        // Test if all components needed are present.
        if (!$this->self_test()) {
            if (!\Cli\Util::confirm('Some components are missing. Continue anyway?')) {
                return;
            }
        }

        if (!$package) {
            \Cli\Util::warn('Please enter a valid package name.');
            return;
        }

        $assets = $this->read_assets($package, $assets_file);
        if (!$assets || !is_array($assets)) {
            \Cli\Util::warn('Invalid assets directory or invalid file format.');
            return;
        }

        $assets_path = pkgpath($package, $assets_dir);
        if (!file_exists($assets_path) || !is_dir($assets_path)) {
            \Cli\Util::warn('Assets path seems to be invalid. Cannot continue.');
            return;
        }

        if (!$web_dir) {
            $web_dir = $package;
        }
        $web_dir = $this->web->path($web_dir);
        if (!file_exists($web_dir) || !is_dir($web_dir)) {
            \Cli\Util::warn('Public web path seems to be invalid. Cannot continue.');
            return;
        }

        $signature = [];

        do {
            $rsignature = \Core\FS::dir_signatures(ds($assets_path, 'src'));
            if ($rsignature !== $signature) {
                $changes = $this->what_changed($rsignature, $signature, strlen($assets_path));
                \Cli\Util::plain("What changed: \n" . \Core\Arr::readable($changes));
                \Cli\Util::plain('Rebuilding assets...');
                $signature = $rsignature;
                $this->assets_merge($assets, $assets_path, $changes);
                \Core\FS::dir_copy(
                    $assets_path,
                    $web_dir,
                    \Core\FS::EXISTS_REPLACE
                );
            } else $loop and sleep(3);
        } while ($loop);
    }
}
