<?php

namespace mysli\assets; class assets
{
    const __use = '
        .{ map, exception.assets }
        mysli.toolkit.{ request, pkg, ym, type.arr -> arr }
        mysli.toolkit.fs.{ fs, file, dir }
    ';

    /**
     * Cached maps.
     * --
     * @var array
     */
    protected static $cache = [ 'map' => [] ];

    /**
     * Publish assets for particular package.
     * --
     * @param string $package
     * --
     * @throws mysli\assets\exception\assets 10 No such directory.
     * --
     * @return integer Count of published directories.
     */
    static function publish($package)
    {
        $map = static::map($package);

        if (!isset($map['includes']))
        {
            return 0;
        }

        $count = 0;

        $source = static::path($package);

        foreach ($map['includes'] as $dir => $opt)
        {
            if (!isset($opt['publish']) || !$opt['publish'])
            {
                continue;
            }

            if (dir::exists(fs::ds($source, $dir, 'dist~')))
            {
                dir::copy(
                    fs::ds($source, $dir, 'dist~'),
                    fs::pubpath('assets', $package, $dir));
                $count++;
            }
            elseif (dir::exists(fs::ds($source, $dir)))
            {
                dir::copy(
                    fs::ds($source, $dir),
                    fs::pubpath('assets', $package, $dir));
                $count++;
            }
            else
            {
                throw new exception\assets(
                    "No such directory: `{$dir}` in `{$source}`.", 10);
            }
        }

        return $count;
    }

    /**
     * Remove previously published assets.
     * --
     * @param string $package
     * --
     * @return boolean
     */
    static function unpublish($package)
    {
        return dir::remove(fs::pubpath('assets', $package));
    }

    /**
     * Get map for particular package.
     * --
     * @param string  $package
     * @param boolean $reload  If map is already cached, should be reloaded?
     * --
     * @throws mysli\assets\exception\assets 10 Map file not found.
     * @throws mysli\assets\exception\assets 20 Invalid map format.
     * --
     * @return array
     */
    static function map($package, $reload=false)
    {
        if ($reload || !isset(static::$cache['map'][$package]))
        {
            $path = static::path($package);
            $filename = fs::ds($path, 'map.ym');

            if (!file::exists($filename))
                throw new exception\assets(
                    "Map file not found: `{$filename}`.", 10
                );

            // Decode map
            $map = ym::decode_file($filename);

            // Valid map?
            if (!is_array($map) || !isset($map['includes']))
                throw new exception\assets(
                    'Invalid map format `includes` section is required.', 20
                );

            // Default settings
            $map_default = ym::decode_file(
                fs::pkgreal('mysli.assets', 'config/defaults.ym')
            );

            // Merge with defaults in a more reasonable way
            foreach ($map_default as $mkey => $mopt)
            {
                if (!isset($map[$mkey]))
                {
                    $map[$mkey] = $mopt;
                }
                else
                {
                    $map[$mkey] = array_merge($mopt, $map[$mkey]);
                }
            }

            // Resolve relative processes
            $map['modules'] = static::resolve_links($map['modules']);

            // Set cache
            static::$cache['map'][$package] = $map;
        }

        return static::$cache['map'][$package];
    }

    /**
     * Resolve files in particular map file.
     * It will modify map file!
     * --
     * @param array  $map  Map file.
     * @param string $root Assets root directory.
     * --
     * @throws mysli\assets\exception\assets
     *         10 Missing `includes` section in `map` array.
     */
    static function resolve_map(array &$map, $root)
    {
        if (!isset($map['includes']))
        {
            throw new exception\assets(
                "Missing `includes` section in `map` array.", 10
            );
        }

        foreach ($map['includes'] as $dir => &$opt)
        {
            $path = fs::ds($root, $dir);

            // No such path, nothing to do
            if (!dir::exists($path))
            {
                $opt['ignore'] = true;
                continue;
            }

            // Set defaults
            $opt['id'] = $dir;
            $opt['resolved'] = [];
            $opt['use-modules'] = [];
            if (!isset($opt['publish'])) { $opt['publish'] = true; }
            if (!isset($opt['process'])) { $opt['process'] = true; }
            if (!isset($opt['ignore']))  { $opt['ignore']  = false; }
            if (!isset($opt['merge']))   { $opt['merge']   = false; }

            // No files to process
            if (!isset($opt['files'])) { continue; }

            if (is_string($opt['files'])) { $opt['files'] = [ $opt['files'] ]; }

            // Process & publish explicity turned off, ignore directory
            if ((!$opt['process'] && !$opt['publish']) || $opt['ignore']) { continue; }

            // Modules
            $modules = isset($map['modules']) ? $map['modules'] : [];
            if (isset($opt['modules']))
            {
                $modules = array_merge($modules, $opt['modules']);
            }

            foreach ($opt['files'] as $file)
            {
                // Do flags
                if (strpos($file, ' !') !== false)
                {
                    $flags = explode(' !', $file);
                    $file = trim( array_shift($flags) );
                }
                else
                {
                    $flags = [];
                }

                if (strpos($file, '*') !== false)
                {
                    $rsearch = file::find($path, $file);
                }
                else
                {
                    $rsearch[$file] = fs::ds($root, $dir, $file);
                }

                // Get modules & resolve file
                foreach ($rsearch as $sfile => $_)
                {
                    if (substr($sfile, 0, 5) === 'dist~')
                    {
                        continue;
                    }

                    if (isset($opt['resolved'][ fs::ds($dir, $sfile) ]))
                    {
                        continue;
                    }

                    list($kfile, $cfile, $module) =
                        static::resolve_file_module($sfile, $modules);

                    if ($module && !in_array($module, $opt['use-modules']))
                    {
                        $opt['use-modules'][] = $module;
                    }

                    $opt['resolved'][ fs::ds($dir, $sfile) ] = [
                        'id'         => $dir,
                        'module'     => $modules[$module],
                        'flags'      => $flags,
                        // File in source directory
                        'compressed' => fs::ds($dir, $cfile),
                        'source'     => fs::ds($dir, $sfile),
                        'resolved'   => fs::ds($dir, $kfile),
                        // File in dist~ directory
                        'compressed_dist' => fs::ds($dir, 'dist~', $cfile),
                        'source_dist'     => fs::ds($dir, 'dist~', $sfile),
                        'resolved_dist'   => fs::ds($dir, 'dist~', $kfile),
                    ];
                }
            }
        }
    }

    /**
     * Get module and new file extention for particular file.
     * --
     * @param string $file
     * @param array  $modules
     * --
     * @return [ string $filename, string $filename_compressed, string $module_id ]
     */
    static function resolve_file_module($file, $modules)
    {
        // Do we have match in modules?
        foreach ($modules as $ext => $opt)
        {
            if (strtolower(substr($file, -(strlen($ext)))) === strtolower($ext))
            {
                return [
                    substr($file, 0, -(strlen($ext))) . $opt['produce'],
                    substr($file, 0, -(strlen($ext))) . 'min.' . $opt['produce'],
                    $ext
                ];
            }
        }

        return [ $file, $file, null ];
    }

    /**
     * Get full absolute path to the package's assets (in package).
     * --
     * @param string $package
     * --
     * @return string
     */
    static function path($package)
    {
        $meta = pkg::get_meta($package);
        $path = pkg::get_path($package);

        $assets = 'assets';

        if (isset($meta['assets']))
        {
            if (isset($meta['assets']['path']))
            {
                $assets = $meta['assets']['path'];
            }
        }

        return fs::ds($path, $assets);
    }

    /**
     * Get full absolute public path to the package's assets.
     * --
     * @param string $package
     * --
     * @return string
     */
    static function pubpath($package)
    {
        return fs::pubpath("assets", $package);
    }

    /**
     * Return an ID from full absolute file path.
     * This will check DEV ids only.
     * --
     * @param string $path Full absolute path inc. filename.
     * @param string $root Assets root.
     * @param array  $map  Map.
     * --
     * @return string
     */
    static function id_from_file($path, $root, array $map)
    {
        $path_seg = trim(substr($path, strlen($root)), '\\/');
        $file = file::name($path);

        foreach ($map['includes'] as $id => $opt)
        {
            if (strpos($path_seg, $id) !== 0)
            {
                continue;
            }

            if (!is_array($opt['files']))
            {
                continue;
            }

            if (!is_array($opt['files']))
            {
                $opt['files'] = [ $opt['files'] ];
            }

            foreach ($opt['files'] as $include)
            {
                if (strpos($include, '*') !== false)
                {
                    $filter = fs::filter_to_regex($include);

                    if (preg_match($filter, $file))
                    {
                        return $id;
                    }
                }
                else
                {
                    if ($include === $file)
                    {
                        return $id;
                    }
                }
            }
        }

        // Not found
        return null;
    }

    /**
     * Get URL to the specific resource.
     * --
     * @param string $...
     * --
     * @return string
     */
    static function url()
    {
        return preg_replace(
            '/[\/\\\\]+/',
            '/',
            '/assets/'.implode('/', func_get_args())
        );
    }

    /**
     * Get HTML tags.
     * --
     * @param  string $id
     * @param  string $package
     * --
     * @return array
     */
    static function get_tags($id, $package)
    {
        $links = static::get_links($id, $package);
        $map = static::map($package);
        $tags = $map['tags'];

        foreach ($links as $id => &$link)
        {
            $ext = substr(file::extension($link), 1);
            foreach ($tags as $tag)
            {
                if (in_array($ext, $tag['match']))
                {
                    $link = str_replace('{link}', $link, $tag['tag']);
                    continue 2;
                }
            }

            unset($links[$id]);
        }

        return $links;
    }

    /**
     * Get links.
     * --
     * @param  string $id
     * @param  string $package
     * --
     * @throws mysli\assets\exception\assets 10 No such ID.
     * --
     * @return array
     */
    static function get_links($id, $package)
    {
        $type = pkg::exists_as($package);
        $map = static::map($package);
        $path = static::pubpath($package);

        if (!isset($map['includes'][$id]))
            throw new exception\assets("No such ID: `{$id}`", 10);

        if ($type === pkg::phar && isset($map['includes'][$id]['merge']))
        {
            return [ static::url($package, $id, $map['includes'][$id]['merge']) ];
        }

        $links = [];
        $modules = isset($map['modules']) ? $map['modules'] : [];
        if (isset($map['includes'][$id]['modules']))
        {
            $modules = array_merge($modules, $map['includes'][$id]['modules']);
        }

        if (!isset($map['includes'][$id]['files']))
        {
            return $links;
        }

        // Need to be an array
        if (!is_array($map['includes'][$id]['files']))
        {
            $map['includes'][$id]['files'] = [ $map['includes'][$id]['files'] ];
        }

        foreach ($map['includes'][$id]['files'] as $file)
        {
            if (strpos($file, ' !') !== false)
            {
                $file = explode(' !', $file, 2)[0];
            }

            list($file, $_, $_) = static::resolve_file_module($file, $modules);

            if (strpos($file, '*') !== false)
            {
                $rsearch = file::find(fs::ds($path, $id), $file);
            }
            else
            {
                $rsearch[$file] = fs::ds($path, $id, $file);
            }

            foreach ($rsearch as $rfile => $afile)
            {
                if (file::exists($afile))
                {
                    $url = static::url($package, $id, $rfile);
                    if (!in_array($url, $links))
                    {
                        $links[] = $url;
                    }
                }
            }
        }

        return $links;
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Resolve internally linked modules.
     * Example of `styl` build referencing `css` build.
     * css:
     *     produce: css
     *     require: [ ... ]
     *     process: [ ... ]
     *     build: [ ... ]
     * styl:
     *     produce: ...
     *     require: [ ... ]
     *     process: [ ... ]
     *     build: &css.build
     * --
     * @param array $modules
     * --
     * @throws mysli\assets\exception\assets 10 Invalid reference.
     * --
     * @return array Resolved modules.
     */
    protected static function resolve_links(array $modules)
    {
        foreach ($modules as $pid => &$module)
        {
            foreach (['require', 'process', 'build'] as $item)
            {
                if (!isset($module[$item]))
                {
                    $module[$item] = '';
                    continue;
                }

                while (is_string($module[$item]) && substr($module[$item], 0, 1) === '$')
                {
                    list($id, $key) = explode('.', substr($module[$item], 1));

                    if (!isset($modules[$id]) || !isset($modules[$id][$key]))
                        throw new exception\assets(
                            "Invalid reference `{$id}.{$key}`, not found. ".
                            "For: `{$pid}.{$item}`", 10
                        );

                    $module[$item] = $modules[$id][$key];
                }
            }
        }

        return $modules;
    }
}
