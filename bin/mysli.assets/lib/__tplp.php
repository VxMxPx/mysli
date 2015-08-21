<?php

namespace mysli\assets; class __tplp
{
    const __use = '
        .{ assets }
    ';

    /**
     * {'mysli.cms.blog/js/main.js'|assets/tags}
     * @param  string $id
     * @return string
     */
    static function tags($id, $type=null)
    {
        list($package, $file) = self::resolve_id($id);
        return implode("\n", assets::get_tags($package, $file, $type));
    }
    /**
     * {'mysli.cms.blog/js/main.js'|assets/links}
     * @param  string $id
     * @return array
     */
    static function links($id, $type=null)
    {
        list($package, $file) = self::resolve_id($id);
        return assets::get_links($package, $file, $type);
    }

    /**
     * Resolve id, e.g.: vendor.package/path/file => [vendor.package, path/file]
     * @param  string $id
     * @return string
     */
    private static function resolve_id($id)
    {
        $seg = explode('/', $id, 2);
        return [$seg[0], isset($seg[1]) ? $seg[1] : null];
    }
}
