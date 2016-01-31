<?php

namespace mysli\toolkit\fs; class fs
{
    const __use = '
        .{
            pkg,
            fs.file -> file,
            fs.dir  -> dir,
            log,
            exception.fs
        }
    ';

    const map_continue = '\\/map continue\\/';

    /**
     * Convert size (from bytes) to nicer (human readable) value (kb, mb)
     * Return: bytes|KB|MB|GB
     * --
     * @param integer $size (bytes)
     * @param integer $round_precision
     * --
     * @return array In format: `[12, 'MB']`.
     */
    static function format_size($size, $round_precision=4)
    {
        $size = floatval($size);

        if ($size < 1024)
        {
            return [$size, 'bytes'];
        }
        elseif ($size < 1048576)
        {
            return [round($size/1024, $round_precision), 'KB'];
        }
        else
        {
            $result = round($size/1048576, $round_precision);

            if ($result > 1024)
            {
                $result = [round($result/1024, $round_precision), 'GB'];
            }
            else
            {
                $result = [$result, 'MB'];
            }

            return $result;
        }
    }

    /**
     * Rename a file or directory.
     * --
     * @param mixed $old String or array [old => new, old => new].
     * @param mixed $new String or null (if first param is array).
     * --
     * @return integer Number of renamed files.
     */
    static function rename($old, $new=null)
    {
        if (is_array($old))
        {
            $renamed = 0;

            foreach ($old as $o => $n)
            {
                $renamed = $renamed + static::rename($o, $n);
            }

            return $renamed;
        }

        log::info(
            "Rename: `{$old}` to `{$new}`.",
            __CLASS__
        );

        return rename($old, $new);
    }

    /**
     * Generate unique prefix for particular file / folder.
     * --
     * @param string $filename Full path.
     * --
     * @return string A new filename.
     */
    static function unique_prefix($filename)
    {
        $destination = dirname($filename);
        $filename    = basename($filename);
        return md5(static::ds($destination, $filename)) . '_' . $filename;
    }

    /**
     * Method to calculate the relative path from $from to $to.
     * Note: On Windows it does not work when $from and $to are on different drives.
     * Credit: http://www.php.net/manual/en/function.realpath.php#105876
     * --
     * @param string $to
     * @param string $from
     * @param string $ps
     * --
     * @return string
     */
    static function relative_path($to, $from, $ps=DIRECTORY_SEPARATOR)
    {
        $ar_from = explode($ps, rtrim($from, $ps));
        $ar_to = explode($ps, rtrim($to, $ps));

        while(count($ar_from) && count($ar_to) && ($ar_from[0] == $ar_to[0]))
        {
            array_shift($ar_from);
            array_shift($ar_to);
        }

        return
            str_pad('', count($ar_from) * 3, '..' . $ps) .
            implode($ps, $ar_to);
    }

    /**
     * Generate a new unique file/dir name, only if the file/dir already exists.
     * --
     * @param  string $filename Full path.
     * @param  string $divider  E.g. file.txt => file_2.txt when divider is `_`.
     * --
     * @return string /absolute/path/to/file
     */
    static function unique_name($filename, $divider='_')
    {
        $directory    = dirname($filename);
        $filename     = basename($filename);
        $new_filename = $filename;
        $ext          = file::extension($filename);
        $ext          = empty($ext) ? '' : '.' . $ext;
        $base         = file::name($filename, false);
        $n            = 2;

        while (file::exists(static::ds($directory, $new_filename)))
        {
            $new_filename = $base . $divider . $n . $ext;
            $n++;
        }

        return static::ds($directory, $new_filename);
    }

    /**
     * Call function for each file/dir.
     * --
     * @example
     *     function ($full_absolute_path, $relative_path, $is_directory)
     *         return fs::map_continue - skip to the next file
     *
     * If used on a directory the whole directory
     * (with all content) will be skipped.
     * --
     * @param string   $directory
     * @param callable $callback
     * @param integer  $rcut
     * --
     * @throws mysli\toolkit\exception\fs 10 Not a valid directory.
     * --
     * @return array
     */
    static function map($directory, $callback, $rcut=null)
    {
        $collection = [];

        if (!dir::exists($directory))
        {
            throw new exception\fs(
                "Not a valid directory: `{$directory}`.", 10
            );
        }

        foreach (static::ls($directory) as $file)
        {
            $abs_path = static::ds($directory, $file);

            if ($rcut !== null)
            {
                $file = substr($abs_path, $rcut);
            }

            $is_dir = dir::exists($abs_path);

            $r = $callback($abs_path, $file, $is_dir);

            if ($r === self::map_continue)
            {
                continue;
            }

            if ($r !== null)
            {
                $collection[] = $r;
            }

            if ($is_dir)
            {
                if ($rcut === null)
                {
                    $rcut = strlen($directory)+1;
                }

                $collection = array_merge(
                    $collection, static::map($abs_path, $callback, $rcut)
                );
            }
        }

        return $collection;
    }

    /**
     * Return list of file(s) and folders in a particular directory.
     * If no filter provided, `.` and `..` will be excluded.
     * --
     * @param string $directory
     * @param string $filter
     *        Normal regular expression filter, matching files will be returned.
     * --
     * @throws mysli\toolkit\exception\fs 10 Directory doesn't exists.
     * --
     * @return array
     */
    static function ls($directory, $filter=null)
    {
        if (!dir::exists($directory)) {
            throw new exception\fs(
                "Directory doesn't exists: `{$directory}`.", 10
            );
        }

        if (!$filter)
        {
            return array_diff(scandir($directory), ['.', '..']);
        }
        else
        {
            $collection = [];

            $filter = substr($filter, 0, 1) === '/'
                ? $filter
                : static::filter_to_regex($filter);

            foreach (static::ls($directory, null) as $file)
            {
                if (preg_match($filter, $file))
                {
                    $collection[] = $file;
                }
            }

            return $collection;
        }
    }

    /**
     * Convert simple filter to regular expression.
     * For example:
     *
     *     *.jpg|*.gif
     *
     * The above example will match all files ending with `.jpg` and `.gif`.
     *
     *     !*.png
     *
     * Match any file but `.png`.
     *
     *     report_*.md
     *
     * Match any file which starts with `report_` and ends with `.md`.
     *
     * Following special characters are allowed:
     *
     *     * - match all until
     *     | - or
     *     ! - not (always at the beginning!)
     *
     * Filter is constructed in a following way:
     *
     * *.jpg        => /(^.*?\.jpg$)/i
     * *.jpg|*.gif  => /(^.*?\.jpg)|(^.*?\.gif$)/i
     * !*.png       => /(?!^.*?\.png$)(^.*?$)/i
     * !*.png|*.jpg => /(?!^.*?\.png$)(?!^.*?\.jpg$)(^.*?$)/i
     * --
     * @param  string $filter
     * --
     * @return string
     */
    static function filter_to_regex($filter)
    {
        $final = '';

        // Do we have negation?
        if (substr($filter, 0, 1) === '!')
        {
            $filter = substr($filter, 1);
            $negate = true;
        }
        else
        {
            $negate = false;
        }

        // Groups
        $groups = explode('|', $filter);

        foreach ($groups as &$group)
        {
            $group = preg_quote($group);
            $group = str_replace('\\*', '.*?', $group);

            if ($negate)
                $group = "(^?!{$group}$)";
            else
                $group = "(^{$group}$)";
        }

        if ($negate)
        {
            $groups[] = '(^.*?$)';
            $final = implode('', $groups);
        }
        else
        {
            $final = implode('|', $groups);
        }

        return "/{$final}/i";
    }

    /*
    --- Paths ------------------------------------------------------------------
     */

    /**
     * Correct Directory Separators.
     * --
     * @param string $...
     *        Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function ds()
    {
        $path = func_get_args();
        $path = implode(DIRECTORY_SEPARATOR, $path);

        if ($path)
        {
            return preg_replace(
                '/(?<![:\/])[\/\\\\]+/', DIRECTORY_SEPARATOR, $path
            );
        }
        else
        {
            return null;
        }
    }

    /**
     * Retrun application path.
     * --
     * @param string $...
     *        Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function apppath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return static::ds(MYSLI_APPPATH, $arguments);
    }

    /**
     * Retrun bin path.
     * --
     * @param string $...
     *        Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function binpath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return static::ds(MYSLI_BINPATH, $arguments);
    }

    /**
     * Retrun public path.
     * --
     * @param string $...
     *        Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function pubpath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return static::ds(MYSLI_PUBPATH, $arguments);
    }

    /**
     * Retrun temporary path.
     * --
     * @param string $...
     *        Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function tmppath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return static::ds(MYSLI_TMPPATH, $arguments);
    }

    /**
     * Retrun configuration path.
     * --
     * @param string $...
     *        Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function cfgpath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return static::ds(MYSLI_CFGPATH, $arguments);
    }

    /**
     * Return contents path.
     * --
     * @param string $... Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function cntpath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return static::ds(MYSLI_CNTPATH, $arguments);
    }

    /**
     * Retrun packages real path.
     * This will take .phar packages into consideration, and return
     * phar://... in case of them.
     * --
     * @param string $package
     * @param string $...
     *        Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function pkgreal($package)
    {
        $arguments = func_get_args();
        $arguments = array_slice($arguments, 1);
        $arguments = implode('/', $arguments);

        $is_phar = pkg::exists_as($package) === pkg::phar;

        if ($is_phar)
        {
            return 'phar://'.static::binpath($package.'.phar/', $arguments);
        }
        else
        {
            return static::binpath($package, $arguments);
        }
    }

    /**
     * Return package's root from __DIR__ or package's name.
     * --
     * @param  string $...
     *         Accept multiple parameters, to build full path from them.
     * --
     * @return string
     */
    static function pkgroot()
    {
        $arguments = func_get_args();
        $dir = rtrim(str_replace('\\', '/', array_shift($arguments)), '/');

        do
        {
            if (file::exists($dir.'/mysli.pkg.ym'))
            {
                break;
            }
            else
            {
                $dir = substr($dir, 0, strrpos($dir, '/'));
            }
        } while(strlen($dir) > 1);

        return static::ds($dir, implode('/', $arguments));
    }
}
