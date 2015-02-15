<?php

namespace mysli\util\tplp;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
');

class tplp {

    private static $cache = [];

    /**
     * Get template object for particular package or
     * render template for particular package.
     * @param  string $package
     * @param  string $file
     * @param  array  $variables
     * @return mixed  string (rendered template) or mysli\util\tplp\template
     */
    static function select($package, $file=null, array $variables=[]) {

        if (!isset(self::$cache[$package])) {
            self::$cache[$package] = new template($package);
        }

        $template = self::$cache[$package];

        return $file
                ? $template->render($file, $variables)
                : $template;
    }

    /**
     * Remova all cache templates for particular package.
     * @param  string $package
     * @return boolean
     */
    static function remove_cache($package) {
        $dir = fs::datpath('mysli/util/tplp/cache/'.str_replace('/', '.', $package));
        return dir::remove($dir);
    }
}
