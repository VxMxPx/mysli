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
