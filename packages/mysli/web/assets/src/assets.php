<?php

namespace mysli\web\assets;

__use(__namespace__,
    ['mysli/framework/exception/*' => 'framework/exception/%s'],
    'mysli/framework/fs/{fs,file,dir}',
    'mysli/util/config',
    'mysli/web/web'
);

class assets {

    use util;

    /**
     * Publish assets for particular package.
     * @param  string $package
     * @return boolean
     */
    static function publish($package, $dist='_dist/assets') {
        $srcpath  = fs::pkgpath($package, $dist);
        $id = str_replace('/', '_', $package);
        $destpath = web::path($id);

        if (!dir::exists($srcpath)) {
            throw new framework\exception\not_found(
                "Cannot publish -- folder not found: `{$srcpath}`", 1);
        }

        if (!dir::exists($destpath)) {
            dir::create($destpath);
        }

        return dir::copy($srcpath, $destpath);
    }
    /**
     * Destroy (delete) published assets for package.
     * @param  string $package
     * @return boolean
     */
    static function destroy($package) {
        $id = str_replace('/', '_', $package);
        $path = web::path($id);
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
