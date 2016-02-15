<?php

namespace mysli\assets; class __tplp
{
    const __use = '
        .{ assets }
    ';

    /**
     * Get tag(s) for particular ID
     * --
     * @example
     *     { 'dir/id' | assets.tags : 'vendor.package' }
     * --
     * @param string $id
     * @param string $package
     * --
     * @return string
     */
    static function tags($id, $package)
    {
        return implode("\n", assets::get_tags($id, $package));
    }

    /**
     * Get only urls for particular package.
     * --
     * @example
     *     { 'js/' | assets.urls : 'vendor.package' }
     * --
     * @param string $id
     * @param string $package
     * --
     * @return array
     */
    static function urls($id, $package)
    {
        return assets::get_links($id, $package);
    }

    /**
     * Get url to only one specific file.
     * --
     * @param string $filename
     * @param string $package
     * --
     * @return string
     */
    static function file($filename, $package)
    {
        return "/assets/{$package}/{$filename}";
    }
}
