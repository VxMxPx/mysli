<?php

/**
 * # Frontend Theme
 *
 * Add or remove themes. Get default theme path.
 */
namespace mysli\frontend; class theme
{
    const __use = '
        .{ exception.theme }
        mysli.toolkit.{
            ym
            pkg
            config
            fs.fs -> fs
            fs.dir -> dir
            fs.file -> file
        }
    ';

    /**
     * List available themes.
     * --
     * @return array
     */
    static function get_list()
    {
        $list = [];

        foreach (fs::ls(fs::cntpath('themes')) as $directory)
        {
            $meta = static::get_meta($directory);

            if (!$meta)
            {
                continue;
            }

            $list[$meta['id']] = $meta;
        }

        return $list;
    }

    /**
     * Change active theme.
     * --
     * @param string $theme Theme's ID.
     * --
     * @throws mysli\frontend\exception\theme 10 Cannot get meta.
     * --
     * @return boolean
     */
    static function set_active($theme)
    {
        if ($theme === static::get_active())
        {
            return true;
        }

        $meta = static::get_meta($theme);

        if (!$meta)
        {
            throw new exception\theme("Cannot get meta for: `{$theme}`.", 10);
        }

        // Something to publish?
        $publish = fs::ds($meta['absolute_path'], 'public');
        if (dir::exists($publish))
        {
            dir::copy($publish, fs::pubpath('themes', $theme));
        }

        $config = config::select('mysli.frontend');
        $config->set('theme.active', $theme);

        return $config->save();
    }

    /**
     * Get currently active theme.
     * --
     * @return string
     */
    static function get_active()
    {
        return config::select('mysli.frontend', 'theme.active');
    }

    /**
     * Check if particular theme exists.
     * --
     * @param string $theme
     * --
     * @return boolean
     */
    static function has($theme)
    {
        return file::exists(
            fs::cntpath('themes', $theme, 'theme.ym')
        );
    }

    /**
     * Get (merged) meta file for particular theme.
     * --
     * @param string $theme
     * --
     * @throws mysli\frontend\exception\theme
     *         10 File specefiled in source not found.
     * --
     * @return array
     */
    static function get_meta($theme)
    {
        $themeym = fs::cntpath('themes', $theme, 'theme.ym');

        if (!file::exists($themeym))
        {
            return null;
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
            $mtheme['absolute_path'] = fs::cntpath('themes', $theme);
        }

        $mtheme['id'] = $theme;

        return $mtheme;
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
