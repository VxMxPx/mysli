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
    protected static $cache = [
        'maps' => []
    ];

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
        if ($reload || !isset(static::$cache['maps'][$package]))
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
            $map['process'] = static::resolve_processors_links($map['process']);

            // Set cache
            static::$cache['maps'][$package] = $map;
        }

        return static::$cache['maps'][$package];
    }

    /**
     * Get list(s) for development (processing).
     *
     * Return:
     * [
     *     id => [
     *         absolute_filename => []
     *     ]
     * ]
     * --
     * @param string $root Assets root directory.
     * @param array  $map
     * @param string $id
     * --
     * @return array
     */
    static function get_dev_list($root, array $map, $id=null)
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

            if (!is_array($options['include']))
            {
                $options['include'] = [ $options['include'] ];
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
            $includes[$path] = array_flip($includes[$path]);

            // Now resolve files
            foreach ($includes[$path] as $absfn => $_)
            {
                // Processor's results on a file
                $processors = static::get_processors(
                    file::name($absfn), $options['process'], $map['process']
                );

                $includes[$path][$absfn] = static::get_dev_file(
                    $absfn, $root, $processors
                );
            }
        }

        return $includes;
    }

    /**
     * Resolve development version of file
     * Set:
     *     rel_path,   // Relative path segment
     *     rel_file,   // Filename
     *     processed,  // Processed filename + relative path
     *     compressed, // Compressed filename + relative path
     * --
     * @param string $filename    Full absolute path.
     * @param string $root        Assets root.
     * @param array  $processors  List of processors for this particular file.
     * @param array  $variables   Costume variables (src/, dest/)
     * --
     * @return array
     */
    static function get_dev_file($filename, $root, array $processors, $variables=null)
    {
        // These are variables for transforming filename
        // to the actual extension, ...
        if (!$variables)
        {
            $variables = [
                'src/'  => '',
                'dest/' => 'dist~/'
            ];
        }

        $fopt = [
            // Relative filename
            'rel_path'      => ltrim(dirname(substr($filename, strlen($root))), '/'),
            'rel_file'      => file::name($filename),
            'processed'     => null,
            'compressed'    => null,
        ];

        foreach ($processors as $processor)
        {
            // Only last line in process is interesting.
            // As it defines a filter for a transformed filename.
            $filter = arr::last($processor['process']);
            $fopt['processed'] = static::apply_filter(
                $fopt['rel_path'].'/'.$fopt['rel_file'],
                $filter,
                $variables
            );

            // Should compress?
            if (isset($processor['compress']))
            {
                $filter = arr::last($processor['compress']);
                $fopt['compressed'] = static::apply_filter(
                    $fopt['rel_path'].'/'.file::name($fopt['processed']),
                    $filter,
                    $variables
                );
            }
        }

        return $fopt;
    }

    /**
     * Get list of published files (for particular ID, taking map into account).
     * This will work similary as get_dev_list, only that in this case source
     * files will not be required.
     *
     * Return:
     * [ abs_path, abs_path ]
     * --
     * @param array $map
     * --
     * @return array
     */
    static function get_pub_list($root, array $map, $id=null)
    {
        // Dist?
        if (dir::exists(fs::ds($root, 'dist~')))
            $root = fs::ds($root, 'dist~');

        $includes = [];

        foreach ($map['files'] as $path => $opt)
        {
            // If specific ID is required then ignore everything else
            if ($id && $path !== $id)
                continue;

            $current_path = fs::ds($root, $path);

            // Directory must be there, duh
            if (!dir::exists($current_path))
                continue;

            // Include must be set of this file
            if (!isset($opt['include']))
                continue;

            // Include needs to be an array
            if (!is_array($opt['include']))
                $opt['include'] = [ $opt['include'] ];

            // Add path
            $includes[$path] = [];

            foreach ($opt['include'] as $file)
            {
                // Now resolve individual includes
                $resolved_file = static::get_dev_file(
                    fs::ds($current_path, $file),
                    $root,
                    static::get_processors($file, $opt['process'], $map['process']),
                    ['src/' => '', 'dest/' => '']
                );

                $resolved_file = file::name($resolved_file['processed']);

                if (strpos($file, '*') !== false)
                {
                    $includes[$path] = array_merge(
                        $includes[$path],
                        array_values( file::find($current_path, $resolved_file) )
                    );
                }
                else
                {
                    $resolved_file = fs::ds($current_path, $resolved_file);

                    if (file::exists($resolved_file))
                        $includes[$path][] = $resolved_file;
                }
            }

            $includes[$path] = array_unique($includes[$path], SORT_STRING);
        }

        return $includes;
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
     * @param string $file
     * @param array  $variables
     * --
     * @return string
     */
    static function apply_filter($file, $filter, array $variables=[])
    {
        return preg_replace_callback(
            '/\{(.*?)}/',
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
                            $file = file::extension($file, true).$ext;
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

        foreach ($map['files'] as $id => $opt)
        {
            if (strpos($path_seg, $id) !== 0)
                continue;

            if (!is_array($opt['include']))
                $opt['include'] = [ $opt['include'] ];

            foreach ($opt['include'] as $include)
            {
                if (strpos($include, '*') !== false)
                {
                    $filter = fs::filter_to_regex($include);

                    if (preg_match($filter, $file))
                        return $id;
                }
                else
                {
                    if ($include === $file)
                        return $id;
                }
            }
        }

        // Not found
        return null;
    }

    /**
     * Get processes for a particular file.
     * This will take file extension into account.
     * --
     * @param string $file             Actual filename (no path).
     * @param array  $file_processors  Processors assigned to ID.
     * @param array  $processors       All processors available.
     * --
     * @throws mysli\assets\exception\assets 10 No such process.
     * --
     * @return array
     */
    static function get_processors($file, array $file_processors, array $processors)
    {
        $list = [];

        foreach ($file_processors as $procid)
        {
            // Processor must be on a list
            if (!isset($processors[$procid]))
            {
                throw new exception\assets(
                    "No such process defined: `{$procid}`.", 10
                );
            }

            // Grab processor from the main list
            $process = $processors[$procid];

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

    /*
    --- Paths ------------------------------------------------------------------
     */

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
     * Return base public URL for particular package's assets.
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
    static function pub_to_url($path)
    {
        return request::url().'/'.substr($path, strlen(fs::pubpath()));
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Resolve internally linked processors.
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
    protected static function resolve_processors_links(array $processes)
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
