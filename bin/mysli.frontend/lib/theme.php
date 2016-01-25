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
        mysli.toolkit.{ pkg, config }
        mysli.toolkit.fs.{ fs, dir, file }
        mysli.toolkit.type.{ arr_path -> arrp }
    ';

    /**
     * List available themes.
     * --
     * @return array
     */
    static function get_list()
    {
        $list = [];

        foreach (pkg::list_enabled() as $pkg)
        {
            $meta = pkg::get_meta($pkg);

            if (arrp::get($meta, 'frontend.type') === 'theme')
            {
                $list[$meta['package']] = $meta;
            }
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

        $meta = pkg::get_meta($theme);

        if (!$meta)
        {
            throw new exception\theme("Cannot get meta for: `{$theme}`.", 10);
        }

        // Get public section (default to asset/public)
        $assets = arrp::get($meta, 'frontend.assets', 'assets');

        // Something to publish?
        $publish = fs::pkgreal($package, "{$assets}/public");

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
        return pkg::is_enabled($theme);
    }
}
