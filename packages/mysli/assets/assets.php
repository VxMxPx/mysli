<?php

namespace Mysli\Assets;

class Assets
{
    use \Mysli\Core\Pkg\Singleton;
    use Util;

    private $web;
    private $config;


    public function __construct(\Mysli\Web\Web $web,
                                \Mysli\Config\Config $config)
    {
        $this->web = $web;
        $this->config = $config;
    }

    /**
     * Make one tag.
     * --
     * @param  sring   $type  - js, css
     * @param  string  $pkg   - vendor/package
     * @param  string  $file  - css/my_file.css
     * --
     * @return string
     */
    private function make_tag($type, $pkg, $file)
    {
        $url = $this->web->url($pkg . '/dist/' . $file);
        if ($type === 'css') {
            return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
        } else {
            return '<script src="' . $url . '"></script>';
        }
    }

    /**
     * Get all tags of particular type.
     * --
     * @param  string $type     - css, js
     * @param  string $packages - vendor/package,vendor/package
     * --
     * @return string
     */
    public function get_tags($type, $packages)
    {
        $debug = $this->config->get('debug');
        $packages = explode(',', $packages);
        $collection = [];

        foreach ($packages as $pkg) {

            if (strpos($pkg, ':') !== false) {
                $allowed = explode(':', $pkg);
                $pkg = $allowed[0];
                $allowed = array_slice($allowed, 1);
            } else $allowed = false;

            $filename = pkgpath($pkg, 'assets/map.json');

            if (!file_exists($filename))
                throw new \Core\NotFoundException("File not found: `{$filename}`.", 1);

            $assets = \Core\JSON::decode_file($filename, true);

            foreach ($assets as $asset_main => $asset_files) {

                if ($allowed && !in_array(basename($asset_main), $allowed)) continue;
                if (substr($asset_main, -(strlen($type))) !== $type) continue;

                if (!$debug)
                    $collection[] = $this->make_tag($type, $pkg, $asset_main);
                else
                    foreach ($asset_files as $asset_file) {
                        $asset_file = $this->convert_ext($asset_file);
                        $collection[] = $this->make_tag(
                            $type,
                            $pkg,
                            $asset_file
                        );
                    }
            }
        }
        return implode("\n", $collection);
    }

    /**
     * This will get script URL (if script exists)
     * If in debug mode, this will grab individual script. If not debug,
     * this will return url to pack.
     * --
     * @param string $script  Example: mysli/ui/button
     *                        debug: http://.../mysli/ui/dist/button.js
     *                        production: http://.../mysli/ui/dist/ui.js
     * --
     * @return string
     */
    public function get_script_url($script)
    {
        $script_segments = explode('/', $script);
        $pkg = implode('/', array_slice($script_segments, 0, 2));

        $script = 'js/' . (isset($script_segments[2])
            ? $script_segments[2]
            : $script_segments[1]) . '.js';

        $map_file = pkgpath($pkg, 'assets/map.json');
        if (!file_exists($map_file)) return;
        $map = \Core\JSON::decode_file($map_file, true);
        $fscripts = false;

        foreach ($map as $master_file => $files) {
            if ($script === $master_file) {
                $fscripts = $this->config->get('debug')
                    ? [$master_file]
                    : $files;
                break;
            }
            foreach ($files as $file)
                if ($script === $file) {
                    $fscripts = [$this->config->get('debug')
                                ? $file
                                : $master_file];
                    break 2;
                }
        }

        if (!$fscripts) return;

        return array_map(function ($element) use ($pkg) {
            $this->web->url($pkg . '/dist/' . $element);
        }, $fscripts);
    }
}
