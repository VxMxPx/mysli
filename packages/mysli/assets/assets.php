<?php

namespace Mysli\Assets;

class Assets
{
    private $web;
    private $config;

    /**
     * @param object $web    mysli/web
     * @param object $config mysli/config
     */
    public function __construct($web, $config)
    {
        $this->web = $web;
        $this->config = $config;
    }

    /**
     * Make one tag.
     * --
     * @param  sring   $type  - js, css
     * @param  boolean $debug
     * @param  string  $pkg   - vendor/package
     * @param  string  $file  - css/my_file.css
     * --
     * @return string
     */
    private function make_tag($type, $debug, $pkg, $file)
    {
        $url = $this->web->url($pkg . '/' . ($debug ? 'src' : 'dist') . '/' . $file);
        if ($type === 'css')
            return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
        else
            return '<script src="' . $url . '"></script>';
    }

    /**
     * Get all tags of particular type.
     * --
     * @param  string $type - css, js
     * @param  string $list - vendor/package,vendor/package
     * --
     * @return string
     */
    private function get_tags($type, $list)
    {
        $debug = $this->config->get('debug');
        $list = explode(',', $list);
        $collection = [];

        foreach ($list as $pkg) {

            if (strpos($pkg, ':') !== false) {
                $allowed = explode(':', $pkg);
                $pkg = $allowed[0];
                $allowed = array_slice($allowed, 1);
            } else $allowed = false;

            $filename = pkgpath($pkg, 'assets.json');

            if (!file_exists($filename))
                throw new \Core\NotFoundException("File not found: `{$filename}`.", 1);

            $assets = \Core\JSON::decode_file($filename, true);

            foreach ($assets as $asset_main => $asset_files) {

                if ($allowed && !in_array(basename($asset_main), $allowed)) continue;
                if (substr($asset_main, -(strlen($type))) !== $type) continue;

                if (!$debug || $type === 'css')
                    $collection[] = $this->make_tag($type, $debug, $pkg, $asset_main);
                else
                    foreach ($asset_files as $asset_file)
                        $collection[] = $this->make_tag($type, $debug, $pkg, $asset_file);
            }
        }
        return implode("\n", $collection);
    }

    /**
     * Register template's global functions.
     * --
     * @param  object $tplp mysli/tplp
     * --
     * @return null
     */
    public function register($tplp)
    {
        $tplp->register_function('css_assets', function ($list) {
            return $this->get_tags('css', $list);
        });
        $tplp->register_function('javascript_assets', function ($list) {
            return $this->get_tags('js', $list);
        });
    }
}
