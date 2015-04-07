<?php

namespace mysli\framework\fs;

__use(__namespace__, '
    mysli.framework.exception/* -> framework\exception\*
');

class fs
{
    const map_continue = '\\/map continue\\/';

    /**
     * Convert size (from bytes) to nicer (human readable) value (kb, mb)
     * Return: bytes|KB|MB|GB
     * @param  integer $size (bytes)
     * @param  integer $round_precision
     * @return array   [12, 'MB']
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
     * Rename file or directory.
     * @param  mixed $old String or array [old => new, old => new]
     * @param  mixed $new String or null (if first param is array)
     * @return integer number of renamed files
     */
    static function rename($old, $new=null)
    {
        if (is_array($old))
        {
            $renamed = 0;

            foreach ($old as $o => $n)
            {
                $renamed = $renamed + self::rename($o, $n);
            }

            return $renamed;
        }

        return rename($old, $new);
    }
    /**
     * Will generate unique prefix for particular file / folder.
     * @param  string $filename full path
     * @return string new filename
     */
    static function unique_prefix($filename)
    {
        $destination = dirname($filename);
        $filename    = basename($filename);
        return md5(self::ds($destination, $filename)) . '_' . $filename;
    }
    /**
     * Retrun data path.
     * @param string ... Accept multiple parameters,
     * to build full path from them.
     * @return string
     */
    static function datpath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return self::ds(MYSLI_DATPATH, $arguments);
    }
    /**
     * Retrun packages path.
     * @param string ... Accept multiple parameters,
     *                   to build full path from them.
     * @return string
     */
    static function pkgpath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return self::ds(MYSLI_PKGPATH, $arguments);
    }
    /**
     * Retrun packages real path.
     * This will take .phar packages into consideration, and return
     * phar://... in case of them.
     * @param string $package
     * @param string ... Accept multiple parameters,
     *                   to build full path from them.
     * @return string
     */
    static function pkgreal($package)
    {
        $arguments = func_get_args();
        $arguments = array_slice($arguments, 1);
        $arguments = implode('/', $arguments);

        $is_phar = \core\pkg::exists_as($package) === \core\pkg::phar;

        if ($is_phar)
        {
            return 'phar://'.self::pkgpath($package.'.phar/', $arguments);
        }
        else
        {
            return self::pkgpath(str_replace('.', '/', $package), $arguments);
        }
    }
    /**
     * Return package's root from __DIR__ or package's name
     * @param  string ... Accept multiple parameters,
     *                    to build full path from them.
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

        return self::ds($dir, implode('/', $arguments));
    }
    /**
     * Retrun temporary path.
     * @param string ... Accept multiple parameters,
     * to build full path from them.
     * @return string
     */
    static function tmppath()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return self::ds(MYSLI_TMPPATH, $arguments);
    }
    /**
     * Correct Directory Separators.
     * @param string ... Accept multiple parameters,
     * to build full path from them.
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
     * Method to calculate the relative path from $from to $to.
     * Note: On Windows it does not work when $from and $to
     * are on different drives.
     * Credit: http://www.php.net/manual/en/function.realpath.php#105876
     * @param  string $to
     * @param  string $from
     * @param  string $ps
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
     * Will generate new unique file/dir name,
     * only if the file/dir already exists.
     * @param  string $filename full path.
     * @param  string $divider e.g. file.txt => file_2.txt when divider is _
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

        while (file::exists(self::ds($directory, $new_filename)))
        {
            $new_filename = $base . $divider . $n . $ext;
            $n++;
        }

        return self::ds($directory, $new_filename);
    }
    /**
     * Call function for each file/dir.
     * function ($full_absolute_path, $relative_path, $is_directory)
     *   return fs::map_continue - skip to the next file
     *       if you used on a directory the whole directory (with all content)
     *       will be skipped.
     * @param  string   $directory
     * @param  callable $callback
     * @param  integer  $rcut
     * @return array
     */
    static function map($directory, $callback, $rcut=null)
    {
        $collection = [];

        if (!dir::exists($directory))
        {
            throw new framework\exception\not_found(
                "Not a valid directory: `{$directory}`.", 1
            );
        }

        foreach (self::ls($directory) as $file)
        {
            $abs_path = self::ds($directory, $file);

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
                    $collection, self::map($abs_path, $callback, $rcut)
                );
            }
        }

        return $collection;
    }
    /**
     * Return list of file(s) and folders in a particular directory.
     * If no filter provided, `.` and `..` will be excluded.
     * @param  string $directory
     * @param  string $filter    Normal regular expression filter,
     *                           matching files will be returned.
     * @return array
     */
    static function ls($directory, $filter=null)
    {
        if (!$filter)
        {
            return array_diff(scandir($directory), ['.', '..']);
        }
        else
        {
            $collection = [];
            $filter = substr($filter, 0, 1) === '/'
                ? $filter
                : "/{$filter}/";

            foreach (scandir($directory) as $file)
            {
                if (preg_match($filter, $file))
                {
                    $collection[] = $file;
                }
            }

            return $collection;
        }
    }
}
