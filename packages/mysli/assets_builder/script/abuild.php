<?php

namespace Mysli\AssetsBuilder\Script;

class Abuild
{
    private $web;

    /**
     * Assign web.
     * --
     * @param object $web mysli/web
     */
    public function __construct($web)
    {
        $this->web = $web;
    }

    /**
     * Print general help.
     */
    public function help_index()
    {
        \Cli\Util::doc(
            'Mysli Assets Builder',
            'abuild <OPTION> [ARGUMENTS...]',
            [
                'observe <PACKAGE> [assets.json FILE] [assets DIR] [public DIR]' =>
                    'Will observe files according to instructions in assets.json file.',
                'build   <PACKAGE> [assets.json FILE] [assets DIR] [public DIR]' =>
                    'Build files according to instructions in assets.json file.'
            ]
        );

        return true;
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
     * What are the changes between two directories
     * (diff \Core\FS::dir_signatures).
     * --
     * @param  array $one
     * @param  array $two
     * --
     * @return array
     */
    private function what_changed(array $one, array $two) {
        $changes = [];
        foreach ($one as $file => $hash) {
            if (!isset($two[$file])) {
                $changes[] = 'Added: ' . $file;
            } else {
                if ($two[$file] !== $hash) {
                    $changes[] = 'Updated: ' . $file;
                }
                unset($two[$file]);
            }
        }
        if (!empty($two)) {
            foreach ($two as $file => $hash) {
                $changes[] = 'Removed: ' . $file;
            }
        }
        return $changes;
    }

    /**
     * Check if we have: stylus, uglify, and cat.
     * --
     * @return boolean
     */
    private function self_test()
    {
        $valid = true;

        exec('stylus --version',   $stylus);
        exec('uglifyjs --version', $uglify);
        exec('cat --version',      $cat);

        if (empty($stylus) || !$stylus[0]) {
            \Cli\Util::warn('Missing: stylus, some functions might not work properly.');
            $valid = false;
        }
        if (empty($uglify) || !$uglify[0]) {
            \Cli\Util::warn('Missing: uglifyjs, some functions might not work properly.');
            $valid = false;
        }
        if (empty($cat) || strpos($cat[0], 'GNU') === false) {
            \Cli\Util::warn('Missing: cat, some functions might not work properly.');
            $valid = false;
        }

        return $valid;
    }

    /**
     * Parse assets using external programs.
     * --
     * @param  array  $assets
     * @param  string $dir
     * --
     * @return null
     */
    private function assets_merge($assets, $dir)
    {
        $contents = [];
        foreach ($assets as $file => $assets) {
            foreach ($assets as $asset) {
                $filename = ds($dir, 'src', $asset);
                if (!file_exists($filename)) {
                    \Cli\Util::warn('File not found: ' . $filename);
                    continue;
                }
                $content = file_get_contents($filename);
                $contents[$file] = isset($contents[$file]) ? $contents[$file] . $content : $content;
            }
        }
        foreach ($contents as $file => $content) {
            $filename = ds($dir, 'dist', $file);
            if (file_put_contents($filename, $content) !== false) {
                \Cli\Util::success('Merged: ' . $file);
                if (substr($file, -3) === '.js') {
                    // Rewrite itself
                    system('uglifyjs -c -o ' . $filename . ' ' . $filename);
                } else {
                    $out = [];
                    exec('cat ' . $filename . ' | stylus -c', $out);
                    file_put_contents($filename, implode('', $out));
                }
            } else {
                \Cli\Util::error('Failed to create: ' . $file);
            }
        }
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
                \Cli\Util::plain("What changed: \n" . \Core\Arr::readable(
                    $this->what_changed($rsignature, $signature)
                ));
                \Cli\Util::plain('Rebuilding assets...');
                $signature = $rsignature;
                $this->assets_merge($assets, $assets_path);
                \Core\FS::dir_copy(
                    $assets_path,
                    $web_dir,
                    \Core\FS::EXISTS_REPLACE
                );
            } else $loop and sleep(3);
        } while ($loop);
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
        $assets_file = 'assets.json',
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
        $assets_file = 'assets.json',
        $assets_dir = 'assets',
        $web_dir = null
    ) {
        $this->observe_or_build($package, $assets_file, $assets_dir, $web_dir, false);
    }
}
