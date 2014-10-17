<?php

namespace mysli\web\assets;

__use(__namespace__,
    'mysli/framework/ym',
    'mysli/framework/json',
    'mysli/framework/type/arr',
    'mysli/framework/fs/{fs,file}',
    ['mysli/framework/exception/*' => 'framework/exception/%s']
);

trait util {

    private static $cache = [];

    /**
     * Get parsed ext, e.g.: stly => css
     * @param  string $file
     * @return string
     */
    private static function parse_extention($file, array $exts=[]) {
        $ext  = file::extension($file);
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
    private static function get_map($package, $assets, $map) {

        if (!isset(self::$cache[$package])) {
            $filename = fs::pkgpath($package, $assets, $map);
            if (!file::exists($filename)) {
                throw new framework\exception\not_found(
                    "Map file not found: {$filename}.", 1);
            }
            if (substr($map, -3) === '.ym') {
                return ym::decode_file($filename);
            } elseif (substr($map, -5) === '.json') {
                return json::decode_file($filename);
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
            self::$cache['$package'] = arr::merge($map_default, $map);
        }

        return self::$cache[$package];
    }
}
