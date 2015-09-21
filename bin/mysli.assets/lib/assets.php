<?php

namespace mysli\assets; class assets
{
    const __use = '
        .{ exception.assets }
        mysli.toolkit.{ request, pkg, ym, type.arr -> arr }
        mysli.toolkit.fs.{ fs, file, dir }
    ';

    /**
     * Cached maps.
     * --
     * @var array
     */
    protected static $map_cache = [];

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
        if ($reload || !isset(static::$map_cache[$package]))
        {
            $path = static::path($package);
            $filename = fs::ds($path, 'map.ym');

            if (!file::exists($filename))
            {
                throw new exception\assets(
                    "Map file not found: `{$filename}`.", 10
                );
            }

            // Decode map
            $map = ym::decode_file($filename);

            // Valid map?
            if (!is_array($map) || !isset($map['files']))
            {
                throw new exception\assets(
                    'Invalid map format `files` section is required.', 20
                );
            }

            // Default settings
            $map_default = ym::decode_file(
                fs::pkgreal('mysli.assets', 'config/defaults.ym')
            );

            // Merge with map!
            $map = arr::merge($map_default, $map);

            // Resolve relative processes
            $map['process'] = static::resolve_relative_processes($map['process']);

            // Set cache
            static::$map_cache[$package] = $map;
        }

        return static::$map_cache[$package];
    }

    /**
     * Get full absolute path to the package's assets.
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
     * Return public URL for particular package's assets.
     * --
     * @param string $package
     * @param string $uri
     * --
     * @return string
     */
    static function url($package, $uri=null)
    {
        return request::url()."/{$package}/{$uri}";
    }

    /**
     * Transform full absolute public path to URL.
     * --
     * @param string $path
     * --
     * @return string
     */
    static function pub2url($path)
    {
        return request::url().'/'.substr($path, strlen(fs::pubpath()));
    }

    /**
     * Apply specific filter to a file.
     * This is not a simple search-replace.
     * It will take into account file extension and replace it in a _smart_ way.
     *
     * For example:
     *
     *     $file      = 'foo.md';
     *     $filter    = 'command {src/file} -o {dest/file.html}';
     *     $variables = [
     *         // Use end slash or `dest` rather than `dest/`
     *         'dest/' => '/var/www/dist~/',
     *         'src/'  => '/var/www/'
     *     ];
     *     // Result: command /var/www/foo.md -o /var/www/dist~/foo.html
     * --
     * @param  string $file
     * @param  array $variables
     * --
     * @return string
     */
    static function apply_filter($file, $filter, array $variables=[])
    {
        return preg_replace_callback(
            '/\{(.*?)}\/',
            function ($group) use ($file, $variables)
            {
                $group = $group[1];

                // Replace variables
                foreach ($variables as $var => $val)
                {
                    $position = strpos($group, $var);
                    if ($position !== false)
                    {
                        $group = substr_replace($group, $val, $position, strlen($var));
                    }
                }

                // Replace file segment
                $group = preg_replace_callback(
                    '/file(\.[\.a-z0-9]+)?$/',
                    function ($mfile) use ($file) {
                        $ext = isset($mfile[1]) ? $mfile[1] : null;
                        if ($ext)
                        {
                            $file = file::name($file).$ext;
                        }
                        return $file;
                    },
                    $group
                );

                return $group;
            },
            $filter
        );
    }

    /**
     * Get processes for a particular file. This will take file extension into
     * account.
     * --
     * @param string $file
     * @param array  $processes
     * @param array  $processes_list
     * --
     * @throws mysli\assets\exception\assets 10 No such process.
     * --
     * @return array
     */
    static function get_file_processes($file, array $processes, array $processes_list)
    {
        $list = [];

        foreach ($processes as $procid)
        {
            // Processor must be on a list
            if (!isset($processes_list[$procid]))
            {
                throw new exception\assets(
                    "No such process defined: `{$procid}`.", 10
                );
            }

            // Grab processor from the main list
            $process = $processes_list[$procid];

            // If match is not specified as an array, convert it now
            if (!is_array($process['match']))
            {
                $process['match'] = [ $process['match'] ];
            }

            // Loop through matches and see if we can match any of them with a
            // provided filename --- in such case add processor to the list.
            foreach ($process['match'] as $match)
            {
                $match = fs::filter_to_regex($match);
                if (preg_match($match, $file))
                {
                    $list[] = $process;
                    break;
                }
            }
        }

        return $list;
    }

    /**
     * Get list of processed files as specified in map file.
     * This will look for actual files and if file is not there it will not be
     * included on the list.
     * --
     * @param string $root
     *        Full absolute path to the assets.
     *        This can be public path static::pubpath($package).
     *
     * @param array $map
     *        Map file.
     *
     * @param boolean $dev
     *        Weather this is a development version.
     *        If yes, compress and merge will be ignored.
     *
     * @param string $id
     *        Only process one particular ID as defined in map.
     * --
     * @return array
     */
    static function resolve_processed_files($root, array $map, $dev=false, $id=null)
    {
        $files = $map['files'];
        $includes = [];

        // These are variables for transforming filename
        // to the actual extension, ...
        $variables = [
            'src/'  => '/',
            'dest/' => '/'
        ];

        foreach ($files as $dir => $options)
        {
            if ($id && $dir !== $id)
            {
                continue;
            }

            if (!isset($options['include']))
            {
                continue;
            }

            // Try with dist~
            $current_path = fs::ds($root, $dir, 'dist~');
            if (!dir::exists($current_path))
            {
                // Default current DIR, no dist~
                $current_path = fs::ds($root, $dir);
                if (!dir::exists($current_path))
                {
                    continue;
                }
            }

            $includes[$dir] = [];

            // If this is not DEV and there's merge options, just return a path
            // to the merged file.
            if (!$dev && isset($options['merge']))
            {
                $includes[$dir][] = fs::ds($current_path, $options['merge']);
                continue;
            }

            // Go through includes and build filenames
            foreach ($options['includes'] as $include)
            {
                // Processor's results on a file
                $processes = static::get_file_processes(
                    $include, $options['process'], $map['process']
                );

                foreach ($processes as $process)
                {
                    // Only last line in process is interesting
                    // (as it defines a filter for a transformed filename)
                    $filter = arr::last($process['process']);
                    $include = static::apply_filter($include, $filter, $variables);

                    // Should do compress too?
                    $filter = arr::last($process['compress']);
                    $include = static::apply_filter($include, $filter, $variables);
                }

                if (strpos($include, '*') !== false)
                {
                    $includes[$dir] = array_merge(
                        $includes[$dir],
                        array_values( file::find($current_dir, $file) )
                    );
                }
                else
                {
                    $includes[$dir][] = fs::ds($current_dir, $file);
                }

                $includes[$dir][] = $include;
            }

            // Avoid duplicates
            $includes[$dir] = array_unique($includes[$dir], SORT_STRING);
        }

        return $includes;
    }

    /**
     * Find all files in particular map.
     * --
     * @param string $root
     *        Full absolute path to the assets root directory.
     *
     * @param array $map
     *        Map file.
     *
     * @param string $id
     *        Only process one particular id as defined in map.
     * --
     * @return array
     */
    static function resolve_source_files($root, array $map, $id=null)
    {
        $files = $map['files'];
        $includes = [];

        foreach ($files as $path => $options)
        {
            if ($id && $path !== $id)
            {
                continue;
            }

            $current_path = fs::ds($root, $path);

            if (!dir::exists($current_path))
            {
                continue;
            }

            if (!isset($options['include']))
            {
                continue;
            }

            $includes[$path] = [];

            foreach ($options['include'] as $file)
            {
                if (strpos($file, '*') !== false)
                {
                    $includes[$path] = array_merge(
                        $includes[$path],
                        array_values( file::find($current_path, $file) )
                    );
                }
                else
                {
                    $includes[$path][] = fs::ds($current_path, $file);
                }
            }

            $includes[$path] = array_unique($includes[$path], SORT_STRING);
        }

        return $includes;
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Resolve internally linked processes.
     * Example of `styl` compressor referencing `css` compressor.
     * css:
     *     match: *.css
     *     require: [ ... ]
     *     process: [ ... ]
     *     compress: [ ... ]
     * styl:
     *     match: ...
     *     require: [ ... ]
     *     process: [ ... ]
     *     compress: &css.compress
     * --
     * @param array $processes
     * --
     * @throws mysli\assets\exception\assets 10 Invalid reference.
     * --
     * @return array Resolved processes.
     */
    protected static function resolve_relative_processes(array $processes)
    {
        foreach ($processes as $pid => &$process)
        {
            foreach (['require', 'process', 'compress'] as $item)
            {
                if (!isset($process[$item]))
                {
                    $process[$item] = [];
                    continue;
                }

                while (is_string($process[$item]) && substr($process[$item], 0, 1) === '&')
                {
                    list($id, $key) = explode('.', substr($process[$item], 1));

                    if (!isset($processes[$id]) || !isset($processes[$id][$key]))
                    {
                        throw new exception\assets(
                            "Invalid reference `{$id}.{$key}`, not found. ".
                            "For: `{$pid}.{$item}`", 10
                        );
                    }

                    $process[$item] = $processes[$id][$key];
                }
            }
        }

        return $processes;
    }
}
