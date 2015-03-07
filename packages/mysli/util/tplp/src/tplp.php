<?php

namespace mysli\util\tplp;

__use(__namespace__, '
    mysli.framework.pkgm
    mysli.framework.fs/fs,dir
    mysli.framework.type/arr_path -> arrp
');

class tplp
{
    private static $cache = [];

    /**
     * Get template object for particular package or
     * render template for particular package.
     * @param  string $package
     * @param  string $file
     * @param  array  $variables
     * @return mixed  string (rendered template) or mysli\util\tplp\template
     */
    static function select($package, $file=null, array $variables=[])
    {
        if (!isset(self::$cache[$package]))
        {
            self::$cache[$package] = new template($package);
        }

        $template = self::$cache[$package];

        return $file
            ? $template->render($file, $variables)
            : $template;
    }
    /**
     * Get full source and destionation path for particular package.
     * @param  string $package
     * @return array  [$source, $destination]
     */
    static function get_paths($package)
    {
        $meta = pkgm::meta($package);
        $source = arrp::get($meta, 'tplp/source', 'tplp');
        $destination = arrp::get($meta, 'tplp/destination', '_dist/tplp');

        $source = fs::pkgreal($package, $source);
        $destination = fs::pkgreal($package, $destination);

        return [$source, $destination];
    }
}
