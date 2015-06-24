<?php

namespace mysli\toolkit\fs; class file
{
    const __use = '.exception.* -> toolkit.exception.*';

    const prepend = 0;
    const append = 1;
    const replace = 2;

    /**
     * Return only extension of file if available otherwise an empty string.
     * --
     * @param string $filename
     * --
     * @return string
     */
    static function extension($filename)
    {
        $file = basename($filename);

        if (strpos($file, '.') === false)
        {
            return '';
        }

        $extension = explode('.', strrev($file), 2);
        return strrev($extension[0]);
    }

    /**
     * Get filename.
     * --
     * @param string  $filename
     * @param boolean $extension
     * --
     * @return string
     */
    static function name($filename, $extension=true)
    {
        $filename = basename($filename);

        if (!$extension)
        {
            $file_ext = self::extension($filename);
            $file_ext = strlen($file_ext);

            if ($file_ext > 0)
            {
                return substr($filename, 0, ($file_ext + 1) * -1);
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
     * @param string $filename
     * --
     * @return integer
     */
    static function size($filename)
    {
        if (!self::exists($filename))
        {
            throw new toolkit\exception\not_found(
                "File not found: `{$filename}`.", 1
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
     * @param string $filename
     * --
     * @return string
     */
    static function read($filename)
    {
        if (self::exists($filename))
        {
            return file_get_contents($filename);
        }
        else
        {
            throw new toolkit\exception\not_found(
                "File not found: `{$filename}`.", 1
            );
        }
    }

    /**
     * Create a new file, if doesn't exists already.
     * If it does exists (and $empty = true) it will remove existing content.
     * --
     * @param string  $filename
     * @param boolean $empty
     * --
     * @return boolean
     */
    static function create($filename, $empty=false)
    {
        if (file::exists($filename))
        {
            if (!$empty)
            {
                return false;
            }

            if (file_put_contents($filename, '') === false)
            {
                throw new toolkit\exception\fs(
                    "Couldn't remove file's contents: `{$filename}`.", 1
                );
            }
        }

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

        return self::create($filename, $empty);
    }

    /**
     * Write a content to the file.
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
        if (!self::exists($filename) && $create)
        {
            self::create($filename);
        }

        if (!self::exists($filename))
        {
            throw new toolkit\exception\not_found(
                "File doesn't exists: `{$filename}`.", 1
            );
        }

        if ($method === self::prepend)
        {
            $handle = fopen($filename, 'r+t');

            if ($handle === false)
            {
                throw new toolkit\exception\fs(
                    "Couldn't open file: `{$filename}`", 1
                );
            }

            if ($lock)
            {
                if (!flock($handle, LOCK_EX))
                {
                    throw new toolkit\exception\fs(
                        "Couldn't lock the file: `{$filename}`.", 2
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
                throw new toolkit\exception\fs(
                    "Couldn't write content to the file: `{$filename}`.", 3
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
                $i = $i + self::remove($f);
            }

            return $i;
        }

        return unlink($file) ? 1 : 0;
    }

    /**
     * Copy file from source to destination.
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
            $destination = fs::ds($destination, '/', self::name($source));
        }
        else
        {
            if (!dir::exists(dirname($destination)))
            {
                throw new toolkit\exception\not_found(
                    "Destination directory not found: `{$destination}`."
                );
            }
        }

        if (file::exists($destination) && !$overwrite)
        {
            throw new toolkit\exception\argument(
                "Destination file exists: `{$destination}`."
            );
        }

        return copy($source, $destination);
    }

    /**
     * Move a file from source to destination.
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
            $destination = fs::ds($destination, '/', self::name($source));
        }
        else
        {
            if (!dir::exists(dirname($destination)))
            {
                throw new toolkit\exception\not_found(
                    "Destination directory not found: `{$destination}`."
                );
            }
        }

        if (file::exists($destination) && !$overwrite)
        {
            throw new toolkit\exception\argument(
                "Destination file exists: `{$destination}`."
            );
        }

        return move($source, $destination);
    }

    /**
     * Rename a file.
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
            throw new toolkit\exception\argument(
                "Destination and source directories must be the same.", 1
            );
        }

        if (basename($source) === basename($destination)) {
            throw new toolkit\exception\argument(
                "Destination and source filenames must be different.", 2
            );
        }

        return \rename($source, $destination);
    }

    /**
     * Find files in particular directory.
     * --
     * @param string $directory
     *
     * @param string $filter
     *        Regular expression filter, e.g. `/.*\.jpg/i`.
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
     * @return array
     */
    static function find(
        $directory, $filter=null, $deep=true, $mrel=false, $rootlen=null)
    {
        if (!dir::exists($directory))
        {
            throw new toolkit\exception\argument(
                "Not a valid directory: `{$directory}`.", 1
            );
        }

        $collection = array_diff(scandir($directory), ['.','..']);
        $matched    = [];

        if ($filter && substr($filter, 0, 1) !== '/')
        {
            throw new toolkit\exception\argument(
                "Invalid filter formar: `{$filter}` ".
                "expected regular expression.", 1
            );
        }

        if (empty($collection))
        {
            return [];
        }

        $rootlen = $rootlen ?: strlen($directory)+1;

        foreach ($collection as $file)
        {
            if (dir::exists(fs::ds($directory, $file)))
            {
                if (!$deep)
                {
                    continue;
                }

                $matched_sub = self::find(
                    fs::ds($directory, $file), $filter, $deep, $mrel, $rootlen
                );
                $matched = array_merge($matched_sub, $matched);
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
     * @return mixed string | array, depends on the input.
     */
    static function signature($filename)
    {
        if (is_array($filename))
        {
            $collection = [];

            foreach ($filename as $file)
            {
                $collection[$file] = self::signature($file);
            }

            return $collection;
        }

        if (!file_exists($filename))
        {
            throw new toolkit\exception\argument(
                "File not found: `{$filename}`.", 1
            );
        }

        return md5_file($filename);
    }
}
