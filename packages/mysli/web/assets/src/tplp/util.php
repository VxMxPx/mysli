<?php

namespace mysli\web\asses\tplp;

__use(__namespace__,
    'mysli/framework/fs/file',
    ['mysli/framework/exception/*' => 'framework/exception/%s'],
    './assets',
    '../web'
);

class util {
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

        // Get package name
        if (file::exists(web::path(implode('_', array_slice($seg, 0, 3))))) {
            $package = implode('/', array_slice($seg, 0, 3));
            $file = implode('/', array_slice($seg, 3));
        } elseif (file::exists(implode('_', array_slice($seg, 0, 2)))) {
            $package = implode('/', array_slice($seg, 0, 2));
            $file = implode('/', array_slice($seg, 2));
        } else {
            throw new framework\exception\not_found("File not found: `{$id}`");
        }

        return [$package, ($file?:null)];
    }
}
