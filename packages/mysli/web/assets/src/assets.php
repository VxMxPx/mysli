<?php

namespace mysli\web\assets;

class assets {

    private static $web_dir = 'assets';
    private static $cache = [];

    /**
     * Get parsed ext, e.g.: stly => css
     * @param  string $file
     * @return string
     */
    static function parse_extention($file, array $exts=[]) {
        $ext = file::extension($file);
        if (arr::key_in($exts, $ext)) {
            return substr($file, 0, -(strlen($ext))) . $exts[$ext];
        } else {
            return $file;
        }
    }
    /**
     * Read assets file for particular package.
     * @param  string $package
     * @param  string $assets relative folder path
     * @param  string $map filename
     * @return array
     */
    static function get_map($package, $assets, $map) {

        if (!isset(self::$cache[$package])) {
            $filename = fs::pkgpath($package, $assets, $map);
            if (!file::exists($filename)) {
                throw new framework\exception\not_found(
                    "Map file not found: {$filename}.", 1);
            }
            if (substr($map, -3) === '.ym') {
                $map = ym::decode_file($filename);
            } elseif (substr($map, -5) === '.json') {
                $map = json::decode_file($filename);
            } else {
                throw new framework\exception\argument(
                    "Invalid file type: `{$map}`", 1);
            }

            // Valid map?
            if (!is_array($map) || !isset($map['files'])) {
                throw new framework\exception\data(
                    'Invalid map format `files` section is required.');
            }

            // Default settings
            $map_default = ym::decode_file(
                fs::pkgpath('mysli/web/assets/data/defaults.ym'));

            // Merge with map!
            self::$cache[$package] = arr::merge($map_default, $map);
        }

        return self::$cache[$package];
    }
    /**
     * Publish assets for particular package.
     * @param  string $package
     * @param  string $dist
     * @return boolean
     */
    static function publish($package, $dist=null) {
        if (!$dist) {
            list($_, $dist, $_ ) = self::get_default_paths($package);
        }
        $dist  = fs::pkgpath($package, $dist);
        $id = self::get_id($package);
        $webpath = web::path(self::$web_dir, $id);

        if (!dir::exists($dist)) {
            throw new framework\exception\not_found(
                "Cannot publish -- folder not found: `{$dist}`", 1);
        }

        if (!dir::exists($webpath)) {
            dir::create($webpath);
        }

        return dir::copy($dist, $webpath);
    }
    /**
     * Destroy (delete) published assets for package.
     * @param  string $package
     * @return boolean
     */
    static function destroy($package) {
        $id = self::get_id($package);
        $path = web::path(self::$web_dir, $id);

        if (dir::exists($path)) {
            return dir::remove($path);
        } else {
            return false;
        }
    }
    /**
     * Get links for particular file/package
     * @param  string $package
     * @param  string $file    if null, all files will be returned
     * @param  string $filter  if file is null, you can filter links by type,
     *                         allowed values: css|js
     * @return array
     */
    static function get_links($package, $file=null, $filter=null) {

        $id = self::get_id($package);

        if (!file::exists(web::path('assets', $id))) {
            throw new framework\exception\not_found(
                "Public folder not found: `{$id}`", 1);
        }

        list($source, $_, $map) = self::get_default_paths($package);

        $map = self::get_map($package, $source, $map);
        $debug = config::select('mysli/web/assets', 'debug', false);
        $links = [];

        foreach ($map['files'] as $main => $props) {

            if ($file && $file !== $main) {
                continue;
            }

            if (!$debug) {
                $links[] = web::url(self::$web_dir."/{$id}/{$main}");
            } else {

                foreach ($props['include'] as $asset) {
                    $asset = self::parse_extention(
                                $asset, $map['settings']['ext']);

                    if (!$filter ||
                    substr($asset, -(strlen($filter))) === $filter) {
                        $links[] = web::url(self::$web_dir."/{$id}/{$asset}");
                    }
                }

            }
        }

        return $links;
    }
    /**
     * Return an array containing HTML tags for particular file / package
     * @param  string $package
     * @param  string $file    if null, tags for all files will be returned
     * @param  string $filter  if file is null, you can filter links by type,
     *                         allowed values: css|js
     * @return array
     */
    static function get_tags($package, $file=null, $filter=null) {

        $links = self::get_links($package, $file, $filter);
        $tags = [];

        foreach ($links as $link) {
            if (substr($link, -3) === '.js') {
                $tags[] = '<script src="'.$link.'"></script>';
            } elseif (substr($link, -4) === '.css') {
                $tags[] =
                    '<link rel="stylesheet" type="text/css" href="'.$link.'">';
            } else {
                throw new framework\exception\argument(
                    "Unknown file extension: `{$link}`");
            }
        }

        return $tags;
    }
    /**
     * Get ID for particular package
     * @param  string $package
     * @return string
     */
    static function get_id($package) {
        return str_replace('/', '_', $package);
    }
    /**
     * Get default paths, define in mysli.pkg.ym
     * @param  string $package
     * @return array  [source, dest, map]
     */
    static function get_default_paths($package) {
        $meta = pkgm::meta($package);

        $source = null;
        $dest   = null;
        $map    = null;

        if (isset($meta['assets'])) {
            if (isset($meta['assets']['source'])) {
                $source = $meta['assets']['source'];
            }
            if (isset($meta['assets']['dest'])) {
                $dest = $meta['assets']['dest'];
            }
            if (isset($meta['assets']['map'])) {
                $map = $meta['assets']['map'];
            }
        }

        return [$source, $dest, $map];
    }
}
