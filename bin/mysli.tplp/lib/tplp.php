<?php

namespace mysli\tplp; class tplp
{
    const __use = '
        .{ exception.tplp }
        mysli.toolkit.{
            pkg,
            fs.fs  -> fs,
            fs.dir -> dir,
            type.arr_path -> arr_path
        }
    ';

    /**
     * Cache template objects.
     * --
     * @var array
     */
    private static $cache = [];

    /**
     * Get template object for particular package or path.
     * Alternatively, if `$file` provided,
     * render template for particular package or path.
     * --
     * @param string $p
     *        Package name or full absolute path, root of templates.
     *
     * @param string $file
     *        Filename to be rendered, optional,
     *        if absent `template` object will be returned.
     *
     * @param array $variables
     *        Only if `$file` is provided, variables to be send to the template.
     * --
     * @throws mysli\tplp\exception\tplp 10 Invalid package name or path.
     * --
     * @return mixed
     *         String (rendered template) or `mysli\tplp\template`.
     */
    static function select($p, $file=null, array $variables=[])
    {
        // If package, resolve it to path.
        if (preg_match('/^[a-z_\.]+$/', $p))
            $path = static::get_path($p);
        else
            $path = $p;

        // Valid path?
        if (!$path || !dir::exists($path))
            throw new exception\tplp(
                "Invalid package name or path: `{$path}` for `{$p}`.", 10
            );


        if (!isset(static::$cache[$path]))
        {
            static::$cache[$path] = new template($path);
        }

        $template = static::$cache[$path];

        return $file
            ? $template->render($file, $variables)
            : $template;
    }

    /**
     * Get full absolute path to the root of templates, from package name.
     * This will inspect mysli.pkg.ym if costume path is set.
     *
     * If package not found, null will be returned.
     * --
     * @param string  $package
     * --
     * @throws mysli\tplp\exception\tplp 10 No such package.
     * --
     * @return string
     */
    static function get_path($package)
    {
        if (!pkg::exists($package))
            throw new exception\tplp("No such package: `{$package}`.", 10);

        $meta = pkg::get_meta($package);
        $path = arr_path($meta, 'tplp.path', 'assets/tplp');

        return fs::pkgreal($path);
    }
}
