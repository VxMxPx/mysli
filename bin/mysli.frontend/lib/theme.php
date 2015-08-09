<?php

/**
 * # Frontend Theme
 *
 * Add or remove themes. Get default theme path.
 */
namespace mysli\frontend; class theme
{
    const __use = '
        .{
            exception.theme
        }
        mysli.toolkit.{
            ym
            pkg
            fs.fs -> fs
            fs.dir -> dir
            fs.file -> file
        }
    ';

    /**
     * List available themes.
     * --
     * @throws mysli\frontend\exception\theme
     *         10 File specefiled in source not found.
     * --
     * @return array
     */
    static function get_list()
    {
        $list = [];

        foreach (fs::ls(fs::cntpath('themes')) as $directory)
        {
            $themeym = fs::cntpath('themes', $directory, 'theme.ym');
            if (!file::exists($themeym))
            {
                continue;
            }

            $mtheme = ym::decode_file($themeym);

            /*
            Source theme from different directory
             */
            if (isset($mtheme['source']))
            {
                $source = $mtheme['source'];

                // Source from package's root
                if (is_array($source))
                {
                    $source = static::get_pkg_source($source[0], $source[1]);
                }

                $sthemeym = fs::ds($source, 'theme.ym');

                if (!file::exists($sthemeym))
                    throw new exception\theme(
                        "File specefiled in source not found: `{$source}`.", 10
                    );

                $mtheme = array_merge_recursive(
                    $mtheme,
                    ym::decode_file($sthemeym)
                );

                $mtheme['absolute_path'] = $source;
            }
            else
            {
                $mtheme['absolute_path'] = fs::cntpath('themes', $directory);
            }

            $mtheme['id'] = $directory;
            $list[$directory] = $mtheme;
        }

        return $list;
    }

    /**
     * Change active theme.
     * --
     * @param string $theme Theme's ID.
     * --
     * @return boolean
     */
    static function set_active($theme)
    {

    }

    /**
     * Get currently active theme.
     * --
     * @return string
     */
    static function get_active()
    {

    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Get full absolute path from package's name and relative path.
     * --
     * @param string $package
     * @param string $path
     * --
     * @return string
     *         Null if not found.
     */
    protected static function get_pkg_source($package, $path)
    {
        if (!pkg::exists($package))
        {
            return null;
        }

        return fs::pkgreal($package, $path);
    }
}
