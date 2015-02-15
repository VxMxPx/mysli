<?php

namespace mysli\web\assets\tplp;

__use(__namespace__, '
    mysli.framework.exception/* as framework\exception\*
    mysli.framework.fs/file
    mysli.web.web
    mysli.web.assets
');

class util {

    private static $web_dir = 'assets';

    /**
     * {'mysli/cms/blog/js/main.js'|assets/tags}
     * @param  string $id
     * @return string
     */
    static function tags($id, $type=null) {
        list($package, $file) = self::resolve_id($id);
        return implode("\n", assets::get_tags($package, $file, $type));
    }
    /**
     * {'mysli/cms/blog/js/main.js'|assets/links}
     * @param  string $id
     * @return array
     */
    static function links($id, $type=null) {
        list($package, $file) = self::resolve_id($id);
        return assets::get_links($package, $file, $type);
    }

    /**
     * Resolve id, e.g.: vendor/package/path/file => [vendor/package, path/file]
     * @param  string $id
     * @return string
     */
    private static function resolve_id($id) {

        $seg = explode('/', $id);
        $wp1 = web::path(self::$web_dir, implode('_', array_slice($seg, 0, 3)));
        $wp2 = web::path(self::$web_dir, implode('_', array_slice($seg, 0, 2)));

        // Get package name
        if (file::exists($wp1)) {
            $package = implode('/', array_slice($seg, 0, 3));
            $file = implode('/', array_slice($seg, 3));
        } elseif (file::exists($wp2)) {
            $package = implode('/', array_slice($seg, 0, 2));
            $file = implode('/', array_slice($seg, 2));
        } else {
            throw new framework\exception\not_found("File not found: `{$id}`");
        }

        return [$package, ($file ?: null)];
    }
}
