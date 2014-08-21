<?php

namespace mysli\assets {

    use mysli\fs as fs;
    use mysli\str as str;
    use mysli\arr as arr;
    use mysli\web as web;
    use mysli\core as core;
    use mysli\json as json;
    use mysli\fs\file as file;
    use mysli\config as config;

    class assets {

        use mysli\assets\util;

        /**
         * Produce HTML tag for style or script.
         * @param  string $type
         * @param  string $pkg
         * @param  string $file
         * @return string
         */
        private static function make_tag($type, $pkg, $file) {
            $url = web::url($pkg . '/' . 'dist' . '/' . $file);
            if ($type === 'css') {
                return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
            } else {
                return '<script src="' . $url . '"></script>';
            }
        }
        /**
         * Get all HTML tags, for particular package(s).
         * @param  string $type js/css
         * @param  string $list vendor/package,vendor/package
         * @return string
         */
        public static function get_tags($type, $list) {
            $debug = config::select('mysli/assets', 'debug');
            $list = explode(',', $list);
            $collection = [];

            foreach ($list as $pkg) {
                if (str::find($pkg, ':') !== false) {
                    $allowed = explode(':', $pkg);
                    $pkg = $allowed[0];
                    $allowed = array_slice($allowed, 1);
                } else {
                    $allowed = false;
                }

                $filename = fs::pkgpath($pkg, 'assets/map.json');

                if (!file::exists($filename)) {
                    throw new core\exception\not_found(
                        "File not found: `{$filename}`.", 1);
                }

                $assets = json::decode_file($filename);

                foreach ($assets as $asset_main => $asset_files) {
                    if ($allowed &&
                        !arr::key_in($allowed, file::name($asset_main))) {
                        continue;
                    }
                    if (str::slice($asset_main, -(str::length($type))) !== $type) {
                        continue;
                    }

                    if (!$debug) {
                        $collection[] = self::make_tag($type, $pkg, $asset_main);
                    } else
                        foreach ($asset_files as $asset_file) {
                            $asset_file = self::parse_extention($asset_file);
                            $collection[] = self::make_tag(
                                $type,
                                $pkg,
                                $asset_file
                            );
                        }
                }
            }
            return implode("\n", $collection);
        }
    }
}
