<?php

namespace mysli\util\tplp;

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
    static function select($package, array $file=null, array $variables=null) {

        if (!isset(self::$cache[$package])) {
            self::$cache[$package] = new template($package);
        }

        $template = self::$cache[$package];

        return $file
                ? $template->render($file, $variables)
                : $template;
    }
}
