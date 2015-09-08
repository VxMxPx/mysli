<?php

namespace mysli\assets; class map
{
    const __use = '
        mysli.toolkit.fs.{ fs, file, dir }
        mysli.toolkit.{ ym }
    ';

    /**
     * Cached map files.
     * --
     * @var array
     */
    protected static $cache = [];

    /**
     * Read assets map file for a particular package.
     * --
     * @param string  $package
     *        Package name in format vendor.package
     *
     * @param boolean $reload
     *        Reload map if cached.
     * --
     * @return array
     */
    static function by_package($package, $reload=false)
    {
        if ($reload || !isset(self::$cache[$package]))
        {
            $filename = fs::pkgreal($package, $map);

            if (!file::exists($filename))
            {
                throw new framework\exception\not_found(
                    "Map file not found: {$filename}.", 1
                );
            }

            if (substr($map, -3) === '.ym')
            {
                $map = ym::decode_file($filename);
            }
            elseif (substr($map, -5) === '.json')
            {
                $map = json::decode_file($filename);
            }
            else
            {
                throw new framework\exception\argument(
                    "Invalid file type: `{$map}`", 1
                );
            }

            // Valid map?
            if (!is_array($map) || !isset($map['files']))
            {
                throw new framework\exception\data(
                    'Invalid map format `files` section is required.'
                );
            }

            // Default settings
            $map_default = ym::decode_file(
                fs::pkgreal('mysli.web.assets', 'data/defaults.ym')
            );

            // Merge with map!
            self::$cache[$package] = arr::merge($map_default, $map);
        }

        return self::$cache[$package];
    }

    /**
     * Get map by id. This will load map from public directory.
     * For it to work, assets must be already processed and published.
     * --
     * @param  string  $id
     *         ID in format `type:vendor.package`,
     *         for example, `package:vendor.package` or `theme:theme_name`.
     *
     * @param  boolean $reload
     *         Reload map, if cached.
     * --
     * @return array
     */
    static function by_id($id, $reload=false)
    {
    }

    /**
     * Load map from particular path (full absolute directory).
     * --
     * @param  string  $path
     *         Full absolute path to the map file (including map file).
     * --
     * @throws mysli\assets\exception\map 10 Map file not found.
     * --
     * @return array
     */
    static function by_path($path)
    {
        if (!file::exists($path))
        {
            throw new exception\map("Map file not found: `{$map}`", 10);
        }

        $map = ym::decode_file($path);

        if (isset($map['id']))
        {
            static::$cache[$map['id']] = $map;
        }

        return $map;
    }









    /**
     * Get default paths
     * @param  string $package
     * @return array  [source, dest, map]
     */
    static function get_paths($package)
    {
        $meta = pkgm::meta($package);

        $source = 'src';
        $dest   = '(dist)';
        $map    = 'assets.ym';

        if (isset($meta['assets']))
        {
            if (isset($meta['assets']['source']))
            {
                $source = $meta['assets']['source'];
            }

            if (isset($meta['assets']['destination']))
            {
                $dest = $meta['assets']['destination'];
            }

            if (isset($meta['assets']['map']))
            {
                $map = $meta['assets']['map'];
            }
        }

        return [$source, $dest, $map];
    }
}
