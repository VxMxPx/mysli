<?php

namespace mysli\assets; class __tplp
{
    const __use = '
        .{ assets }
    ';

    /**
     * Get tag(s) for particular ID
     * @example
     *     {'dir/id'|assets.tag:'vendor.package'}
     * --
     * @param string $id
     * @param string $package
     * --
     * @return string
     */
    static function tag($id, $package)
    {
        return implode("\n", assets::get_tags($id, $package));
    }

    /**
     * Get only urls for particular package.
     * --
     * @example
     *     {'mysli.cms.blog/js/main.js'|assets/links}
     * --
     * @param string $id
     * @param string $package
     * --
     * @return array
     */
    static function url($id, $package)
    {
        return assets::get_links($id, $package);
    }
}
