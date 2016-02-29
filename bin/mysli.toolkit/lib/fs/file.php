<?php

namespace mysli\toolkit\fs; class file
{
    const __use = <<<fin
        .{ log, exception.file }
        .fs.{ fs, dir }
fin;

    const prepend = 0;
    const append = 1;
    const replace = 2;

    /**
     * Return only extension of file if available otherwise null.
     *
     * Example:
     *
     *     ui.min.js => .js
     * --
     * @param string  $path
     * @param boolean $invert  Return only file ratherthan extension.
     * --
     * @return string
     */
    static function extension($path, $invert=false)
    {
        $file = basename($path);
        $extension = strpos($file, '.') ? substr($file, strrpos($file, '.')) : null;
        if ($invert)
        {
            return substr($path, 0, -strlen($extension));
        }
        else
        {
            return $extension;
        }
    }

    /**
     * Get filename.
     * --
     * @param string  $filename
     * @param boolean $extension Include file extension?
     * --
     * @return string
     */
    static function name($filename, $extension=true)
    {
        $filename = basename($filename);

        if (!$extension)
        {
            $ext = static::extension($filename);
            if ($ext)
            {
                return substr($filename, 0, -(strlen($ext)));
            }
        }

        return $filename;
    }

    /**
     * Return file path (no filename).
     * --
     * @param string $filename
     * --
     * @return string
     */
    static function path($filename)
    {
        return dirname($filename);
    }

    /**
     * Return file size in bytes. File must exists.
     * --
     * @throws mysli\toolkit\exception\file 10 File not found.
     * --
     * @param string $filename
     * --
     * @return integer
     */
    static function size($filename)
    {
        if (!static::exists($filename))
        {
            throw new exception\file(
                "File not found: `{$filename}`.", 10
            );
        }

        return filesize($filename);
    }

    /**
     * file_exists wrapper
     * --
     * @param string $filename
     * --
     * @return boolean
     */
    static function exists($filename)
    {
        return file_exists($filename);
    }

    /**
     * Get file's content if file exists.
     * --
     * @throws mysli\toolkit\exception\file 10 File not found.
     * --
     * @param string $filename
     * --
     * @return string
     */
    static function read($filename)
    {
        if (static::exists($filename))
        {
            return file_get_contents($filename);
        }
        else
        {
            throw new exception\file(
                "File not found: `{$filename}`.", 10
            );
        }
    }

    /**
     * Create a new file, if doesn't exists already.
     * If it does exists (and $empty = true) it will remove existing content.
     * --
     * @throws mysli\toolkit\exception\file
     *         10 Couldn't remove file's contents.
     * --
     * @param string  $filename
     * @param boolean $empty
     * --
     * @return boolean
     */
    static function create($filename, $empty=false)
    {
        if (static::exists($filename))
        {
            if (!$empty)
            {
                return false;
            }

            if (file_put_contents($filename, '') === false)
            {
                throw new exception\file(
                    "Couldn't remove file's contents: `{$filename}`.", 10
                );
            }
        }

        log::info("Create: `{$filename}`", __CLASS__);

        return touch($filename);
    }

    /**
     * Create a new file, if doesn't exists already.
     * This can create directory also, if is not there already.
     * --
     * @param string  $filename
     * @param boolean $empty
     * --
     * @return boolean
     */
    static function create_recursive($filename, $empty=false)
    {
        $dir = dirname($filename);

        if (!dir::exists($dir))
        {
            dir::create($dir, 0777, true);
        }

        return static::create($filename, $empty);
    }

    /**
     * Write a content to the file.
     * --
     * @throws mysli\toolkit\exception\file
     *         10 File doesn't exists.
     *
     * @throws mysli\toolkit\exception\file
     *         20 Couldn't open file.
     *
     * @throws mysli\toolkit\exception\file
     *         30 Couldn't lock the file.
     *
     * @throws mysli\toolkit\exception\file
     *         40 Couldn't write content to the file.
     * --
     * @param  string  $filename Full absolute path.
     * @param  string  $content
     * @param  integer $method   file::append, file::prepend, file::replace
     * @param  boolean $create
     * --
     * @return integer Number of bytes written.
     */
    static function write(
        $filename, $content, $method=self::replace, $lock=false, $create=true)
    {
        if (!static::exists($filename) && $create)
        {
            static::create($filename);
        }

        if (!static::exists($filename))
        {
            throw new exception\file(
                "File doesn't exists: `{$filename}`.", 10
            );
        }

        log::info(
            "Writting to file: `{$filename}`, method: `{$method}`, lock: `{$lock}`.",
            __CLASS__
        );

        if ($method === self::prepend)
        {
            $handle = fopen($filename, 'r+t');

            if ($handle === false)
            {
                throw new exception\file(
                    "Couldn't open file: `{$filename}`", 20
                );
            }

            if ($lock)
            {
                if (!flock($handle, LOCK_EX))
                {
                    throw new exception\file(
                        "Couldn't lock the file: `{$filename}`.", 30
                    );
                }
            }

            $content_length = strlen($content);
            $sum_length = filesize($filename) + $content_length;
            $content_old = fread($handle, $content_length);
            rewind($handle);
            $i = 1;

            while (ftell($handle) < $sum_length)
            {
                fwrite($handle, $content);
                $content = $content_old;
                $content_old = fread($handle, $content_length);
                fseek($handle, $i * $content_length);
                $i++;
            }

            fflush($handle);

            if ($lock)
            {
                flock($handle, LOCK_UN);
            }

            fclose($handle);
            return $i;
        }
        else
        {
            if ($method === self::append)
            {
                $flags = FILE_APPEND;
            }
            else
            {
                $flags = 0;
            }

            if ($lock)
            {
                $flags = $flags|LOCK_EX;
            }

            $r = file_put_contents($filename, $content, $flags);

            if ($r === false)
            {
                throw new exception\file(
                    "Couldn't write content to the file: `{$filename}`.", 40
                );
            }

            return $r;
        }
    }

    /**
     * Remove one (or more files).
     * --
     * @param mixed $file String or an array to remove more than one file.
     * --
     * @return integer Number of removed files.
     */
    static function remove($file)
    {
        if (is_array($file))
        {
            $i = 0;

            foreach ($file as $f)
            {
                $i = $i + static::remove($f);
            }

            return $i;
        }

        log::info("Remove: `{$file}`", __CLASS__);

        return unlink($file) ? 1 : 0;
    }

    /**
     * Copy file from source to destination.
     * --
     * @throws mysli\toolkit\exception\file
     *         10 Destination directory not found.
     *
     * @throws mysli\toolkit\exception\file
     *         20 Destination file exists.
     * --
     * @param mixed   $source      Absolute path.
     * @param string  $destination Absolute path.
     * @param boolean $overwrite   If destination exists, overwrite it.
     * --
     * @return boolean
     */
    static function copy($source, $destination, $overwrite=true)
    {
        if (dir::exists($destination))
        {
            $destination = fs::ds($destination, '/', static::name($source));
        }
        else
        {
            if (!dir::exists(dirname($destination)))
            {
                throw new exception\file(
                    "Destination directory not found: `{$destination}`.", 10
                );
            }
        }

        if (static::exists($destination) && !$overwrite)
        {
            throw new exception\file(
                "Destination file exists: `{$destination}`.", 20
            );
        }

        log::info(
            "Copy: `{$source}` to `{$destination}`, overwrite: `{$overwrite}`.",
            __CLASS__
        );

        return copy($source, $destination);
    }

    /**
     * Move a file from source to destination.
     * --
     * @throws mysli\toolkit\exception\file
     *         10 Destination directory not found.
     *
     * @throws mysli\toolkit\exception\file
     *         20 Destination file exists.
     * --
     * @param mixed   $source      Absolute path.
     * @param string  $destination Absolute path.
     * @param boolean $overwrite   If destination exists, overwrite it.
     * --
     * @return boolean
     */
    static function move($source, $destination, $overwrite=true)
    {
        if (dir::exists($destination))
        {
            $destination = fs::ds($destination, '/', static::name($source));
        }
        else
        {
            if (!dir::exists(dirname($destination)))
            {
                throw new exception\file(
                    "Destination directory not found: `{$destination}`.", 10
                );
            }
        }

        if (static::exists($destination) && !$overwrite)
        {
            throw new exception\file(
                "Destination file exists: `{$destination}`.", 20
            );
        }

        log::info(
            "Move: `{$source}` to `{$destination}`, overwrite: `{$overwrite}`.",
            __CLASS__
        );

        return move($source, $destination);
    }

    /**
     * Rename a file.
     * --
     * @throws mysli\toolkit\exception\file
     *         10 Destination and source directories must be the same.
     *
     * @throws mysli\toolkit\exception\file
     *         20 Destination and source filenames must be different.
     * --
     * @param mixed  $source      Absolute path.
     * @param string $destination Absolute path.
     * --
     * @return boolean
     */
    static function rename($source, $destination)
    {
        if (strpos($destination, '/') === false &&
            strpos($destination, '\\') === false)
        {
            $destination = fs::ds(dirname($source), $destination);
        }

        if (dirname($source) !== dirname($destination))
        {
            throw new exception\file(
                "Destination and source directories must be the same.", 10
            );
        }

        if (basename($source) === basename($destination)) {
            throw new exception\file(
                "Destination and source filenames must be different.", 20
            );
        }

        log::info(
            "Rename: `{$source}` to `{$destination}`.",
            __CLASS__
        );

        return \rename($source, $destination);
    }

    /**
     * Find files in particular directory.
     * --
     * @param string $directory
     *
     * @param string $filter
     *        Regular expression filter, e.g. `/.*\.jpg/i`.
     *        Simple filter is supported. (@see fs::filter_to_regex())
     *
     * @param boolean $deep
     *        Include sub-directories.
     *
     * @param integer $mrel
     *        Relative path (cut off root directory segment)
     *        will require whole segment to match.
     *
     * @param integer $rootlen
     *        Use internally for length of a root, you can
     *        pass a value, to set how much of the file path should be removed,
     *        default is `strlen($directory)`.
     * --
     * @throws mysli\toolkit\exception\file
     *         10 Not a valid directory.
     * --
     * @return array
     */
    static function find(
        $directory, $filter=null, $deep=true, $mrel=false, $rootlen=null)
    {
        if (!dir::exists($directory))
        {
            throw new exception\file(
                "Not a valid directory: `{$directory}`.", 10
            );
        }

        // Grab files in the selected directory...
        $collection = array_diff(scandir($directory), ['.','..']);
        $matched    = [];

        // No files were found at all
        if (empty($collection))
            return [];

        // Convert simple filter to the regular expression
        if ($filter && substr($filter, 0, 1) !== '/')
            $filter = fs::filter_to_regex($filter);


        // If there's no $rootlen, it will be acquired from the directory.
        // This is used latter in recursion.
        $rootlen = $rootlen ?: strlen(rtrim($directory, '\\/'))+1;

        /*
        Start looking for files.
         */
        foreach ($collection as $file)
        {
            if (dir::exists(fs::ds($directory, $file)))
            {
                if (!$deep)
                    continue;

                $matched_sub = static::find(
                    fs::ds($directory, $file), $filter, $deep, $mrel, $rootlen
                );
                $matched = array_merge($matched, $matched_sub);
                continue;
            }

            // Full file path
            $ffile  = fs::ds($directory, $file);
            // Relative file path
            $rfile = substr($ffile, $rootlen);

            // Match either to relative, or filename itself
            if ($filter && !preg_match($filter, ($mrel ? $rfile : $file)))
            {
                continue;
            }

            $matched[$rfile] = $ffile;
        }

        return $matched;
    }

    /**
     * Return a md5 signature of specified file(s).
     * --
     * @param  mixed $filename
     *         Filename (full path), or an array, collection of files,
     *         e.g. `['/abs/path/file.1', '/abs/path/file.2']`.
     * --
     * @throws mysli\toolkit\exception\file 10 File not found.
     * --
     * @return mixed string | array, depends on the input.
     */
    static function signature($filename)
    {
        if (is_array($filename))
        {
            $collection = [];

            foreach ($filename as $file)
            {
                $collection[$file] = static::signature($file);
            }

            return $collection;
        }

        if (!file::exists($filename))
        {
            throw new exception\file(
                "File not found: `{$filename}`.", 10
            );
        }

        return md5_file($filename);
    }
}
