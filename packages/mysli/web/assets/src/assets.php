<?php

namespace mysli\web\assets;

__use(__namespace__,
    ['mysli/framework/exception/*' => 'framework/exception/%s'],
    'mysli/util/config'
);

class assets {

    use util;

    /**
     * Get links for particular file/package
     * @param  string $package
     * @param  string $file    if null, all files will be returned
     * @param  string $filter  if file is null, you can filter links by type,
     *                         allowed values: css|js
     * @return array
     */
    static function get_links($package, $file=null, $filter=null) {
        $id = str_replace('/', '_', $package);
        if (!file::exists(web::path($id))) {
            throw new framework\exception\not_found(
                "Public folder not found: `{$id}`", 1);
        }
        $map = self::get_map($package);
        $debug = config::select('mysli/web/assets', 'debug', false);
        $links = [];
        foreach ($map['files'] as $main => $props) {
            if ($file && $file !== $main) {
                continue;
            }
            if (!$debug) {
                $links[] = web::url($id, $main);
            } else {
                foreach ($props['include'] as $asset) {
                    $asset = self::parse_extention($asset,
                                                   $map['settings']['ext']);
                    if (!$filter ||
                    substr($asset, -(strlen($filter))) === $filter) {
                        $links[] = web::url($id, $asset);
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
            if (substr($link, 0, -3) === '.js') {
                $tags[] = '<script src="'.$link.'"></script>';
            } elseif (substr($link, 0, -4) === '.css') {
                $tags[] =
                    '<link rel="stylesheet" type="text/css" href="'.$link.'">';
            } else {
                throw new framework\exception\argument(
                    "Unknown file extension: `{$link}`");
            }
        }

        return $tags;
    }
}


//     \inject::to(__namespace__)
//     ->from('mysli/web')
//     ->from('mysli/json')
//     ->from('mysli/config')
//     ->from('mysli/core/type/str')
//     ->from('mysli/fs');

//     class assets {

//         use mysli\assets\util;

//         // /**
//         //  * Produce HTML tag for style or script.
//         //  * @param  string $type
//         //  * @param  string $pkg
//         //  * @param  string $file
//         //  * @return string
//         //  */
//         // static function make_tag($type, $pkg, $file) {
//         //     $url = web::url($pkg . '/' . 'dist' . '/' . $file);
//         //     if ($type === 'css') {
//         //         return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
//         //     } else {
//         //         return '<script src="' . $url . '"></script>';
//         //     }
//         // }
//         // /**
//         //  * Get all HTML tags, for particular package(s).
//         //  * @param  string $type js/css
//         //  * @param  string $list vendor/package,vendor/package
//         //  * @return string
//         //  */
//         // static function get_tags($type, $list) {
//         //     $debug = config::select('mysli/assets', 'debug');
//         //     $list = explode(',', $list);
//         //     $collection = [];

//         //     foreach ($list as $pkg) {
//         //         if (str::find($pkg, ':') !== false) {
//         //             $allowed = explode(':', $pkg);
//         //             $pkg = $allowed[0];
//         //             $allowed = array_slice($allowed, 1);
//         //         } else {
//         //             $allowed = false;
//         //         }

//         //         $filename = fs::pkgpath($pkg, 'assets/map.json');

//         //         if (!fs\file::exists($filename)) {
//         //             throw new base\exception\not_found(
//         //                 "File not found: `{$filename}`.", 1);
//         //         }

//         //         $assets = json::decode_file($filename);

//         //         foreach ($assets as $asset_main => $asset_files) {
//         //             if ($allowed &&
//         //                 !base\arr::key_in($allowed, fs\file::name($asset_main))) {
//         //                 continue;
//         //             }
//         //             if (str::slice($asset_main, -(str::length($type))) !== $type) {
//         //                 continue;
//         //             }

//         //             if (!$debug) {
//         //                 $collection[] = self::make_tag($type, $pkg, $asset_main);
//         //             } else
//         //                 foreach ($asset_files as $asset_file) {
//         //                     $asset_file = self::parse_extention($asset_file);
//         //                     $collection[] = self::make_tag(
//         //                         $type,
//         //                         $pkg,
//         //                         $asset_file
//         //                     );
//         //                 }
//         //         }
//         //     }
//         //     return implode("\n", $collection);
//         // }
//     }
// }
