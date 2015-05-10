<?php

namespace mysli\web\assets;

__use(__namespace__, '
    mysli.framework.exception/* -> framework\exception\*
    mysli.framework.fs/fs,file,dir
    mysli.framework.type/arr
    mysli.framework.pkgm
    mysli.framework.json
    mysli.framework.ym
    mysli.util.config
    mysli.web.web
');

class assets
{
    private static $web_dir = 'assets';
    private static $cache = [];

    /**
     * Get full absolute path for particular package.
     * This will NOT check weather path already exists.
     * @param  string $package
     * @return string
     */
    static function get_public_path($package)
    {
        return web::path(self::$web_dir, $package);
    }
    /**
     * Get full absolute public url for particular package.
     * This will NOT check weather path exists.
     * @param  string $package
     * @return string
     */
    static function get_public_url($package)
    {
        return web::url(self::$web_dir.'/'.$package);
    }
    /**
     * Get parsed ext, e.g.: stly => css
     * @param  string $file
     * @return string
     */
    static function parse_extention($file, array $exts=[])
    {
        $ext = file::extension($file);

        if (arr::key_in($exts, $ext))
        {
            return substr($file, 0, -(strlen($ext))) . $exts[$ext];
        }
        else
        {
            return $file;
        }
    }
    /**
     * Read assets file for particular package.
     * @param  string  $package
     * @param  string  $map filename
     * @param  boolean $reload load map even if cached
     * @return array
     */
    static function get_map($package, $map, $reload=false)
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
     * Publish assets for particular package.
     * @param  string $package
     * @param  string $dist
     * @param  array  $assets (map array)
     * @return integer
     */
    static function publish($package, $dist=null, array $assets=null)
    {
        $n = 0;

        if (!$dist || !$assets)
        {
            list($_, $dist, $map) = self::get_paths($package);
            $assets = self::get_map($package, $map);
        }

        $dist  = fs::pkgreal($package, $dist);
        $webpath = self::get_public_path($package);

        if (!isset($assets['publish']) || !is_array($assets['publish'])) {
            return $n;
        }

        if (!dir::exists($dist))
        {
            throw new framework\exception\not_found(
                "Cannot publish -- folder not found: `{$dist}`", 1
            );
        }

        if (dir::exists($webpath))
        {
            dir::remove($webpath);
        }
        dir::create($webpath);

        foreach ($assets['publish'] as $asset_dir) {
            $n = $n + dir::copy(fs::ds($dist, $asset_dir), fs::ds($webpath, $asset_dir));
        }

        return $n;
    }
    /**
     * Destroy (delete) published assets for package.
     * @param  string $package
     * @return boolean
     */
    static function destroy($package)
    {
        $path = self::get_public_path($package);

        if (dir::exists($path)) return dir::remove($path);
        else                    return false;
    }
    /**
     * Get links for particular file/package
     * @param  string $package
     * @param  string $file    if null, all files will be returned
     * @param  string $filter  if file is null, you can filter links by type,
     *                         allowed values: css|js
     * @return array
     */
    static function get_links($package, $file=null, $filter=null)
    {
        if (!file::exists(self::get_public_path($package)))
        {
            throw new framework\exception\not_found(
                "Public folder not found: `{$package}`", 1
            );
        }

        list($source, $_, $map) = self::get_paths($package);

        $map = self::get_map($package, $map);
        $debug = config::select('mysli.web.assets', 'debug', false);
        $links = [];

        foreach ($map['files'] as $main => $props)
        {
            if ($file && $file !== $main)
            {
                continue;
            }

            if (!$debug)
            {
                $links[] = web::url(self::$web_dir."/{$package}/{$main}");
            }
            else
            {
                foreach ($props['include'] as $asset)
                {
                    $asset = self::parse_extention(
                        $asset, $map['settings']['ext']
                    );

                    if (!$filter ||
                        substr($asset, -(strlen($filter))) === $filter)
                    {
                        $links[] = web::url(self::$web_dir."/{$package}/{$asset}");
                    }
                }
            }
        }

        return $links;
    }
    /**
     * Return an array containing HTML tags for particular file / package
     * @param  string $package
     * @param  string $file    if null, tags for all files will be returned
     * @param  string $filter  if file is null, you can filter links by type,
     *                         allowed values: css|js
     * @return array
     */
    static function get_tags($package, $file=null, $filter=null)
    {
        $links = self::get_links($package, $file, $filter);
        $tags = [];

        foreach ($links as $link)
        {
            if (substr($link, -3) === '.js')
            {
                $tags[] = '<script src="'.$link.'"></script>';
            }
            elseif (substr($link, -4) === '.css')
            {
                $tags[] = '<link rel="stylesheet" type="text/css" href="'.$link.'">';
            }
            else
            {
                throw new framework\exception\argument(
                    "Unknown file extension: `{$link}`"
                );
            }
        }

        return $tags;
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
